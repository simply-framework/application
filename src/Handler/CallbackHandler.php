<?php

namespace Simply\Application\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A handler that calls the given callback to generate the response.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CallbackHandler implements RequestHandlerInterface
{
    /** @var callable The callback to call with the request */
    private $callback;

    /**
     * CallbackHandler constructor.
     * @param callable $callback The callback to call with the request
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /** {@inheritdoc} */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->callback)($request);
    }
}
