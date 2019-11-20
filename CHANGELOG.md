# Change Log for OXID eShop testing library

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [v7.1.0] - 2019-11-20

### Added
- command "reset-shop" sets default values for module configurations

## [v7.0.1] - 2019-11-07

### Fixed
- Non stable versions of oxid-esales/codeception components.

## [v7.0.0] - 2019-11-07

### Changed
 - Backwards compatibility break: changed shop services namespace.
 - Update of Codeception version to 3.1
 
## [v6.0.1] - 2019-07-30

### Added
 - ProjectConfiguration service for handling backup and restore of project configuration file.
 - Codeception test runner: bin/runtests-codeception
 - Extend Cache::clearReverseProxyCache() method to also clear cache for Nginx and Varnish module.  

### Changed
 - Improved message when making screenshot
 - Switch to subshop when activating a module for subshop.
 - Destroy PHP session at test tear down.
 - Use Printer::write() rather than print or echo for test result output.   

## [v6.0.0] - 2018-09-17

### Changed
 - Backwards compatibility break: Removes goutte driver for acceptance tests
 - Removes unnecessary exception translation in AcceptanceTestCase
 - Updates Symfony yaml component to version ~3
 - Backwards compatibility break: Updated phpunit dependency to version 6
   - Changes PHP Unit classes to namespaced versions
   - \OxidEsales\TestingLibrary\Printer methods use namespaced phpunit classes as arguments
   - \OxidEsales\TestingLibrary\UnitTestCase::getSession() and getConfig() not static any more.    
   - Calls to prior methods of PHPUnit_Framework_TestCase (base class of \OxidEsales\TestingLibrary\UnitTestCase) not possible any more. An example is \OxidEsales\TestingLibrary\UnitTestCase::setExpectedException(). Use \OxidEsales\TestingLibrary\UnitTestCase::expectException() for this example instead. Please have a look at the changelog of phpunit version 5 and 6 for other changes.
   - \OxidEsales\TestingLibrary\AcceptanceTestCase::onNotSuccessfulTest(): Fixed method signature for phpunit 6 
 - PHP 7.2 support
 - Added screenshots for selenium functionality
 
### Deprecated
 - \OxidEsales\TestingLibrary\UnitTestCase::getMock(): Simulated the getMock() method that is deprecated from phpunit Version 5  

## [v5.0.7] - 2019-07-30

### Fixed
 - AcceptanceTestCase::assertElementText() was not doing any assertions

## [v5.0.6] - 2019-03-28

### Changed
 - Remove database defaults file (.cnf) after it was used
 - Do not silence shell output when mysql command is called

## [v5.0.5] - 2019-03-28

### Fixed
 - Warnings when using MySQL 5.7

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

[v7.1.0]: https://github.com/OXID-eSales/testing_library/compare/v7.0.1...v7.1.0
[v7.0.1]: https://github.com/OXID-eSales/testing_library/compare/v7.0.0...v7.0.1
[v7.0.0]: https://github.com/OXID-eSales/testing_library/compare/v6.0.1...v7.0.0
[v6.0.1]: https://github.com/OXID-eSales/testing_library/compare/v6.0.0...v6.0.1
[v6.0.0]: https://github.com/OXID-eSales/testing_library/compare/v5.0.3...v6.0.0
[v5.0.7]: https://github.com/OXID-eSales/testing_library/compare/v5.0.6...v5.0.7
[v5.0.6]: https://github.com/OXID-eSales/testing_library/compare/v5.0.5...v5.0.6
[v5.0.5]: https://github.com/OXID-eSales/testing_library/compare/v5.0.4...v5.0.5
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
