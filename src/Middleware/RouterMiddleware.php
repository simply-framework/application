<?php

namespace Simply\Application\Middleware;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Simply\Application\Handler\MiddlewareHandler;
use Simply\Router\Exception\MethodNotAllowedException;
use Simply\Router\Exception\RouteNotFoundException;
use Simply\Router\Route;
use Simply\Router\Router;

/**
 * Routing middleware that uses router to point requests to appropriate handlers.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class RouterMiddleware implements MiddlewareInterface
{
    /** @var ContainerInterface Container used to load handlers and additional middleware */
    private $container;

    /** @var Router The router used to map requests to handlers */
    private $router;

    /** @var ResponseFactoryInterface The http factory used to generate error responses */
    private $responseFactory;

    /** @var StreamFactoryInterface The http factory used to generate error bodies */
    private $streamFactory;

    /**
     * RouterMiddleware constructor.
     * @param Router $router The router used to map requests to handlers
     * @param ContainerInterface $container Container used to load handlers and additional middleware
     * @param ResponseFactoryInterface $responseFactory The http factory used to generate error responses
     * @param StreamFactoryInterface $streamFactory The http factory used to generate error bodies
     */
    public function __construct(
        Router $router,
        ContainerInterface $container,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->router = $router;
        $this->container = $container;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    /** {@inheritdoc} */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = urldecode($uri->getPath());

        try {
            $route = $this->router->route($request->getMethod(), $path);
        } catch (MethodNotAllowedException $exception) {
            return $this->responseFactory->createResponse(405, 'Method Not Allowed')
                ->withHeader('Allow', implode(', ', $exception->getAllowedMethods()))
                ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                ->withBody($this->streamFactory->createStream('Method Not Allowed'));
        } catch (RouteNotFoundException $exception) {
            return $handler->handle($request);
        }

        if ($request->getMethod() === 'GET' && $route->getPath() !== $path) {
            return $this->responseFactory->createResponse(302, 'Found')
                ->withHeader('Location', (string) $uri->withPath($route->getUrl()));
        }

        return $this->dispatch($route, $request);
    }

    /**
     * Dispatches the given route for the given request.
     * @param Route $route The route to dispatch
     * @param ServerRequestInterface $request The request to provide for the dispatched route
     * @return ResponseInterface The response result from the route
     */
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

    /**
     * Loads the request handler from the container.
     * @param string $name The name of the dependency to load
     * @return RequestHandlerInterface The request handler loaded from the container
     */
    private function getHandler(string $name): RequestHandlerInterface
    {
        return $this->container->get($name);
    }

    /**
     * Loads a middleware from the container.
     * @param string $name The name of the dependency
     * @return MiddlewareInterface The middleware loaded from the container
     */
    private function getMiddleware(string $name): MiddlewareInterface
    {
        return $this->container->get($name);
    }
}
