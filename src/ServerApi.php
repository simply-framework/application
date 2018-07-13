<?php

namespace Simply\Application;

/**
 * SystemApi.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ServerApi
{
    public function isHeadersSent(): bool
    {
        return headers_sent();
    }

    /**
     * @param string $line
     * @codeCoverageIgnore
     */
    public function sendHeaderLine(string $line): void
    {
        header($line, false);
    }

    public function output(string $output): void
    {
        echo $output;
    }

    public function isConnectionAborted(): bool
    {
        return connection_status() !== \CONNECTION_NORMAL;
    }
}
