<?php

namespace Simply\Application\HttpFactory;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

/**
 * A http factory that uses Zend's Diactoros library to generate the server request.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DiactorosHttpFactory implements HttpFactoryInterface
{
    public function createServerRequestFromGlobals(): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals();
    }
}
