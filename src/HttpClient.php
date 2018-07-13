<?php

namespace Simply\Application;

use Psr\Http\Message\ResponseInterface;

/**
 * HttpClient.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class HttpClient
{
    private $omitBody;
    private $chunkSize;
    private $serverApi;

    public function __construct()
    {
        $this->omitBody = false;
        $this->chunkSize = 8 * 1024;
        $this->serverApi = new ServerApi();
    }

    public function setServerApi(ServerApi $api): void
    {
        $this->serverApi = $api;
    }

    public function setResponseChunkSize(int $bytes)
    {
        if ($bytes < 1) {
            throw new \InvalidArgumentException('The response chunk size must be at least 1');
        }

        $this->chunkSize = $bytes;
    }

    public function omitBody(bool $omit = true)
    {
        $this->omitBody = $omit;
    }

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

    private function hasResponseBody(ResponseInterface $response): bool
    {
        return !$this->omitBody && !\in_array($response->getStatusCode(), [204, 205, 304], true);
    }

    private function detectLength(ResponseInterface $response): ?int
    {
        if ($response->hasHeader('Content-Length')) {
            $lengthHeader = $response->getHeader('Content-Length');
            return array_shift($lengthHeader);
        }

        return $response->getBody()->getSize();
    }

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
