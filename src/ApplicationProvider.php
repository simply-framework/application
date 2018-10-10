<?php

namespace Simply\Application;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Simply\Application\Handler\ErrorHandler;
use Simply\Application\Handler\MiddlewareHandler;
use Simply\Application\Handler\NotFoundHandler;
use Simply\Application\HttpFactory\DiactorosHttpFactory;
use Simply\Application\HttpFactory\HttpFactoryInterface;
use Simply\Application\Middleware\ErrorHandlerMiddleware;
use Simply\Application\Middleware\RouterMiddleware;
use Simply\Container\AbstractEntryProvider;
use Simply\Router\Router;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UploadedFileFactory;
use Zend\Diactoros\UriFactory;

/**
 * Provides the default dependencies required by the application.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ApplicationProvider extends AbstractEntryProvider
{
    /**
     * Returns the configured web application.
     * @param ContainerInterface $container The container used to resolve dependencies
     * @return Application The configured web application
     */
    public function getApplication(ContainerInterface $container): Application
    {
        return new Application(
            $container->get(HttpFactoryInterface::class),
            $container->get(MiddlewareHandler::class),
            $container->get(HttpClient::class)
        );
    }

    /**
     * Returns the default middleware stack that is run for every request.
     * @param ContainerInterface $container The container used to resolve dependencies
     * @return MiddlewareHandler The default middleware stack that is run for every request
     */
    public function getMiddlewareHandler(ContainerInterface $container): MiddlewareHandler
    {
        $stack = new MiddlewareHandler($container->get(NotFoundHandler::class));

        $stack->push($container->get(ErrorHandlerMiddleware::class));
        $stack->push($container->get(RouterMiddleware::class));

        return $stack;
    }

    /**
     * Returns the request handler used for 404 responses.
     * @param ContainerInterface $container The container used to resolve dependencies
     * @return NotFoundHandler Rhe request handler used for 404 responses
     */
    public function getNotFoundHandler(ContainerInterface $container): NotFoundHandler
    {
        return new NotFoundHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class)
        );
    }

    /**
     * Returns the middleware used to route exceptions to the error handler.
     * @param ContainerInterface $container The container used to resolve dependencies
     * @return ErrorHandlerMiddleware The middleware used to route exceptions to the error handler
     */
    public function getErrorHandlerMiddleware(ContainerInterface $container): ErrorHandlerMiddleware
    {
        return new ErrorHandlerMiddleware(
            $container->get(ErrorHandler::class)
        );
    }

    /**
     * Returns the request handler for handling exception responses.
     * @param ContainerInterface $container The container used to resolve dependencies
     * @return ErrorHandler The request handler for handling exception responses
     */
    public function getErrorHandler(ContainerInterface $container): ErrorHandler
    {
        return new ErrorHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class)
        );
    }

    /**
     * Returns the router middleware used to route requests to appropriate handlers.
     * @param ContainerInterface $container The container used to resolve dependencies
     * @return RouterMiddleware The router middleware used to route requests to appropriate handlers
     */
    public function getRouterMiddleware(ContainerInterface $container): RouterMiddleware
    {
        return new RouterMiddleware(
            $container->get(Router::class),
            $container,
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class)
        );
    }

    /**
     * Returns the http client used to communicate to the browser.
     * @return HttpClient The http client used to communicate to the browser
     */
    public function getHttpClient(): HttpClient
    {
        return new HttpClient();
    }

    /**
     * Returns the factory used create requests and responses.
     * @return HttpFactoryInterface The factory for creating requests and responses
     */
    public function getHttpFactory(): HttpFactoryInterface
    {
        return new DiactorosHttpFactory();
    }

    /**
     * Returns the standard Request factory.
     * @return RequestFactoryInterface The standard Request factory
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        return new RequestFactory();
    }

    /**
     * Returns the standard Response Factory.
     * @return ResponseFactoryInterface The standard Response Factory
     */
    public function getResponseFactory(): ResponseFactoryInterface
    {
        return new ResponseFactory();
    }

    /**
     * Returns the standard ServerRequest Factory.
     * @return ServerRequestFactoryInterface The standard ServerRequest Factory
     */
    public function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        return new ServerRequestFactory();
    }

    /**
     * Returns the standard Stream Factory.
     * @return StreamFactoryInterface The standard Stream Factory
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
        return new StreamFactory();
    }

    /**
     * Returns the standard UploadedFile Factory.
     * @return UploadedFileFactoryInterface The standard UploadedFile Factory
     */
    public function getUploadedFileFactory(): UploadedFileFactoryInterface
    {
        return new UploadedFileFactory();
    }

    /**
     * Returns the standard Uri Factory.
     * @return UriFactoryInterface The standard Uri Factory
     */
    public function getUriFactory(): UriFactoryInterface
    {
        return new UriFactory();
    }
}
