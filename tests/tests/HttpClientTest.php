<?php

namespace Simply\Application;

use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;

/**
 * HttpClientTest.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class HttpClientTest extends TestCase
{
    public function testInvalidChunkSize()
    {
        $client = new HttpClient();

        $this->expectException(\InvalidArgumentException::class);
        $client->setResponseChunkSize(0);
    }

    public function testDefaultChunkSize()
    {
        $api = $this->getMockBuilder(ServerApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $api->method('isHeadersSent')->willReturn(false);
        $api->method('sendHeaderLine')->willReturn(null);
        $api->method('isConnectionAborted')->willReturn(false);

        $api->expects($this->exactly(2))
            ->method('output')
            ->withConsecutive([str_repeat('a', 8 * 1024)], ['b'])
            ->willReturn(null);

        $response = new Response\TextResponse(str_repeat('a', 8 * 1024) . 'b');

        $client = new HttpClient();
        $client->setServerApi($api);

        $this->assertSame(8 * 1024 + 1, $client->send($response));
    }

    public function testChunkSizeOne()
    {
        $api = $this->getMockBuilder(ServerApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $api->method('isHeadersSent')->willReturn(false);
        $api->method('sendHeaderLine')->willReturn(null);
        $api->method('isConnectionAborted')->willReturn(false);

        $api->expects($this->exactly(4))
            ->method('output')
            ->withConsecutive(['a'], ['b'], ['c'], ['d'])
            ->willReturn(null);

        $response = new Response\TextResponse('abcd');

        $client = new HttpClient();
        $client->setResponseChunkSize(1);
        $client->setServerApi($api);

        $this->assertSame(4, $client->send($response));
    }

    /**
     * @dataProvider getNoBodyResponseCodes
     */
    public function testNoBodyResponses(int $code)
    {
        $headers = [];
        $api = $this->getMockBuilder(ServerApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $api->method('isHeadersSent')->willReturn(false);
        $api->method('sendHeaderLine')->willReturnCallback(function ($header) use (& $headers) {
            $headers[] = $header;
        });

        $api->expects($this->never())->method('output');
        $api->expects($this->never())->method('isConnectionAborted');

        $response = new Response\TextResponse('body', $code);

        $client = new HttpClient();
        $client->setServerApi($api);

        $this->assertSame(0, $client->send($response));
        $this->assertContains('Content-Length: 0', $headers);
    }

    public function getNoBodyResponseCodes(): array
    {
        return [
            [204],
            [205],
            [304],
        ];
    }
}
