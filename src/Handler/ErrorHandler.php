<?php

namespace Simply\Application\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Simply\Application\HttpFactory\HttpFactoryInterface;

/**
 * ErrorHandler.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ErrorHandler implements RequestHandlerInterface
{
    private $httpFactory;

    public function __construct(HttpFactoryInterface $factory)
    {
        $this->httpFactory = $factory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->httpFactory->createResponse(500, 'Internal Server Error')
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withBody($this->httpFactory->createStream('Internal Server Error'));
    }
}
