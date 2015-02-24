OXID eShop Testing Library
==========================

The OXID eShop testing library can be used to test OXID eShop with existing or new Unit, Integration, Mink or QUnit tests. 
Furthermore, it can also be very helpful to developers who create a module for the OXID eShop.

This library is meant to help developers check their shop/module code with ease. It contains all the required tools and dependencies to execute unit tests, selenium tests, metrics.

## Requirements

* eShop version 4.9.\*/5.2.\* or later
* Latest Composer version

## Installation

First and foremost - make sure you have a working shop, meaning:

1. Shop is installed/configured (`config.inc.php` is filled in with database connection details and so)
1. Shop can be accessed through url.  

While testing library does not require this to be installed, it does require working shop in order to run tests successfully.

Additionally, the setup uses composer to get required packages, so make sure to have composer installed and accessable. You can find composer installation guide [here](https://getcomposer.org/download/).

### Selecting where to install testing library

Testing library can be installed directly within shop or to any other directory. However, installation varies slightly depending on selected location. We advise to install it using shop directory. 

#### Option 1: Selecting shop directory for installation (preferred way)

To install testing library within shop directory, update/create `composer.json` with following values:
```
{
    "repositories": {
        "oxid-esales/testing_library": {
            "type": "vcs",
            "url": "https://github.com/OXID-eSales/testing_library.git"
        },
        "behat/mink-selenium-driver": {
            "type": "vcs",
            "url": "https://github.com/OXID-eSales/MinkSeleniumDriver.git"
        },
        "alexandresalome/php-selenium": {
          "type": "vcs",
          "url": "https://github.com/OXID-eSales/PHP-Selenium.git"
        }
    },
    "require-dev": {
        "oxid-esales/testing_library": "master",
        "incenteev/composer-parameter-handler": "~2.0"
    },
    "minimum-stability": "dev",
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
            "dist-file": "vendor/oxid-esales/testing_library/test_config.yml.dist",
            "parameter-key": "mandatory_parameters",
            "env-map": {
              "shop_path": "SHOP_PATH",
              "shop_tests_path": "SHOP_TESTS_PATH",
              "modules_path": "MODULES_PATH"
            }
        }
    }
}
```
Installing this way, binaries will be accessible from `shop/path/vendor/bin`.

#### Option 2: Selecting any directory for installation (alternative way)

To install testing library to any directory except shop (for shop installation, see above), you need to checkout testing library from Github into desired directory (`git clone https://github.com/OXID-eSales/testing_library`). Installing this way, binaries will be accessible from `testing_library/path/bin`.

#### Installing testing library

After you selected where you want to install the testing library, follow these steps to install testing library:

1. Navigate to the directory that you picked for installation during the directory selection.
1. Use composer to setup testing library components (`composer install`). Insure you do this from within the directory where `composer.json` is located.  
During setup you will be asked several questions regarding testing library configuration. These options will be explained in more detail here: [Parameter explanation](README.md#configuration)


## Running tests
Several test runners are available for use once testing library is prepared. These are available in `bin` directory:  
`./runtests` - run shop/module unit and integration tests.  
`./runtests-selenium` - run shop/module selenium tests.  
`./runtests-coverage` - run shop tests with code coverage.  
`./runmetrics` - execute code metrics test for shop.  
Additionally you can pass parameters to these scripts. `runmetrics` uses `pdepend`, and all `runtests` uses `phpunit`.
You can add `phpunit` parameters to `runtests`, `runtests-selenium`, `runtests-coverage`.
You can add `pdepend` parameters to `runmetrics`. To see which additional options can be passed to test runner, add `--help` option to the command (i.e. `./runtests --help`, `./runmetrics --help`). This will show available options for desired tool.

Some usage examples:

1. Running only a single file tests - `./runtests path/to/test/fileTest.php`
1. Running only specific pattern matching tests from specified file - `./runtests --filter match_pattern path/to/test/fileTest.php`

One thing to note when adding parameters to these tools - always provide file/folder at the end as it will no longer be picked automatically.


### Test running tips
Some changes to configuration can change which tests are executed. Some possible usages:

1. Running only shop tests - <b>`modules_path`</b> should be set to null.  
1. Running only module tests - <b>`shop_tests_path`</b> should be set to null.  

## Configuration

Configuration file is named `test_config.yml` and is placed in the root directory of this library.
During setup you will be asked several questions regarding testing library and shop/module installation.
After setup `test_config.yml` will be created, and later can be edited when some configuration value needs to be changed.

### Configuration parameters:
#### Mandatory parameters:
These parameters are required for testing library to work properly.

| Parameter name | Description |
|----------------|-------------|
|<b>shop_path</b> | Path to eShop source. Defaults to the same directory as to where vendor is located. Supports relative and absolute paths |
|<b>shop_tests_path</b> | Path to eShop tests. If shop resides in `/var/www/shop/source` and tests are in `/var/www/shop/tests`, this should be set to `../tests`. Supports relative and absolute paths. |
|<b>modules_path</b> | When testing not activated module, specify module path in shop. Module path in shop, e.g. if module is in `shop/modules/oe/mymodule` directory, value here should be `oe/mymodule`. Multiple modules can be specified separated by comma: `oe/module1,module2,tt/module3`. |

#### Optional parameters
These parameters are not required in order to work, but they provide additional functionality and options when testing.

| Parameter name | Description |
|----------------|-------------|
|<b>shop_url</b>| eShop base url (if not set, takes it from shop's config.inc.php file). Default `null`.|  
|<b>shop_serial</b>| For PE and EE editions shop serial has to be specified for shop installer to work. Default `''`.|  
|<b>enable_varnish</b>| Run tests with varnish on or off. Default `false`  |
|<b>is_subshop</b>| Whether to run subshop tests. Currently only used when running selenium tests. Default `false`.| |<b>install_shop</b>| Whether to prepare shop database for testing. Shop `config.ing.php` file must be correct. Default `true`.|  
|<b>shop_setup_path</b>| eShop setup directory. After setting up the shop, setup directory will be deleted. For shop installation to work during tests run, path to this directory must be specified. If not set, uses default (i.e. shop dir `/var/www/eshop/source/`, default setup dir `/var/www/eshop/source/setup` ). |
|<b>restore_shop_after_test_suite</b>| Whether to restore shop data after running all tests. If this is set to false, shop will be left with tests data added on it. Default `true`.  |
|<b>selenium_server_ip</b>| Selenium server IP address. Default `127.0.0.1`.  |
|<b>selenium_screen_shots_path</b>| Folder where to save selenium screen shots. If not specified, screenshots will not be taken. Default `null`.|  
|<b>selenium_screen_shots_url</b>| Url, where selenium screen shots should be available. Default `null`.  |
|<b>browser_name</b>| Browser name which will be used for testing. Possible values: `*iexplore, *iehta, *firefox, *chrome, *piiexplore, *pifirefox, *safari, *opera`. make sure that path to browser executable is known for the system. Default `firefox`.  |
|<b>data_base_restore</b>| Currently exists `dbRestore` and `dbRestore_largeDb`. `dbRestore_largeDb` - used with local database, `dbRestore` - used with external database. Default `dbRestore`.  |
|<b>special_db_dump_folder</b>| Whether to create special directory for storing database dump during tests run. Set this to true if php has no write access to /tmp folder. Default `true`. |

## Known issues

* Some eShop 4.9/5.2 tests might fail. This is a known issue and this is already fixed in eShop development version.
