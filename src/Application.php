<?php

namespace Simply\Application;

use Simply\Application\Handler\MiddlewareHandler;
use Simply\Application\HttpFactory\HttpFactoryInterface;

/**
 * Application.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Application
{
    private $httpFactory;
    private $middlewareStack;
    private $httpClient;

    public function __construct(HttpFactoryInterface $factory, MiddlewareHandler $stack, HttpClient $client)
    {
        $this->httpFactory = $factory;
        $this->middlewareStack = $stack;
        $this->httpClient = $client;
    }

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
