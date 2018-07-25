<?php

namespace Simply\Application;

use Interop\Http\Factory\RequestFactoryInterface;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use PHPUnit\Framework\TestCase;
use Simply\Container\ContainerBuilder;

/**
 * ApplicationProviderTest.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2018 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ApplicationProviderTest extends TestCase
{
    public function testRequestFactory()
    {
        $this->assertInstanceOf(RequestFactoryInterface::class, $this->getContainer()->get(
            RequestFactoryInterface::class
        ));
    }

    public function testServerRequestFactory()
    {
        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $this->getContainer()->get(
            ServerRequestFactoryInterface::class
        ));
    }

    public function testUploadedFileFactory()
    {
        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $this->getContainer()->get(
            UploadedFileFactoryInterface::class
        ));
    }

    public function testUriFactory()
    {
        $this->assertInstanceOf(UriFactoryInterface::class, $this->getContainer()->get(
            UriFactoryInterface::class
        ));
    }

    private function getContainer()
    {
        $builder = new ContainerBuilder();
        $builder->registerProvider(new ApplicationProvider());

        return $builder->getContainer();
    }
}
