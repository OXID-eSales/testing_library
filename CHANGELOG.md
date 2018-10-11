# Change Log for OXID eShop testing library

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [v6.0.0] - 2018-09-17

## Changed
 - Removes goutte driver for acceptance tests
 - Removes unnecessary exception translation in AcceptanceTestCase
 - Updates Symfony yaml component to version ~3
 - Changes PHP Unit classes to namespaced versions
 - Fixes static calls in UnitTestCase
 - Updated phpunit dependency to version 6
 - \OxidEsales\TestingLibrary\AcceptanceTestCase::onNotSuccessfulTest(): Fixed method signature for phpunit 6
 - \OxidEsales\TestingLibrary\UnitTestCase::getMock(): Simulated the getMock() method that is deprecated from
 phpunit Version 5
 - Updated phpunit dependency to version 5 

## [v5.0.4] - 2018-10-05

### Changed
 - Ported changes from v4.0.2 

## [v5.0.3] - 2018-07-24

### Changed
 - ShopInstaller: Pass proper PHP version to oe-eshop-db_views_regenerate command if PHPBIN is set in the environment
 
## [v5.0.2] - 2018-07-16

### Changed
 - \OxidEsales\TestingLibrary\ModuleLoader::installModule move call to ModuleLoader::clearModuleChain 
 to ModuleLoader::activateModules
 
## [v5.0.1] - 2018-07-16

### Changed
 - \OxidEsales\TestingLibrary\ModuleLoader::installModule flushes table description cache before activation 
 
## [v5.0.0] - 2018-07-09

### Changed
 - expected log file name was changed to 'source/oxideshop.log'
 - expected log file format was changed
 - require v2.0.0 of oxid-esales/oxideshop-unified-namespace-generator

## [v4.0.2] - 2018-09-17

### Changed
- Invalidate cached objects after module activation in `\OxidEsales\TestingLibrary\ModuleLoader::installModule`

## [v4.0.1] - 2018-07-16

### Changed
- Added exception handling to `\OxidEsales\TestingLibrary\AcceptanceTestCase::formException`
- Special treatment of goute driver in `\OxidEsales\TestingLibrary\AcceptanceTestCase::assertElementPresent`
- Flushes table description cache before activation in `\OxidEsales\TestingLibrary\ModuleLoader::installModule`  

## [v4.0.0] - 2018-04-30

### Deprecated
- `\OxidEsales\TestingLibrary\AcceptanceTestCase::shouldReformatExceptionMessage`

## [v4.0.0-beta.1] - 2018-01-17

### Added

- class \OxidEsales\TestingLibrary\helpers\ExceptionLogFileHelper holds some helper methods for exception log file
- \OxidEsales\TestingLibrary\BaseTestCase::__construct an instance of ExceptionLogFileHelper is assigned within the constructor
- \OxidEsales\TestingLibrary\BaseTestCase::activateTheme moved from \OxidEsales\TestingLibrary\AcceptanceTestCase
- \OxidEsales\TestingLibrary\BaseTestCase::failOnLoggedExceptions makes a test fail, if exception log is not empty
- \OxidEsales\TestingLibrary\BaseTestCase::setUp calls failOnLoggedExceptions now
- \OxidEsales\TestingLibrary\BaseTestCase::tearDown calls failOnLoggedExceptions now
- \OxidEsales\TestingLibrary\BaseTestCase::assertLoggedException validates an expected entry in the exception log file 

### Changed

### Deprecated
- \OxidEsales\TestingLibrary\UnitTestCase::stubExceptionToNotWriteToLog was using deprecated oxTestModules and was made obsolete now

### Removed
- \OxidEsales\TestingLibrary\AcceptanceTestCase::activateTheme moved to \OxidEsales\TestingLibrary\BaseTestCase as this method is needed by some integration tests too

### Fixed
- Stabilize acceptance tests: after first failure test data were not completely restored, so if same test was triggered
once again, it could have failed because of previously failed test influence.

### Security

## [v3.3.2] - 2018-01-11

### Changed

- Updated oxDatabaseHelper class. Will use single parameter for database connection.

## [v3.3.1] - 2017-12-21

### Changed

Updated DatabaseRestorer class. Force usage of master database server on reads
when restore tables. This was not always working in master slave setup.
An example:
- Test deletes table
- Testing Library reads list of tables from salve
- Replication deletes the table from salve
- Testing Library tries to delete the table from master
- Exception is raised as the table is already gone

## [v3.3.0] - 2017-12-05

### Added

- add helping methods markTestSkippedIfSubShop and markTestSkippedIfNoSubShopto the library/UnitTestCase


[v5.0.4]: https://github.com/OXID-eSales/testing_library/compare/v5.0.3...v5.0.4
[v5.0.3]: https://github.com/OXID-eSales/testing_library/compare/v5.0.2...v5.0.3
[v5.0.2]: https://github.com/OXID-eSales/testing_library/compare/v5.0.1...v5.0.2
[v5.0.1]: https://github.com/OXID-eSales/testing_library/compare/v5.0.0...v5.0.1
[v5.0.0]: https://github.com/OXID-eSales/testing_library/compare/v4.0.0...v5.0.0
[v4.0.2]: https://github.com/OXID-eSales/testing_library/compare/v4.0.1...v4.0.2
[v4.0.1]: https://github.com/OXID-eSales/testing_library/compare/v4.0.0...v4.0.1
[v4.0.0]: https://github.com/OXID-eSales/testing_library/compare/v4.0.0-beta.1...v4.0.0
[v4.0.0-beta.1]: https://github.com/OXID-eSales/testing_library/compare/v3.3.2...v4.0.0-beta.1
[v3.3.2]: https://github.com/OXID-eSales/testing_library/compare/v3.3.1...v3.3.2
[v3.3.1]: https://github.com/OXID-eSales/testing_library/compare/v3.3.0...v3.3.1
[v3.3.0]: https://github.com/OXID-eSales/testing_library/compare/v3.2.0...v3.3.0
