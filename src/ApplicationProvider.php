<?php

namespace Simply\Application;

use Psr\Container\ContainerInterface;
use Simply\Application\Handler\ErrorHandler;
use Simply\Application\Handler\MiddlewareHandler;
use Simply\Application\Handler\NotFoundHandler;
use Simply\Application\HttpFactory\DiactorosHttpFactory;
use Simply\Application\HttpFactory\HttpFactoryInterface;
use Simply\Application\Middleware\ErrorHandlerMiddleware;
use Simply\Application\Middleware\RouterMiddleware;
use Simply\Container\AbstractEntryProvider;
use Simply\Router\Router;

/**
 * Provides the default dependencies required by the application.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ApplicationProvider extends AbstractEntryProvider
{
    public function getApplication(ContainerInterface $container): Application
    {
        return new Application(
            $container->get(HttpFactoryInterface::class),
            $container->get(MiddlewareHandler::class),
            $container->get(HttpClient::class)
        );
    }

    public function getHttpFactory(): HttpFactoryInterface
    {
        return new DiactorosHttpFactory();
    }

    public function getMiddlewareHandler(ContainerInterface $container): MiddlewareHandler
    {
        $stack = new MiddlewareHandler($container->get(NotFoundHandler::class));

        $stack->push($container->get(ErrorHandlerMiddleware::class));
        $stack->push($container->get(RouterMiddleware::class));

        return $stack;
    }

    public function getNotFoundHandler(ContainerInterface $container): NotFoundHandler
    {
        return new NotFoundHandler($container->get(HttpFactoryInterface::class));
    }

    public function getErrorHandlerMiddleware(ContainerInterface $container): ErrorHandlerMiddleware
    {
        return new ErrorHandlerMiddleware(
            $container->get(ErrorHandler::class)
        );
    }

    public function getErrorHandler(ContainerInterface $container): ErrorHandler
    {
        return new ErrorHandler(
            $container->get(HttpFactoryInterface::class)
        );
    }

    public function getRouterMiddleware(ContainerInterface $container): RouterMiddleware
    {
        return new RouterMiddleware(
            $container->get(Router::class),
            $container,
            $container->get(HttpFactoryInterface::class)
        );
    }

    public function getHttpClient(): HttpClient
    {
        return new HttpClient();
    }
}
