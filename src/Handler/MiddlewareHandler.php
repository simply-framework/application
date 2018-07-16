<?php

namespace Simply\Application\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A middleware stack that acts like a request handler.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class MiddlewareHandler implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] List of middlewares to call */
    private $stack;

    /** @var RequestHandlerInterface The fallback request handler */
    private $fallback;

    /**
     * MiddlewareHandler constructor.
     * @param RequestHandlerInterface $fallback The fallback request handler to call
     */
    public function __construct(RequestHandlerInterface $fallback)
    {
        $this->stack = [];
        $this->fallback = $fallback;
    }

    /**
     * Adds a new middleware to the stack.
     * @param MiddlewareInterface $middleware Additional middleware to add to the stack
     */
    public function push(MiddlewareInterface $middleware): void
    {
        $this->stack[] = $middleware;
    }

    /** {@inheritdoc} */
    public function handle(Request $request): Response
    {
        $stack = $this->stack;

        $handler = new CallbackHandler(function (Request $request) use (& $stack, & $handler): Response {
            $middleware = array_shift($stack);

            if ($middleware instanceof MiddlewareInterface) {
                return $middleware->process($request, $handler);
            }

            return $this->fallback->handle($request);
        });

        return $handler->handle($request);
    }
}
