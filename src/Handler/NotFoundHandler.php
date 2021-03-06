<?php

namespace Simply\Application\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A simple handler that sends a 404 response.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class NotFoundHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface The factory used to generate the response */
    private $responseFactory;

    /** @var StreamFactoryInterface The factory used to generate the body for the response */
    private $streamFactory;

    /**
     * NotFoundHandler constructor.
     * @param ResponseFactoryInterface $responseFactory The factory used to generate the response
     * @param StreamFactoryInterface $streamFactory The factory used to generate the body for the response
     */
    public function __construct(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    /** {@inheritdoc} */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createResponse(404, 'Not Found')
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withBody($this->streamFactory->createStream('Not Found'));
    }
}
