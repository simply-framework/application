<?php

namespace Simply\Application;

/**
 * The default system api used to interact with the browser.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ServerApi
{
    /**
     * Tells if the headers have been sent and no more headers can be provided.
     * @return bool True if no more headers can be provided, false otherwise
     */
    public function isHeadersSent(): bool
    {
        return headers_sent();
    }

    /**
     * Sets a header line to be sent to the browser.
     * @param string $line The header line to send to browser
     * @codeCoverageIgnore
     */
    public function sendHeaderLine(string $line): void
    {
        header($line, false);
    }

    /**
     * Outputs the given string to the browser.
     * @param string $output The string to output
     */
    public function output(string $output): void
    {
        echo $output;
    }

    /**
     * Tells if the connection to the user have been terminated and no more output can be sent.
     * @return bool True if no more output can be sent, false otherwise
     */
    public function isConnectionAborted(): bool
    {
        return connection_status() !== \CONNECTION_NORMAL;
    }
}
