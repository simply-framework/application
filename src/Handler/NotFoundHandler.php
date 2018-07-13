<?php

namespace Simply\Application\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Simply\Application\HttpFactory\HttpFactoryInterface;

/**
 * NotFoundHandler.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class NotFoundHandler implements RequestHandlerInterface
{
    private $httpFactory;

    public function __construct(HttpFactoryInterface $factory)
    {
        $this->httpFactory = $factory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->httpFactory->createResponse(404, 'Not Found')
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withBody($this->httpFactory->createStream('Not Found'));
    }
}
