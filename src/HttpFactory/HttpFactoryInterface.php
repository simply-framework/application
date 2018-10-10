<?php

namespace Simply\Application\HttpFactory;

use Psr\Http\Message\ServerRequestInterface;

/**
 * A factory that provides the server request generated from globals.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface HttpFactoryInterface
{
    /**
     * Generates a server request from the globals.
     * @return ServerRequestInterface A server request generated from the globals
     */
    public function createServerRequestFromGlobals(): ServerRequestInterface;
}
