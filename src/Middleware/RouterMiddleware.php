<?php

namespace Simply\Application\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Simply\Application\Handler\MiddlewareHandler;
use Simply\Application\HttpFactory\HttpFactoryInterface;
use Simply\Router\Exception\MethodNotAllowedException;
use Simply\Router\Exception\RouteNotFoundException;
use Simply\Router\Route;
use Simply\Router\Router;

/**
 * RouterMiddleware.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class RouterMiddleware implements MiddlewareInterface
{
    private $container;
    private $router;
    private $httpFactory;

    public function __construct(Router $router, ContainerInterface $container, HttpFactoryInterface $factory)
    {
        $this->router = $router;
        $this->container = $container;
        $this->httpFactory = $factory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = urldecode($uri->getPath());

        try {
            $route = $this->router->route($request->getMethod(), $path);
        } catch (MethodNotAllowedException $exception) {
            return $this->httpFactory->createResponse(405, 'Method Not Allowed')
                ->withHeader('Allow', implode(', ', $exception->getAllowedMethods()))
                ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                ->withBody($this->httpFactory->createStream('Method Not Allowed'));
        } catch (RouteNotFoundException $exception) {
            return $handler->handle($request);
        }

        if ($request->getMethod() === 'GET' && $route->getPath() !== $path) {
            return $this->httpFactory->createResponse(302, 'Found')
                ->withHeader('Location', (string) $uri->withPath($route->getUrl()));
        }

        return $this->dispatch($route, $request);
    }

    private function dispatch(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        foreach ($route->getParameters() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $handler = $route->getHandler();

        if (\is_string($handler)) {
            return $this->getHandler($handler)->handle($request);
        }

        $stack = new MiddlewareHandler($this->getHandler($handler['handler']));

        foreach ($handler['middleware'] ?? [] as $name) {
            $stack->push($this->getMiddleware($name));
        }

        return $stack->handle($request);
    }

    private function getHandler(string $name): RequestHandlerInterface
    {
        return $this->container->get($name);
    }

    private function getMiddleware(string $name): MiddlewareInterface
    {
        return $this->container->get($name);
    }
}
