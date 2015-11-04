<?php
/**
 * This file is part of OXID eSales Testing Library.
 *
 * OXID eSales Testing Library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Testing Library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Testing Library. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

namespace OxidEsales\TestingLibrary;

use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorerFactory;
use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorerInterface;
use oxRegistry;
use oxDb;
use oxUtilsObject;
use oxConfig;
use oxSession;
use oxLegacyDb;
use ReflectionClass;
use PHPUnit_Framework_TestResult as TestResult;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_Exception;

use oxTestsStaticCleaner;
use oxTestModules;
use modOxid;
use modOxUtilsDate;

require_once TEST_LIBRARY_HELPERS_PATH . 'modOxUtilsDate.php';

/**
 * Base tests class. Most tests should extend this class.
 */
abstract class UnitTestCase extends BaseTestCase
{
    /** @var bool Registry cache. */
    private static $setupBeforeTestSuiteDone = false;

    /** @var DatabaseRestorerInterface Database restorer object */
    private static $dbRestore = null;

    /** @var ModuleLoader Module loader. */
    private static $moduleLoader = null;

    /** @var ShopStateBackup */
    private static $shopStateBackup;

    /** @var VfsStreamWrapper */
    private static $vfsStreamWrapper;

    /** @var array MultiShop tables used in shop */
    private $multiShopTables = array(
        'oxarticles',
        'oxcategories',
        'oxattribute',
        'oxdelivery',
        'oxdeliveryset',
        'oxdiscount',
        'oxmanufacturers',
        'oxselectlist',
        'oxvendor',
        'oxvoucherseries',
        'oxwrapping'
    );

    /** @var array Queries to run on tear down. */
    private $teardownQueries = array();

    /** @var array Tables to be restored after test run. */
    private $tablesForCleanup = array();

    /** @var mixed Backing up for earlier value of database link object */
    private $dbObjectBackup = null;

    /** @var array Buffer variable of queries for feature testing */
    protected $dbQueryBuffer = array();

    /**
     * Running setUpBeforeTestSuite action.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (!self::$setupBeforeTestSuiteDone) {
            $this->setUpBeforeTestSuite();
            self::$setupBeforeTestSuiteDone = true;
        }
    }

    /**
     * Runs necessary things before running tests suite.
     */
    public function setUpBeforeTestSuite()
    {
        $testConfig = $this->getTestConfig();
        if ($testConfig->getModulesToActivate()) {
            $oTestModuleLoader = $this->_getModuleLoader();
            $oTestModuleLoader->loadModules($testConfig->getModulesToActivate());
            $oTestModuleLoader->setModuleInformation();
        }
        oxRegistry::set("oxUtilsDate", new modOxUtilsDate());

        $this->backupDatabase();

        oxRegistry::getUtils()->commitFileCache();

        $oxLang = oxRegistry::getLang();
        $oxLang->resetBaseLanguage();

        $this->getShopStateBackup()->backupRegistry();
        $this->getShopStateBackup()->backupRequestVariables();
    }


    /**
     * Initialize the fixture.
     *
     * @return null
     */
    protected function setUp()
    {
        parent::setUp();
        oxRegistry::getUtils()->cleanStaticCache();
        oxRegistry::set('oxtableviewnamegenerator', null);

        if ($this->getTestConfig()->getModulesToActivate()) {
            $testModuleLoader = $this->_getModuleLoader();
            $testModuleLoader->setModuleInformation();
        }

        $reportingLevel = (int) getenv('TRAVIS_ERROR_LEVEL');
        error_reporting($reportingLevel ? $reportingLevel : ((E_ALL ^ E_NOTICE) | E_STRICT));

        $this->dbObjectBackup = oxDb::getDbObject();
        $this->dbQueryBuffer = array();

        $this->setShopId(null);
        $this->setAdminMode(false);
    }

    /**
     * Starts test.
     *
     * @param TestResult $result
     *
     * @return TestResult
     */
    public function run(TestResult $result = null)
    {
        $this->removeBlacklistedClassesFromCodeCoverage($result);
        $result = parent::run($result);

        oxTestModules::cleanUp();
        return $result;
    }

    /**
     * Executed after test is down.
     * Cleans up database only if test does not have dependencies.
     * If test does have dependencies, any value instead of null should be returned.
     */
    protected function tearDown()
    {
        oxDb::setDbObject($this->dbObjectBackup);

        if ($this->getResult() === null) {
            $this->cleanUpDatabase();

            oxTestsStaticCleaner::clean('oxUtilsObject', '_aInstanceCache');
            oxTestsStaticCleaner::clean('oxArticle', '_aLoadedParents');

            oxTestModules::cleanUp();
            modOxid::globalCleanup();

            $this->getShopStateBackup()->resetRequestVariables();
            $this->getShopStateBackup()->resetRegistry();

            oxUtilsObject::resetClassInstances();
            oxUtilsObject::resetModuleVars();

            parent::tearDown();
        }
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        self::getShopStateBackup()->resetStaticVariables();
        $dbRestore = self::_getDbRestore();
        $dbRestore->restoreDB();
    }

    /**
     * Get parameter from session object.
     *
     * @param string $parameterName parameter name.
     *
     * @return mixed
     */
    public function getSessionParam($parameterName)
    {
        return $this->getSession()->getVariable($parameterName);
    }

    /**
     * Set parameter to session object.
     *
     * @param string $parameterName Parameter name.
     * @param object $value         Any parameter value, default null.
     */
    public function setSessionParam($parameterName, $value = null)
    {
        $this->getSession()->setVariable($parameterName, $value);
    }

    /**
     * Sets parameter to POST.
     *
     * @param string $paramName
     * @param string $paramValue
     */
    public function setRequestParameter($paramName, $paramValue)
    {
        $_POST[$paramName] = $paramValue;
    }

    /**
     * Get parameter from config object.
     *
     * @param string $parameterName parameter name.
     *
     * @return mixed
     */
    public function getRequestParameter($parameterName)
    {
        return $this->getConfig()->getRequestParameter($parameterName);
    }

    /**
     * Get parameter from config object.
     *
     * @param string $parameterName parameter name.
     *
     * @return mixed
     */
    public function getConfigParam($parameterName)
    {
        return $this->getConfig()->getConfigParam($parameterName);
    }

    /**
     * Set parameter to config object.
     *
     * @param string $parameterName Parameter name.
     * @param mixed  $value         Any parameter value, default null.
     */
    public function setConfigParam($parameterName, $value = null)
    {
        $config = oxRegistry::getConfig();
        $config->setConfigParam($parameterName, $value);
    }

    /**
     * Sets OXID shop admin mode.
     *
     * @param bool $adminMode set to admin mode TRUE / FALSE.
     */
    public function setAdminMode($adminMode)
    {
        $this->setSessionParam('blIsAdmin', $adminMode);
        $this->getConfig()->setAdminMode($adminMode);
    }

    /**
     * Get OXID shop ID.
     *
     * @return string
     */
    public function getShopId()
    {
        return $this->getConfig()->getShopId();
    }

    /**
     * Sets OXID shop ID.
     *
     * @param string $shopId set active shop ID.
     */
    public function setShopId($shopId)
    {
        $this->getConfig()->setShopId($shopId);
    }

    /**
     * Set static time value for testing.
     *
     * @param int $time
     */
    public function setTime($time = null)
    {
        oxRegistry::get("oxUtilsDate")->setTime($time);
    }

    /**
     * Get static / real time value for testing.
     *
     * @return int
     */
    public function getTime()
    {
        return oxRegistry::get("oxUtilsDate")->getTime();
    }

    /**
     * Returns session object
     *
     * @return oxSession
     */
    public static function getSession()
    {
        return oxRegistry::getSession();
    }

    /**
     * Returns config object
     *
     * @return oxConfig
     */
    public static function getConfig()
    {
        return oxRegistry::getConfig();
    }

    /**
     * Returns database object
     *
     * @param int $fetchMode
     *
     * @return oxLegacyDb
     */
    public static function getDb($fetchMode = null)
    {
        $oDB = oxDb::getDb();
        if ($fetchMode !== null) {
            $oDB->setFetchMode($fetchMode);
        }

        return $oDB;
    }

    /**
     * Returns basic stub of database link object to use as mock for oxDb class
     *
     * @return oxLegacyDb|MockObject
     */
    public function getDbObjectMock()
    {
        $dbStub = $this->getMockBuilder('oxLegacyDb')->getMock();
        $dbStub->expects($this->any())
            ->method('setFetchMode')
            ->will($this->returnValue(true));

        $dbStub->expects($this->any())
            ->method('quote')
            ->will($this->returnCallback(function ($s) {
                return "'$s'";
            }));

        return $dbStub;
    }

    /**
     * Add query to query buffer
     * @param string $query
     */
    public function fillDbQueryBuffer($query)
    {
        $this->dbQueryBuffer[] = $query;
    }

    /**
     * Returns cache backend
     *
     * @return \OxidEsales\EshopEnterprise\Core\Cache\Generic\Cache
     */
    public function getCacheBackend()
    {
        return oxRegistry::get('oxCacheBackend');
    }

    /**
     * Sets language
     *
     * @param int $languageId
     */
    public function setLanguage($languageId)
    {
        $oxLang = oxRegistry::getLang();
        $oxLang->setBaseLanguage($languageId);
        $oxLang->setTplLanguage($languageId);
    }

    /**
     * Returns currently set language
     *
     * @return string
     */
    public function getLanguage()
    {
        return oxRegistry::getLang()->getBaseLanguage();
    }

    /**
     * Sets template language
     *
     * @param int $languageId
     */
    public function setTplLanguage($languageId)
    {
        oxRegistry::getLang()->setTplLanguage($languageId);
    }

    /**
     * Returns template language
     *
     * @return string
     */
    public function getTplLanguage()
    {
        return oxRegistry::getLang()->getTplLanguage();
    }

    /**
     * Get teardown sqls containing delete information
     *
     * @return array
     */
    public function getTeardownSqls()
    {
        return (array)$this->teardownQueries;
    }

    /**
     * Add single teardown sql
     *
     * @param string $query teardown query
     */
    public function addTeardownSql($query)
    {
        if (!in_array($query, $this->teardownQueries)) {
            $this->teardownQueries[] = $query;
        }
    }

    /**
     * Set multishop tables array, in case some custom tables need to be used
     *
     * @param array $multiShopTables
     */
    public function setMultiShopTables($multiShopTables)
    {
        $this->multiShopTables = $multiShopTables;
    }

    /**
     * Get multishop tables array
     *
     * @return array
     */
    public function getMultiShopTables()
    {
        return $this->multiShopTables;
    }

    /**
     * Executes SQL and adds table to clean up after test.
     * For EE version elements are added to map table for specified shops.
     *
     * @param string    $sql     Sql to be executed.
     * @param string    $table   Table name.
     * @param array|int $shopIds List of shop IDs.
     * @param null      $mapId   Map ID.
     */
    public function addToDatabase($sql, $table, $shopIds = 1, $mapId = null)
    {
        oxDb::getDb()->execute($sql);

        if ($this->getTestConfig()->getShopEdition() == 'EE' && in_array($table, $this->getMultiShopTables())) {
            $mapId = !is_null($mapId) ? $mapId : oxDb::getDb()->Insert_ID();
            $shopIds = (array)$shopIds;

            foreach ($shopIds as $iShopId) {
                $sql = "REPLACE INTO `{$table}2shop` SET `oxmapobjectid` = ?, `oxshopid` = ?";
                oxDb::getDb()->execute($sql, array($mapId, $iShopId));
            }
        }
    }

    /**
     * Returns a mock object for the specified class.
     *
     * @param string     $originalClassName       Name of the class to mock.
     * @param array|null $methods                 When provided, only methods whose names are in the array
     *                                            are replaced with a configurable test double. The behavior
     *                                            of the other methods is not changed.
     *                                            Providing null means that no methods will be replaced.
     * @param array      $arguments               Parameters to pass to the original class' constructor.
     * @param string     $mockClassName           Class name for the generated test double class.
     * @param bool       $callOriginalConstructor Can be used to disable the call to the original class' constructor.
     * @param bool       $callOriginalClone       Can be used to disable the call to the original class' clone constructor.
     * @param bool       $callAutoload            Can be used to disable __autoload() during the generation of the test double class.
     * @param bool       $cloneArguments
     * @param bool       $callOriginalMethods
     *
     * @return MockObject
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 3.0.0
     */
    public function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false)
    {
        // TODO: remove this condition when namespaces will be implemented fully.
        if (strpos($originalClassName, 'OxidEsales\\') === false) {
            $originalClassName = strtolower($originalClassName);
        }
        $originalClassName = oxRegistry::get('oxUtilsObject')->getClassName($originalClassName);
        return parent::getMock($originalClassName, $methods, $arguments, $mockClassName, $callOriginalConstructor, $callOriginalClone, $callAutoload, $cloneArguments);
    }

    /**
     * Calls all the queries stored in $_aTeardownSqls
     * Cleans all the tables that were set
     */
    public function cleanUpDatabase()
    {
        if ($tearDownQueries = $this->getTeardownSqls()) {
            foreach ($tearDownQueries as $query) {
                oxDb::getDb()->execute($query);
            }
        }

        if ($tablesForCleanup = $this->getTablesForCleanup()) {
            $dbRestore = $this->_getDbRestore();
            foreach ($tablesForCleanup as $sTable) {
                $dbRestore->restoreTable($sTable);
            }
        }
    }

    /**
     * Gets dirty tables for cleaning
     *
     * @param array $tablesForCleanup
     */
    public function setTablesForCleanup($tablesForCleanup)
    {
        $this->tablesForCleanup = $tablesForCleanup;
    }

    /**
     * Sets dirty tables for cleaning
     *
     * @return array
     */
    public function getTablesForCleanup()
    {
        return (array)$this->tablesForCleanup;
    }

    /**
     * Adds table to be cleaned on teardown
     *
     * @param string $table
     */
    public function addTableForCleanup($table)
    {
        if (!in_array($table, $this->tablesForCleanup)) {
            $this->tablesForCleanup[] = $table;
            if ($this->getTestConfig()->getShopEdition() == 'EE' && in_array($table, $this->getMultiShopTables())) {
                $this->tablesForCleanup[] = "{$table}2shop";
            }
        }
    }

    /**
     * Cleans up table
     *
     * @param string $table      Table name
     * @param string $columnName Column name
     */
    public function cleanUpTable($table, $columnName = null)
    {
        $sCol = (!empty($columnName)) ? $columnName : 'oxid';

        if ($this->getTestConfig()->getShopEdition() == 'EE' && in_array($table, $this->getMultiShopTables())) {
            // deletes all records from shop relations table
            $query = "delete from `{$table}2shop`
                where oxmapobjectid in (select oxmapid from `$table` where `$sCol` like '\_%')";
            $this->getDb()->execute($query);
        }

        //deletes allrecords where oxid or specified column name values starts with underscore(_)
        $sQ = "delete from `$table` where `$sCol` like '\_%' ";
        $this->getDb()->execute($sQ);
    }

    /**
     * Create proxy class of given class. Proxy allows to test of protected class methods and to access non public members
     *
     * @param string $superClassName
     *
     * @return string
     */
    public function getProxyClassName($superClassName)
    {
        $superClassName = oxRegistry::get('oxUtilsObject')->getClassName(strtolower($superClassName));
        $escapedSuperClassName = str_replace('\\', '_', $superClassName);
        $proxyClassName = "{$escapedSuperClassName}Proxy";

        if (!class_exists($proxyClassName, false)) {
            $class = "
                class $proxyClassName extends $superClassName
                {
                    public function __call(\$function, \$args)
                    {
                        \$function = str_replace('UNIT', '_', \$function);
                        if(method_exists(\$this,\$function)){
                            return call_user_func_array(array(&\$this, \$function),  \$args);
                        }else{
                            throw new Exception('Method '.\$function.' in class '.get_class(\$this).' does not exist');
                        }
                    }
                    public function setNonPublicVar(\$name, \$value)
                    {
                        \$this->\$name = \$value;
                    }

                    public function getNonPublicVar(\$name)
                    {
                        return \$this->\$name;
                    }
                }";
            eval($class);
        }

        return $proxyClassName;
    }

    /**
     * Create proxy of given class. Proxy allows to test of protected class methods and to access non public members
     *
     * @param string $superClassName
     * @param array  $params
     *
     * @deprecated
     *
     * @return object
     */
    public function getProxyClass($superClassName, array $params = null)
    {
        $proxyClassName = $this->getProxyClassName($superClassName);
        if (!empty($params)) {
            // Create an instance using Reflection, because constructor has parameters
            $class = new ReflectionClass($proxyClassName);
            $instance = $class->newInstanceArgs($params);
        } else {
            $instance = new $proxyClassName();
        }

        return $instance;
    }

    /**
     * Cleans tmp dir.
     */
    public function cleanTmpDir()
    {
        $directory = oxRegistry::getConfig()->getConfigParam('sCompileDir');
        system("rm -f $directory/*.txt");
        system("rm -f $directory/ox*.tmp");
        system("rm -f $directory/*.tpl.php");
    }

    /**
     * Creates virtual file with given content.
     *
     * @param string $fileName
     * @param string $fileContent
     *
     * @usage Create file from file name and file content to temp directory.
     *
     * @return string path to file
     */
    public function createFile($fileName, $fileContent)
    {
        return $this->getVfsStreamWrapper()->createFile($fileName, $fileContent);
    }

    /**
     * @return VfsStreamWrapper
     */
    public function getVfsStreamWrapper()
    {
        if (is_null(self::$vfsStreamWrapper)) {
            self::$vfsStreamWrapper = new VfsStreamWrapper();
        }

        return self::$vfsStreamWrapper;
    }

    /**
     * Creates stub object from given class
     *
     * @param string $className   Class name
     * @param array  $methods     Assoc array with method => value
     * @param array  $testMethods Array with test methods for mocking
     *
     * @return mixed
     */
    public function createStub($className, $methods, $testMethods = array())
    {
        $mockedMethods = array_unique(array_merge(array_keys($methods), $testMethods));

        $object = $this->getMock($className, $mockedMethods, array(), '', false);

        foreach ($methods as $method => $value) {
            if (!in_array($method, $testMethods)) {
                $object->expects($this->any())
                    ->method($method)
                    ->will($this->returnValue($value));
            }
        }

        return $object;
    }

    /**
     * eval Func for invoke mock
     *
     * @param mixed $value
     *
     * @access protected
     *
     * @return MockStubFunc
     */
    public function evalFunction($value)
    {
        return new MockStubFunc($value);
    }

    /**
     * @param string $extension
     * @param string $class
     */
    public function addClassExtension($extension, $class)
    {
        $utilsObject = new oxUtilsObject();
        $extensions = $utilsObject->getModuleVar("aModules");

        oxRegistry::set($class, null);

        if ($extensions[strtolower($class)]) {
            $extension = $extensions[strtolower($class)] . '&' . $extension;
        }
        $extensions[strtolower($class)] = $extension;
        $utilsObject->setModuleVar("aModules", $extensions);
    }

    /**
     * @param string $extension
     * @param string $class
     */
    public function removeClassExtension($extension, $class = '')
    {
        oxRegistry::set($class, null);

        $utilsObject = new oxUtilsObject();
        $extensions = $utilsObject->getModuleVar("aModules");

        if (!$extensions) {
            $extensions = array();
        }

        if ($class) {
            unset($extensions[$class]);
        } else {
            while (($key = array_search($extension, $extensions)) !== false) {
                unset($extensions[$key]);
            }
        }
        $utilsObject->setModuleVar("aModules", $extensions);
    }

    /**
     * Returns shop state backup class.
     *
     * @return ShopStateBackup
     */
    protected static function getShopStateBackup()
    {
        if (is_null(self::$shopStateBackup)) {
            self::$shopStateBackup = new ShopStateBackup();
        }
        return self::$shopStateBackup;
    }

    /**
     * Returns database restorer object.
     *
     * @return DatabaseRestorerInterface
     */
    protected static function _getDbRestore()
    {
        if (is_null(self::$dbRestore)) {
            $factory = new DatabaseRestorerFactory();
            self::$dbRestore = $factory->createRestorer(self::getTestConfig()->getDatabaseRestorationClass());
        }

        return self::$dbRestore;
    }

    /**
     * Returns database restorer object.
     *
     * @return ModuleLoader
     */
    protected static function _getModuleLoader()
    {
        if (is_null(self::$moduleLoader)) {
            self::$moduleLoader = new ModuleLoader();
        }

        return self::$moduleLoader;
    }

    /**
     * Converts a string to UTF format.
     *
     * @param string $sVal
     *
     * @return string
     */
    protected function _2Utf($sVal)
    {
        return iconv("ISO-8859-1", "UTF-8", $sVal);
    }

    /**
     * Creates stub object from given class
     *
     * @param string $sClass       Class name
     * @param array  $aMethods     Assoc array with method => value
     * @param array  $aTestMethods Array with test methods for mocking
     *
     * @deprecated use oxUnitTestCase::createStub() instead.
     *
     * @return mixed
     */
    protected function _createStub($sClass, $aMethods, $aTestMethods = array())
    {
        return $this->createStub($sClass, $aMethods, $aTestMethods);
    }

    /**
     * Backs up database for later restorations.
     */
    protected function backupDatabase()
    {
        $oDbRestore = self::_getDbRestore();
        $oDbRestore->dumpDB();
    }

    /**
     * Removes blacklisted classes from code coverage report, as this is only fixed in PHPUnit 4.0.
     *
     * @param TestResult $result
     */
    private function removeBlacklistedClassesFromCodeCoverage($result)
    {
        if ($result->getCollectCodeCoverageInformation()) {
            $oCoverage = $result->getCodeCoverage();
            $oFilter = $oCoverage->filter();
            $aBlacklist = $oFilter->getBlacklist();
            foreach ($aBlacklist as $sFile) {
                $oFilter->removeFileFromWhitelist($sFile);
            }
        }
    }
}
