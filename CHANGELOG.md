# Change Log for OXID eShop testing library

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [v4.0.0] - Unreleased

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

## [3.3.2] - 2018-01-11

### Changed

- Updated oxDatabaseHelper class. Will use single parameter for database connection.

## [3.3.1] - 2017-12-21

### Changed

Updated DatabaseRestorer class. Force usage of master database server on reads
when restore tables. This was not always working in master slave setup.
An example:
- Test deletes table
- Testing Library reads list of tables from salve
- Replication deletes the table from salve
- Testing Library tries to delete the table from master
- Exception is raised as the table is already gone

## [3.3.0] - 2017-12-05

### Added

- add helping methods markTestSkippedIfSubShop and markTestSkippedIfNoSubShopto the library/UnitTestCase
