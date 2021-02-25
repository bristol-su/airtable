# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - (25/02/2021)

### Changed
- portal-42: Dynamically update rows rather than replacing them each sync.
- The `uniqueIdColumnName` attribute is required for any AirTable driver configuration.

## [1.0.4] - (18/06/2020)

### Added
- Debug mode

## [1.0.3] - (18/06/2020)

### Changed
- Flush rows job collects all IDs then dispatches jobs to delete the rows

## [1.0.2] - (17/06/2020)

### Changed
- Rate limit CreateRecords job 
- No chaining of jobs to save on message size space

## [1.0.1] - (17/06/2020)

### Changed
- Dispatch batched events for row creation to avoid a message size limit

## [1.0] - (16/06/2020)

> First Release

### Added

- AirTable Client
- AirTable Progress integration
- AirTable Control Exporter integration

[Unreleased]: https://github.com/bristol-su/control/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/bristol-su/control/compare/v1.0.4...v2.0.0
[1.0.4]: https://github.com/bristol-su/control/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/bristol-su/control/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/bristol-su/control/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/bristol-su/control/compare/v1.0...v1.0.1
[1.0]: https://github.com/bristol-su/control/releases/tag/v1.0
