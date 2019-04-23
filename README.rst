OXID eShop Testing Library
==========================

.. image:: https://img.shields.io/packagist/v/oxid-esales/testing-library.svg?maxAge=3600
    :target: https://packagist.org/packages/oxid-esales/testing-library

The OXID eShop testing library can be used to test OXID eShop with
existing or new Unit, Integration, Mink or QUnit tests. Furthermore, it
can also be very helpful to developers who create a module for the OXID
eShop.

This library is meant to help developers check their shop/module code
with ease. It contains all the required tools and dependencies to
execute unit tests, selenium tests, metrics.

Requirements
------------

-  Latest Composer version
-  PHP cURL extension
-  Not compatible with Windows (use VM instead)

This library can be used to test modules with earlier shop versions, but
it will not be possible to run shop tests.

Installation
------------

Testing library setup uses composer to get required packages, so make
sure to have composer installed and accessible. You can find composer
installation guide `here <https://getcomposer.org/download/>`__.

Selecting where to install testing library
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Testing library can be installed directly within shop or to any other
directory. However, installation varies slightly depending on selected
location. We advise to install it using shop directory.

Option 1: Selecting shop directory for installation (preferred way)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

To install testing library within shop directory, update/create
``composer.json`` with following values:

.. code:: json

    {
        "name": "oxid-esales/eshop",
        "description": "OXID eShop",
        "type": "project",
        "keywords": ["oxid", "modules", "eShop"],
        "homepage": "https://www.oxid-esales.com/en/home.html",
        "license": [
            "GPL-3.0",
            "proprietary"
        ],
        "require-dev": {
            "oxid-esales/testing-library": "dev-master",
            "incenteev/composer-parameter-handler": "~2.0"
        },
        "minimum-stability": "dev",
        "prefer-stable": true,
        "scripts": {
            "post-install-cmd": [
                "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters"
            ],
            "post-update-cmd": [
                "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters"
            ]
        },
        "extra": {
            "incenteev-parameters": {
                "file": "test_config.yml",
                "dist-file": "vendor/oxid-esales/testing-library/test_config.yml.dist",
                "parameter-key": "mandatory_parameters",
                "env-map": {
                    "shop_path": "SHOP_PATH",
                    "shop_tests_path": "SHOP_TESTS_PATH",
                    "partial_module_paths": "PARTIAL_MODULE_PATHS"
                }
            }
        }
    }

Installing this way, binaries will be accessible from
``shop_source_path/vendor/bin``. Latest development shop version already
includes composer.json file in its source, so no changes need to be
made.

Option 2: Selecting any directory for installation (alternative way)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

To install testing library to any directory, you need to checkout
testing library from Github into desired directory
(``git clone https://github.com/OXID-eSales/testing_library``).
Installing this way, binaries will be accessible from
``testing_library/bin``.

Installing testing library
~~~~~~~~~~~~~~~~~~~~~~~~~~

After you selected where you want to install the testing library, follow
these steps:

1. Navigate to the directory that you picked for installation.
2. Use composer to setup testing library components
   (``composer install``). Ensure you do this from within the directory
   where ``composer.json`` is located. During setup you will be asked
   several questions regarding testing library configuration. These
   options will be explained in more detail here: `Parameter
   explanation <README.md#configuration>`__

Running tests
-------------

First and foremost - make sure you have a working shop, meaning:

1. Shop is installed/configured (``config.inc.php`` is filled in with
   database connection details and so)
2. Shop can be accessed through url (used for shop installation).

Several test runners are available for use once testing library is prepared.
These are available in ``bin`` or ``vendor/bin`` directory:

* ``runtests`` - run shop/module unit and integration tests.
* ``runtests-selenium`` - run shop/module selenium tests.
* ``runtests-coverage`` - run shop/module tests with code coverage.
* ``runmetrics`` - execute code metrics test for shop/module.

Additionally you can pass parameters to these scripts. ``runmetrics``
uses ``pdepend``, and all ``runtests`` uses ``phpunit``. You can add
``phpunit`` parameters to ``runtests``, ``runtests-selenium``,
``runtests-coverage``. You can add ``pdepend`` parameters to
``runmetrics``. To see which additional options can be passed to test
runner, add ``--help`` option to the command (i.e.
``./runtests --help``, ``./runmetrics --help``). This will show
available options for desired tool.

Some usage examples:

1. Running only a single file tests -
   ``bin/runtests path/to/test/fileTest.php``
2. Running only specific pattern matching tests from specified file -
   ``bin/runtests --filter match_pattern path/to/test/fileTest.php``
3. Running one or multiple test suites -
   ``TEST_DIRS=unit,integration bin/runtests``

One thing to note when adding parameters to these tools - always provide
file/folder at the end as it will no longer be picked automatically. Use
AllTestsUnit or AllTestsSelenium respectively to run all tests.

Run with PHPStorm
~~~~~~~~~~~~~~~~~

You need to setup path to bootstrap in order to run tests with PHPStorm.

1. Open PHPStorm.
2. Open Run/Debug configurations window.
3. Press on configuration icon (wrench) right after the "Use alternative
   configuration file" input.
4. New PHPUnit window pops up. Check "Default bootstrap file" checkbox
   and choose path to bootstrap.
   Path to bootstrap:
   {source}/vendor/oxid-esales/testing-library/bootstrap.php

Configuration
-------------

Configuration file is named ``test_config.yml`` and is placed in the
root directory of this library or shop (when installing with shop
composer.json). During setup you will be asked several questions
regarding testing library and shop/module installation. After setup
``test_config.yml`` will be created, and later can be edited if some
configuration values need to be changed.

All of the parameters can be set with environmental variables.
Environmental parameter names are the same as in the configuration file,
but should be all uppercase: *shop\_path* => *SHOP\_PATH*,
*browser\_name* => *BROWSER\_NAME*, etc.

Configuration parameters
~~~~~~~~~~~~~~~~~~~~~~~~

Mandatory parameters
^^^^^^^^^^^^^^^^^^^^

These parameters are required for testing library to work properly.

+------------------------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Parameter name               | Description                                                                                                                                                                                                                                                                                                                                                           |
+==============================+=======================================================================================================================================================================================================================================================================================================================================================================+
| **shop\_path**               | Path to eShop source. Defaults to the same directory as to where vendor is located. Supports relative and absolute paths. Can be left empty when installed from shop or module directory.                                                                                                                                                                             |
+------------------------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **shop\_tests\_path**        | Path to eShop tests. If shop resides in ``/var/www/shop/source`` and tests are in ``/var/www/shop/tests``, this should be set to ``../tests``. Supports relative and absolute paths.                                                                                                                                                                                  |
+------------------------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **partial\_module\_paths**   | When testing not activated module, specify module path in shop. Module path in shop, e.g. if module is in ``shop/modules/oe/mymodule`` directory, value here should be ``oe/mymodule``. Multiple modules can be specified separated by comma: ``oe/module1,module2,tt/module3``. If no modules will be tested, leave this field empty or ``null``. Default ``null``   |
+------------------------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

Optional parameters
^^^^^^^^^^^^^^^^^^^

These parameters are not required in order to work, but they provide
additional functionality and options when testing.

+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Parameter name                           | Description                                                                                                                                                                                                                                                                                             |
+==========================================+=========================================================================================================================================================================================================================================================================================================+
| **shop\_url**                            | eShop base url (if not set, takes it from shop's config.inc.php file). Default ``null``.                                                                                                                                                                                                                |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **shop\_serial**                         | For PE and EE editions shop serial has to be specified for shop installer to work. Default ``''``.                                                                                                                                                                                                      |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **enable\_varnish**                      | Run tests with varnish on or off. Shop has to be configured to work with varnish, correct serial must be used. Default ``false``                                                                                                                                                                        |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **is\_subshop**                          | Whether to run subshop tests. Currently only used when running selenium tests. Default ``false``.                                                                                                                                                                                                       |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **install\_shop**                        | Whether to prepare shop database for testing. Shop ``config.ing.php`` file must be correct. Default ``true``.                                                                                                                                                                                           |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **remote\_server\_dir**                  | If defined, testing services will be copied to this directory and called via url instead of used locally. Example: username@server.com:/path/to/shop.                                                                                                                                                   |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **shop\_setup\_path**                    | eShop setup directory. After setting up the shop, setup directory will be deleted. For shop installation to work during tests run, path to this directory must be specified. If not set, uses default (i.e. shop dir ``/var/www/eshop/source/``, default setup dir ``/var/www/eshop/source/setup`` ).   |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **restore\_shop\_after\_tests\_suite**   | Whether to restore shop data after running all tests. If this is set to false, shop will be left with tests data added on it. Default ``false``.                                                                                                                                                        |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **restore\_after\_acceptance\_tests**    | Whether to dump and restore the database after a single acceptance test.                                                      Default ``true``.                                                                                                                                                         |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **restore\_after\_unit\_tests**          | Whether to dump and restore the database after all tests are finished in a single unit, integration test suite.               Default ``true``.                                                                                                                                                         |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **test\_database\_name**                 | If specified, this database is used instead of real one for unit and integration tests                                                                                                                                                                                                                  |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **tmp\_path**                            | If php has no write access to /tmp folder, provide alternative temp folder for tests.                                                                                                                                                                                                                   |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **database\_restoration\_class**         | Currently exists ``DatabaseRestorer`` and ``LocalDatabaseRestorer``. ``LocalDatabaseRestorer`` - is faster, but does not work with remote databases, ``DatabaseRestorer`` - can be used with external database. Default ``DatabaseRestorer``.                                                           |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **activate\_all\_modules**               | Whether to activate all modules defined in modules\_path when running tests. Normally only tested module is activated during test run. Modules will be activated in the specified order. Default ``false``.                                                                                             |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **run\_tests\_for\_shop**                | Whether to run shop unit tests. This applies only when correct shop\_tests\_path are set. Default ``true``.                                                                                                                                                                                             |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **run\_tests\_for\_modules**             | Whether to run modules unit tests. All modules provided in modules\_path will be tested. If shop\_tests\_path and run\_shop\_tests are set, shop tests will be run with module tests. Default ``true``.                                                                                                 |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **screen\_shots\_path**                  | Folder where to save selenium screen shots. If not specified, screenshots will not be taken. Default ``null``. Default ``false``.                                                                                                                                                                       |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **screen\_shots\_url**                   | Url, where selenium screen shots should be available. Default ``null``.                                                                                                                                                                                                                                 |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **browser\_name**                        | Browser name which will be used for acceptance testing. Possible values: ``*iexplore, *iehta, *firefox, *chrome, *piiexplore, *pifirefox, *safari, *opera``. make sure that path to browser executable is known for the system. Default ``firefox``.                                                    |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **selenium\_server\_ip**                 | Selenium server IP address. Used to connect to selenium server when Mink selenium driver is used for acceptance tests. Default ``127.0.0.1``.                                                                                                                                                           |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **additional\_test\_paths**              | Used for running additional tests. It's possible to add paths separated by comma. Loads tests in same manner as eShop or modules tests.                                                                                                                                                                 |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| **retry\_times\_after\_test\_fail**      | How many times to try test before marking it as failure. Could be used for unstable tests which fails randomly.                                                                                                                                                                                         |
+------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

Changing PHPUnit parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To change PHPUnit parameters, add phpunit.xml file inside tests
directory and it will be used.

Execution before UNIT test run with additional.inc.php
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Testing library gives possibility to make some actions before UNIT test run.
So if there is a need to to do that, add additional.inc.php file into
tests directory and it will be executed.

**Note:**

  Use addTestData() method and testSql directory to change environment for Acceptance tests.
  Read more in a section Writing acceptance tests.

Writing Tests
-------------

Directory Structure
~~~~~~~~~~~~~~~~~~~

Module tests should be placed in module root directory:
``path/to/shop/modules/my_module/tests``. Tests can by placed in three
directories: unit, integration and acceptance depending on tests type.
``./runtests`` collects tests from unit and integration directories,
while ``./runtests-selenium`` - from acceptance. Code coverage is
calculated from both unit and integration tests.

Writing unit and integration tests
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Unit and integration should be placed under ``tests/unit`` and
``tests/integration`` directories. Any number of subdirectories can be
created inside - all tests will be collected. Unit and integration tests
should extend ``OxidEsales\TestingLibrary\UnitTestCase`` class so that
database, registry, configuration parameters restoration, module
activation would work. If unit tests are not relying on database or
registry and are real clean unit tests, ``PHPUnit\Framework\TestCase``
class can be extended, but have in mind that autoloading of module
classes and correct shop classes extension will not work. All
preparation works can be done in ``additional.inc.php`` file. This file
is loaded before database dump creation and before running any of the
test, so can be used autoloaders registration, demodata preparation,
etc. For unit testing shop is installed without default demodata added.

Methods usage
^^^^^^^^^^^^^

Run unit and integration test dependent if Subshops is enabled:

.. code:: php

    public function testCase_forSubShops()
    {
        $this->markTestSkippedIfSubShop();
        ...
    }

    public function testCase_forNoSubShops()
    {
        $this->markTestSkippedIfNoSubShop();
        ...
    }

Rest of the methods can be found in class: ``OxidEsales\TestingLibrary\UnitTestCase``.

Writing acceptance tests
~~~~~~~~~~~~~~~~~~~~~~~~

Currently for acceptance testing Mink library and selenium driver is used.

**Note:**

  selenium-server-standalone-jar 2.47.1 is used for testing.
  There might be some issues with older versions `issue #13 <https://github.com/OXID-eSales/testing_library/issues/13>`__

Acceptance tests should be placed under
``tests/acceptance`` directory and extend
``OxidEsales\TestingLibrary\AcceptanceTestCase``.

Tested module will be activated by default.
Some data might be also added by extending
``AcceptanceTestCase::addTestData()`` method and activating module
manually. This method will be run before any test and before database
dump creation, once per tests suite.

For acceptance testing
shop is installed with default demodata. Additional demodata can be
added to ``testSql`` directory by the name of ``demodata_EE.sql``,
``demodata_PE_CE.sql`` or ``demodata_EE_mall.sql`` (when subshop
functionality is enabled in test\_config). These files will be loaded on
top of the database depending on the shop edition. Any additional files,
needed for testing can be placed under ``testData`` directory - all
content will be copied onto the shop source before running tests.

A useful method for preparing the shop is
``AcceptanceTestCase::callShopSC()``. With this method
you can e.g. insert a new article or modify config variables. For detailed
usage examples have a look at the OXID eShop acceptance tests.

Methods usage
^^^^^^^^^^^^^

Testing library provides methods which allows write tests easier. Some methods usages are described bellow:

Activating theme:

.. code:: php

    // This will activate azure theme.
    $this->activateTheme('azure');

Add article to basket:

.. code:: php

    // This will add article with ID 1001 to basket.
    $this->addToBasket("1001");

Update items amount in basket: *(Note that item must be in basket in order to change it)*

.. code:: php

    // This will update article with ID 1001 in basket to have 2 items.
    $this->changeBasket("1001", 2);

.. code:: php

    // This will remove an item from basket.
    $this->changeBasket("1001", 0);

Login user in front end side:

.. code:: php

    $this->loginInFrontend("example_test@oxid-esales.dev", "useruser");

Rest of the methods can be found in class: ``OxidEsales\TestingLibrary\AcceptanceTestCase``.

Changing database restoration mechanism
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Currently there are two database restoration classes available -
``DatabaseRestorer`` and ``LocalDatabaseRestorer``. Both of these are
truncating changed tables and adding all the information back in. If
provided solutions are not fitting your needs, it can be changed by
implementing
``OxidEsales\TestingLibrary\DatabaseRestorer\DatabaseRestorerInterface``
interface and registering new class in
``test_config.yml::database_restoration_class``.

Library API
~~~~~~~~~~~

- ``test_config.yml`` parameters
- ``OxidEsales\TestingLibrary\AcceptanceTestCase``
- ``OxidEsales\TestingLibrary\UnitTestCase``
- ``\OxidEsales\TestingLibrary\ServiceCaller``
- ``OxidEsales\TestingLibrary\Services`` called via ``\OxidEsales\TestingLibrary\ServiceCaller``

Testing library branch names
----------------------------

Testing library branch names follow the OXID eShop component branch names. Which means that branch names
do not follow testing library version numbers, but represent the target version of the OXID eShop compilation.
For example:

===========   ===========================================================
Branch Name   OXID eShop Compilation Target Version
-----------   -----------------------------------------------------------
b-6.1.x       v6.1.*  (Next patch release of OXID eShop compilation v6.1)
b-6.x         v6.*  (Next minor release of OXID eShop compilation v6)
master        v* (Next major release of OXID eShop compilation)
===========   ===========================================================

Tests running workflow
----------------------

Graphically visualized workflow can be found in workflow.puml. This file can be opened with tool called PlantUml (http://plantuml.com/).
