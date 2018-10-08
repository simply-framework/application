<?php

namespace Simply\Application;

use Psr\Http\Message\ResponseInterface;

/**
 * The http client that facilitates sending responses to the browser client.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class HttpClient
{
    /** @var bool Whether to omit the body of the response or not */
    private $omitBody;

    /** @var int Size of the chunks in bytes sent to the browser */
    private $chunkSize;

    /** @var ServerApi The api used to actually communicate to the browser */
    private $serverApi;

    /**
     * HttpClient constructor.
     */
    public function __construct()
    {
        $this->omitBody = false;
        $this->chunkSize = 8 * 1024;
        $this->serverApi = new ServerApi();
    }

    /**
     * Sets the api used to communicate to the client.
     * @param ServerApi $api The api used to communicate to the client
     */
    public function setServerApi(ServerApi $api): void
    {
        $this->serverApi = $api;
    }

    /**
     * Sets the size of the chunks in bytes that are sent to the browser.
     * @param int $bytes Size of chunks in bytes
     */
    public function setResponseChunkSize(int $bytes): void
    {
        if ($bytes < 1) {
            throw new \InvalidArgumentException('The response chunk size must be at least 1');
        }

        $this->chunkSize = $bytes;
    }

    /**
     * Tells the client to omit the body from the response.
     * @param bool $omit True to omit the body, false to include it
     */
    public function omitBody(bool $omit = true): void
    {
        $this->omitBody = $omit;
    }

    /**
     * Sends the given response to the browser.
     * @param ResponseInterface $response The response to send
     * @return int The number of bytes outputted
     */
    public function send(ResponseInterface $response): int
    {
        $length = 0;

        if ($this->hasResponseBody($response)) {
            $length = $this->detectLength($response);
        }

        if ($length !== null) {
            $response = $response->withHeader('Content-Length', (string) $length);
        }

        if (!$this->serverApi->isHeadersSent()) {
            $this->sendHeaders($response);
        }

        if ($length === null || $length > 0) {
            return $this->sendBody($response, $length);
        }

        return 0;
    }

    /**
     * Tells if we are supposed to output the body of the response.
     * @param ResponseInterface $response The response to send
     * @return bool True if we should send the body, false otherwise
     */
    private function hasResponseBody(ResponseInterface $response): bool
    {
        return !$this->omitBody && !\in_array($response->getStatusCode(), [204, 205, 304], true);
    }

    /**
     * Attempts to detect the length of the response for the content-length header.
     * @param ResponseInterface $response The response used to detect the length
     * @return int|null Length of the response in bytes or null if it cannot be determined
     */
    private function detectLength(ResponseInterface $response): ?int
    {
        if ($response->hasHeader('Content-Length')) {
            $lengthHeader = $response->getHeader('Content-Length');
            return array_shift($lengthHeader);
        }

        return $response->getBody()->getSize();
    }

    /**
     * Sends the headers for the given response.
     * @param ResponseInterface $response The response to send
     */
    private function sendHeaders(ResponseInterface $response): void
    {
        $this->serverApi->sendHeaderLine(sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ));

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $this->serverApi->sendHeaderLine(sprintf('%s: %s', $name, $value));
            }
        }
    }

    /**
     * Sends the body for the given response.
     * @param ResponseInterface $response The response to send
     * @param int|null $length Length of body or null if unknown
     * @return int The number of bytes sent to the browser
     */
    private function sendBody(ResponseInterface $response, ?int $length): int
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        $bytes = 0;

        while (!$body->eof()) {
            $read = $length === null ? $this->chunkSize : min($this->chunkSize, $length - $bytes);
            $output = $body->read($read);
            $this->serverApi->output($output);

            $bytes += \strlen($output);

            if ($this->serverApi->isConnectionAborted()) {
                break;
            }

            if ($length !== null && $bytes >= $length) {
                break;
            }
        }

        return $bytes;
    }
}
