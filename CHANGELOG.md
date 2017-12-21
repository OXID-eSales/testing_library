# Change Log for OXID eShop testing library

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

### Added

### Changed

### Deprecated

### Removed

### Fixed

### Security

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
