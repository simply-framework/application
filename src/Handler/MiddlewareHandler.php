<?php

namespace Simply\Application\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * MiddlewareStack.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class MiddlewareHandler implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    private $stack;

    /** @var RequestHandlerInterface */
    private $fallback;

    public function __construct(RequestHandlerInterface $fallback)
    {
        $this->stack = [];
        $this->fallback = $fallback;
    }

    public function push(MiddlewareInterface $middleware)
    {
        $this->stack[] = $middleware;
    }

    public function handle(Request $request): Response
    {
        $stack = $this->stack;
        $handler = new CallbackHandler(function (Request $request) use (& $stack, & $handler): Response {
            if ($stack) {
                $middleware = array_shift($stack);
                return $middleware->process($request, $handler);
            }

            return $this->fallback->handle($request);
        });

        return $handler->handle($request);
    }
}
