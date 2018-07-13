<?php

namespace Simply\Application;

use PHPUnit\Framework\TestCase;

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
}
