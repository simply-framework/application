<?php

namespace Simply\Application\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Simply\Application\HttpFactory\HttpFactoryInterface;

/**
 * A simple handler that sends a 500 error response.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ErrorHandler implements RequestHandlerInterface
{
    /** @var HttpFactoryInterface The http factory used to generate the response */
    private $httpFactory;

    /**
     * ErrorHandler constructor.
     * @param HttpFactoryInterface $factory The http factory used to generate the response
     */
    public function __construct(HttpFactoryInterface $factory)
    {
        $this->httpFactory = $factory;
    }

    /** {@inheritdoc} */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->httpFactory->createResponse(500, 'Internal Server Error')
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withBody($this->httpFactory->createStream('Internal Server Error'));
    }
}
