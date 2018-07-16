<?php

namespace Simply\Application\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Basic error handler middleware that forwards all exceptions to given request handler.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /** @var RequestHandlerInterface The request handler used to handle exceptions */
    private $errorHandler;

    /**
     * ErrorHandlerMiddleware constructor.
     * @param RequestHandlerInterface $handler The request handler used to handle exceptions
     */
    public function __construct(RequestHandlerInterface $handler)
    {
        $this->errorHandler = $handler;
    }

    /** {@inheritdoc} */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $exception) {
            return $this->errorHandler->handle($request->withAttribute('exception', $exception));
        }
    }
}
