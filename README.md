# Middleware Application Framework

[![Travis](https://img.shields.io/travis/simply-framework/application.svg?style=flat-square)](https://travis-ci.org/simply-framework/application)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/simply-framework/application.svg?style=flat-square)](https://scrutinizer-ci.com/g/simply-framework/application/)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/simply-framework/application.svg?style=flat-square)](https://scrutinizer-ci.com/g/simply-framework/application/)
[![Packagist](https://img.shields.io/packagist/v/simply/application.svg?style=flat-square)](https://packagist.org/packages/simply/application)

This package provides a bare-bones middleware framework that implements the different PSR standards and
takes advantage of the Simply Router and Container to create a coherent whole.

The application framework takes advantage of the [PSR-7 HTTP Message Interface], [PSR-11 Container Interface]
and [PSR-15 HTTP Handlers] while also looking into being compatible with upcoming [PSR-17 HTTP Factories] via
the http-factory interop.

NOTE: This package is part of a framework that is still highly experimental in nature. Stable api or proper
documentation are not to be expected until the framework has been tested in practice.

API documentation is available at: https://docs.riimu.net/simply/application/

## Credits
 
This library is Copyright (c) 2018 Riikka Kalliomäki.

See LICENSE for license and copying information.

[PSR-7 HTTP Message Interface]: https://www.php-fig.org/psr/psr-7
[PSR-11 Container Interface]: https://www.php-fig.org/psr/psr-11
[PSR-15 HTTP Handlers]: https://www.php-fig.org/psr/psr-15
[PSR-17 HTTP Factories]: https://github.com/php-fig/fig-standards/tree/master/proposed/http-factory/
