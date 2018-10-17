# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Changed
- Now depends on version `^0.3.0` of the `simply/container` library

## [0.2.1] - 2018-10-10
### Changed
- The library now depends on version `^2.0` of the `zendframework/zend-diactoros`
- The `ApplicationProvider` now directly instantiates the PSR-17 factories provided by the updated Diactoros library
- Improved some tests as result of mutation testing

### Removed
- The `HttpFactoryInterface` no longer implements the PSR-17 interfaces, and only provides the method
  `createServerRequestFromGlobals`.

## [0.2.0] - 2018-07-31
### Added
- The `ApplicationProvider` now adds container entries for the standard http factories.

### Changed
- The `ErrorHandler`, `NotFoundHandler` and `RouterMiddleware` now depend on the standard http factory interfaces.
- The library now depends on PSR-17 standard library instead of the interop library

## 0.1.0 - 2018-07-16
### Added
- Initial development release

[Unreleased]: https://github.com/simply-framework/application/compare/v0.2.1...HEAD
[0.2.1]: https://github.com/simply-framework/application/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/simply-framework/application/compare/v0.1.0...v0.2.0
