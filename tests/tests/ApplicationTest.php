<?php

namespace Simply\Application;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Simply\Application\Handler\CallbackHandler;
use Simply\Application\HttpFactory\HttpFactoryInterface;
use Simply\Container\Container;
use Simply\Container\ContainerBuilder;
use Simply\Router\RouteDefinition;
use Simply\Router\RouteDefinitionProvider;
use Simply\Router\Router;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;

/**
 * ApplicationTest.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ApplicationTest extends TestCase
{
    /** @var Container */
    private $container;

    /** @var MockObject|HttpFactoryInterface httpFactory */
    private $httpFactory;

    private $nextRequest;

    /** @var MockObject|ServerApi */
    private $serverApi;
    private $sentHeaders;
    private $sentOutput;

    protected function setUp()
    {
        $this->container = null;
        $this->serverApi = null;
        $this->sentHeaders = [];
        $this->sentOutput = '';
        $this->nextRequest = null;

        $this->httpFactory = $this->createMock(HttpFactoryInterface::class);
        $this->httpFactory->method('createServerRequestFromGlobals')->willReturnCallback(function () {
            return $this->nextRequest;
        });
    }

    public function testSuccessfulRouting()
    {
        $this->buildApplication([
            ['GET', '/path/', $this->getHandler('Correct Output')],
        ]);

        $this->makeRequest('GET', '/path/');

        $this->assertSame('Correct Output', $this->sentOutput);
        $this->assertContains('Content-Type: text/plain; charset=utf-8', $this->sentHeaders);
    }

    public function testCallingApplicationTwice()
    {
        $this->buildApplication([
            ['GET', '/get/path/', $this->getHandler('GetOutput')],
            ['POST', '/post/path/', $this->getHandler('PostOutput')],
        ]);

        $this->makeRequest('GET', '/get/path/');
        $this->makeRequest('POST', '/post/path/');

        $this->assertSame('GetOutputPostOutput', $this->sentOutput);
    }

    public function testMethodNotAllowedRoute()
    {
        $this->buildApplication([
            ['GET', '/path/', $this->getHandler('Not Happening')],
        ]);

        $this->makeRequest('POST', '/path/');

        $this->assertContains('HTTP/1.1 405 Method Not Allowed', $this->sentHeaders);
        $this->assertContains('Allow: GET, HEAD', $this->sentHeaders);
        $this->assertSame('Method Not Allowed', $this->sentOutput);
    }

    public function testNotFoundHandler()
    {
        $this->buildApplication([
            ['GET', '/path/', $this->getHandler('Not Happening')],
        ]);

        $this->makeRequest('GET', '/other/');

        $this->assertContains('HTTP/1.1 404 Not Found', $this->sentHeaders);
        $this->assertSame('Not Found', $this->sentOutput);
    }

    public function testRedirectCanonicalPath()
    {
        $this->buildApplication([
            ['GET', '/noslash', $this->getHandler('Not Happening')],
        ]);

        $this->makeRequest('GET', '/noslash/');

        $this->assertContains('HTTP/1.1 302 Found', $this->sentHeaders);
        $this->assertContains('Location: /noslash', $this->sentHeaders);
    }

    public function testInternalServerError()
    {
        $this->buildApplication([
            ['GET', '/error/', new CallbackHandler(function () {
                throw new \RuntimeException('Unexpected Error');
            })],
        ]);

        $this->makeRequest('GET', '/error/');

        $this->assertContains('HTTP/1.1 500 Internal Server Error', $this->sentHeaders);
        $this->assertSame('Internal Server Error', $this->sentOutput);
    }

    public function testMiddlewareHandling()
    {
        $handler = new CallbackHandler(function (Request $request) {
            $this->assertSame('param_value', $request->getAttribute('path_param'));

            return $this->getResponse('Handler');
        });

        $middleware = new class() implements MiddlewareInterface {
            public function process(Request $request, RequestHandlerInterface $handler): Response
            {
                return $handler->handle($request)->withHeader('Custom-Header', 'header-value');
            }
        };

        $this->buildApplication([
            ['GET', '/middleware/{path_param}/', [$middleware, $handler]],
        ]);

        $this->makeRequest('GET', '/middleware/param_value/');

        $this->assertContains('Custom-Header: header-value', $this->sentHeaders);
        $this->assertSame('Handler', $this->sentOutput);
    }

    public function testHeadOmitBody()
    {
        $this->buildApplication([
            ['GET', '/path/', $this->getHandler('body')],
        ]);

        $this->makeRequest('HEAD', '/path/');

        $this->assertContains('HTTP/1.1 200 OK', $this->sentHeaders);
        $this->assertSame('', $this->sentOutput);
    }

    public function testHeaderLimitedBody()
    {
        $handler = new CallbackHandler(function () {
            $response = $this->getResponse('12345');
            $response = $response->withHeader('Content-Length', '4');

            return $response;
        });

        $this->buildApplication([
            ['GET', '/limited/', $handler],
        ]);

        $this->makeRequest('GET', '/limited/');

        $this->assertContains('Content-Length: 4', $this->sentHeaders);
        $this->assertSame('1234', $this->sentOutput);
    }

    public function testAbnormalClientSend()
    {
        $this->buildApplication([
            ['GET', '/chunks/', $this->getHandler('12345')],
        ]);

        $client = $this->getHttpClient(true, true);
        $client->setResponseChunkSize(3);

        unset($this->container[HttpClient::class]);
        $this->container[HttpClient::class] = $client;

        $this->makeRequest('GET', '/chunks/');

        $this->assertSame([], $this->sentHeaders);
        $this->assertSame('123', $this->sentOutput);
    }

    public function testSmallRewindedBody()
    {
        $stream = fopen('php://memory', 'wb+');
        fwrite($stream, '0');

        $response = (new ResponseFactory())->createResponse()
            ->withBody((new StreamFactory())->createStreamFromResource($stream));

        $this->buildApplication([
            ['GET', '/path/', $this->getHandler($response)]
        ]);

        $this->makeRequest('GET', '/path/');

        $this->assertContains('Content-Length: 1', $this->sentHeaders);
        $this->assertSame('0', $this->sentOutput);
    }

    public function testUnmockedApplication()
    {
        $provider = new RouteDefinitionProvider();
        $provider->addRouteDefinition(new RouteDefinition('test.route', ['GET'], '/path/', 'test.handler'));

        $router = new Router($provider);

        $builder = new ContainerBuilder();
        $builder->registerProvider(new ApplicationProvider());

        $handler = new CallbackHandler(function () {
            return (new ResponseFactory())->createResponse()
                ->withBody((new StreamFactory())->createStream('Expected Output'));
        });

        $builder->registerConfiguration([
            Router::class => $router,
            'test.handler' => $handler,
        ]);

        /** @var Application $application */
        $application = $builder->getContainer()->get(Application::class);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/path/';

        $this->expectOutputString('Expected Output');
        $application->run();

        unset(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI']
        );
    }

    private function makeRequest(string $method, string $path)
    {
        $this->nextRequest = (new ServerRequestFactory())->createServerRequest($method, $path);
        $this->container->get(Application::class)->run();
    }

    private function buildApplication(array $routes): void
    {
        $provider = new RouteDefinitionProvider();
        $builder = new ContainerBuilder();
        $number = 1;

        foreach ($routes as [$method, $path, $handler]) {
            $name = 'route.' . $number++;
            $routeHandler = $name;

            if (\is_array($handler)) {
                $routeHandler = [
                    'middleware' => \array_slice($handler, 0, -1),
                    'handler' => $name,
                ];

                $handler = array_pop($handler);

                foreach ($routeHandler['middleware'] as $id => $middleware) {
                    $identifier = "middleware.$id";
                    $builder->registerConfiguration([$identifier => $middleware]);
                    $routeHandler['middleware'][$id] = $identifier;
                }
            }

            $provider->addRouteDefinition(new RouteDefinition($name, [$method], $path, $routeHandler));
            $builder->registerConfiguration([$name => $handler]);
        }

        $router = new Router($provider);

        $builder->registerProvider(new ApplicationProvider());
        $builder->registerConfiguration([Router::class => $router]);

        $container = $builder->getContainer();

        unset($container[HttpClient::class], $container[HttpFactoryInterface::class]);

        $container[HttpClient::class] = $this->getHttpClient();
        $container[HttpFactoryInterface::class] = $this->httpFactory;

        $this->container = $container;
    }

    private function getHttpClient($headersSent = false, $connectionAborted = false): HttpClient
    {
        $this->serverApi = $this->createMock(ServerApi::class);
        $this->serverApi->method('isHeadersSent')->willReturn($headersSent);
        $this->serverApi->method('sendHeaderLine')->willReturnCallback(function (string $line): void {
            $this->sentHeaders[] = $line;
        });
        $this->serverApi->method('output')->willReturnCallback(function (string $output): void {
            $this->sentOutput .= $output;
        });
        $this->serverApi->method('isConnectionAborted')->willReturn($connectionAborted);

        $client = new HttpClient();
        $client->setServerApi($this->serverApi);

        return $client;
    }

    /**
     * @param Response|string $response
     * @return MockObject|RequestHandlerInterface
     */
    private function getHandler($response): RequestHandlerInterface
    {
        /** @var MockObject|RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(function () use ($response) {
            if (!$response instanceof Response) {
                return $this->getResponse($response);
            }

            return $response;
        });

        return $handler;
    }

    private function getResponse(string $body, array $headers = null): Response
    {
        $response = (new ResponseFactory())->createResponse();
        $response = $response->withBody((new StreamFactory())->createStream($body));

        if ($headers === null) {
            $headers = [
                'Content-Type' => 'text/plain; charset=utf-8',
            ];
        }

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
