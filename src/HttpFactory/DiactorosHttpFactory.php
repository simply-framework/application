<?php

namespace Simply\Application\HttpFactory;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;
use Zend\Diactoros\UploadedFile;
use Zend\Diactoros\Uri;

/**
 * A http factory that uses Zend's Diactoros library to implement the requests and responses.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DiactorosHttpFactory implements HttpFactoryInterface
{
    /** {@inheritdoc} */
    public function createRequest(string $method, $uri): RequestInterface
    {
        if (!$uri instanceof UriInterface) {
            $uri = $this->createUri($uri);
        }

        return new Request($uri, $method);
    }

    /** {@inheritdoc} */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return (new Response())
            ->withStatus($code, $reasonPhrase);
    }

    /** {@inheritdoc} */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (!$uri instanceof UriInterface) {
            $uri = $this->createUri($uri);
        }

        return new ServerRequest($serverParams, [], $uri, $method);
    }

    /** {@inheritdoc} */
    public function createServerRequestFromGlobals(): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals();
    }

    /** {@inheritdoc} */
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($content);
        $stream->rewind();

        return $stream;
    }

    /** {@inheritdoc} */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new Stream($filename, $mode);
    }

    /** {@inheritdoc} */
    public function createStreamFromResource($resource): StreamInterface
    {
        if (!\is_resource($resource)) {
            throw new \InvalidArgumentException('Unexpected argument type, expected a resource');
        }

        return new Stream($resource);
    }

    /** {@inheritdoc} */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {
        if ($size === null) {
            $size = $stream->getSize();

            if ($size === null) {
                throw new \RuntimeException('Cannot determine the size of the uploaded file');
            }
        }

        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    /** {@inheritdoc} */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
