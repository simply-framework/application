<?php

namespace Simply\Application\HttpFactory;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * DiactorosHttpTest.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DiactorosHttpTest extends TestCase
{
    public function testCreateRequest()
    {
        $factory = new DiactorosHttpFactory();

        $request = $factory->createRequest('POST', '/request_uri/');
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/request_uri/', (string) $request->getUri());

        $request = $factory->createRequest('POST', $factory->createUri('/request_uri/'));
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/request_uri/', (string) $request->getUri());
    }

    public function testCreateStreamFromFile()
    {
        $factory = new DiactorosHttpFactory();

        $stream = $factory->createStreamFromFile(__FILE__, 'rb');
        $this->assertStringEqualsFile(__FILE__, $stream->getContents());
    }

    public function testCreateStreamFromResource()
    {
        $factory = new DiactorosHttpFactory();

        $stream = $factory->createStreamFromResource(fopen(__FILE__, 'rb'));
        $this->assertStringEqualsFile(__FILE__, $stream->getContents());
    }

    public function testCreateStreamFromInvalidResource()
    {
        $factory = new DiactorosHttpFactory();

        $this->expectException(\InvalidArgumentException::class);
        $factory->createStreamFromResource('Not a Resource');
    }

    public function testCreateUploadedFile()
    {
        $factory = new DiactorosHttpFactory();

        $file = $factory->createUploadedFile($factory->createStreamFromFile(__FILE__));
        $this->assertSame(filesize(__FILE__), $file->getSize());
    }

    public function testCreateUploadedFileWithUnknownSize()
    {
        $factory = new DiactorosHttpFactory();

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getSize')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $factory->createUploadedFile($stream);
    }
}
