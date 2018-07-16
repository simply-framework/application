<?php

namespace Simply\Application;

use Simply\Application\Handler\MiddlewareHandler;
use Simply\Application\HttpFactory\HttpFactoryInterface;

/**
 * The core application that runs the main middleware stack.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Application
{
    /** @var HttpFactoryInterface The factory used to generate the request from globals */
    private $httpFactory;

    /** @var MiddlewareHandler The middleware stack that is run for every request */
    private $middlewareStack;

    /** @var HttpClient The http client the application uses to communicate to the browser */
    private $httpClient;

    /**
     * Application constructor.
     * @param HttpFactoryInterface $factory The factory used to generate the request from globals
     * @param MiddlewareHandler $stack The middleware stack that is run for every request
     * @param HttpClient $client The http client the application uses to communicate to the browser
     */
    public function __construct(HttpFactoryInterface $factory, MiddlewareHandler $stack, HttpClient $client)
    {
        $this->httpFactory = $factory;
        $this->middlewareStack = $stack;
        $this->httpClient = $client;
    }

    /**
     * Runs the application middleware stack for the request and sends the response.
     */
    public function run(): void
    {
        $request = $this->httpFactory->createServerRequestFromGlobals();
        $response = $this->middlewareStack->handle($request);

        if ($request->getMethod() === 'HEAD') {
            $this->httpClient->omitBody();
        }

        $this->httpClient->send($response);
    }
}
