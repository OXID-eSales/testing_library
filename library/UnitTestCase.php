<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use modOXID;
use modOxUtilsDate;

use oxDatabaseHelper;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Module\ModuleVariablesLocator;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\EshopCommunity\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\EshopCommunity\Core\Database;
use OxidEsales\TestingLibrary\Helper\ProjectConfigurationHelper;
use OxidEsales\TestingLibrary\Helper\SessionHelper;
use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorerFactory;
use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorerInterface;

use OxidEsales\TestingLibrary\Services\Library\ProjectConfigurationHandler;
use oxTestModules;
use oxTestsStaticCleaner;
use PHPUnit\Framework\TestResult;
use ReflectionClass;
use Exception;

require_once TEST_LIBRARY_HELPERS_PATH . 'oxDatabaseHelper.php';
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
    private $vfsStreamWrapper;

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
            $oTestModuleLoader->activateModules($testConfig->getModulesToActivate());

            // Modules might add new translations, and language object has a static cache which must be flushed
            \OxidEsales\Eshop\Core\Registry::set('oxLang', null);
        }
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsDate::class, new modOxUtilsDate());

        if ($testConfig->shouldRestoreAfterUnitTests()) {
            $this->backupDatabase();
            self::getProjectConfigurationHandler()->backup();
        }

        \OxidEsales\Eshop\Core\Registry::getUtils()->commitFileCache();

        $oxLang = \OxidEsales\Eshop\Core\Registry::getLang();
        $oxLang->resetBaseLanguage();

        $this->getShopStateBackup()->backupRegistry();
        $this->getShopStateBackup()->backupRequestVariables();
    }


    /**
     * Initialize the fixture.
     *
     */
    protected function setUp()
    {
        parent::setUp();
        \OxidEsales\Eshop\Core\Registry::getUtils()->cleanStaticCache();
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\TableViewNameGenerator::class, null);

        $this->dbQueryBuffer = array();

        $this->setShopId(null);
        $this->setAdminMode(false);
        UtilsObject::resetModuleVars();
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
        $originalErrorReportingLevel = error_reporting();
        error_reporting($originalErrorReportingLevel & ~E_NOTICE);
        $result = parent::run($result);
        error_reporting($originalErrorReportingLevel);

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
        /**
         * This try catch block fixes some issues with tests that do interfere with the transaction nesting and lead to
         * transactions marked as rollback only, even when all the shop code has been executed.
         */
        try {
            while (\OxidEsales\Eshop\Core\DatabaseProvider::getDb()->isTransactionActive() && \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->isRollbackOnly() ) {
                \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->rollbackTransaction();
            }
        /**
         * Catch exceptions, which happen when calling isRollbackOnly() on a connection which is not in a transaction.
         */
        } catch (Exception $exception) {
            // Do nothing
        }

        if ($this->getResult() === null) {

            $this->ensureNoPhpSession();

            $this->cleanUpDatabase();

            oxTestsStaticCleaner::clean('oxUtilsObject', '_aInstanceCache');
            oxTestsStaticCleaner::clean('oxArticle', '_aLoadedParents');

            oxTestModules::cleanUp();
            modOxid::globalCleanup();

            UtilsObject::resetClassInstances();

            $this->getShopStateBackup()->resetRequestVariables();
            $this->getShopStateBackup()->resetRegistry();

            ModuleVariablesLocator::resetModuleVariables();
            SessionHelper::resetStaticPropertiesToDefaults();

            parent::tearDown();
        }
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        self::getShopStateBackup()->resetStaticVariables();
        $testConfig = self::getStaticTestConfig();
        if ($testConfig->shouldRestoreAfterUnitTests()) {
            $dbRestore = self::_getDbRestore();
            $dbRestore->restoreDB();
            self::getProjectConfigurationHandler()->restore();
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->closeConnection();
        }
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
        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
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
        \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsDate::class)->setTime($time);
    }

    /**
     * Get static / real time value for testing.
     *
     * @return int
     */
    public function getTime()
    {
        return \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsDate::class)->getTime();
    }

    /**
     * Returns session object
     *
     * @return \OxidEsales\Eshop\Core\Session
     */
    public function getSession()
    {
        return \OxidEsales\Eshop\Core\Registry::getSession();
    }

    /**
     * Returns config object
     *
     * @return \OxidEsales\Eshop\Core\Config
     */
    public function getConfig()
    {
        return \OxidEsales\Eshop\Core\Registry::getConfig();
    }

    /**
     * Returns database object
     *
     * @param int $fetchMode
     *
     * @return DatabaseInterface
     */
    public static function getDb($fetchMode = null)
    {
        $oDB = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        if ($fetchMode !== null) {
            $oDB->setFetchMode($fetchMode);
        }

        return $oDB;
    }

    /**
     * Returns basic stub of database link object to use as mock for oxDb class
     *
     * @return DatabaseInterface|MockObject
     */
    public function getDbObjectMock()
    {
        $dbStub = $this->getMockBuilder('OxidEsales\EshopCommunity\Core\Database\Adapter\Doctrine\Database')->getMock();
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
        return \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\Cache\Generic\Cache::class);
    }

    /**
     * Sets language
     *
     * @param int $languageId
     */
    public function setLanguage($languageId)
    {
        $oxLang = \OxidEsales\Eshop\Core\Registry::getLang();
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
        return \OxidEsales\Eshop\Core\Registry::getLang()->getBaseLanguage();
    }

    /**
     * Sets template language
     *
     * @param int $languageId
     */
    public function setTplLanguage($languageId)
    {
        \OxidEsales\Eshop\Core\Registry::getLang()->setTplLanguage($languageId);
    }

    /**
     * Returns template language
     *
     * @return string
     */
    public function getTplLanguage()
    {
        return \OxidEsales\Eshop\Core\Registry::getLang()->getTplLanguage();
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
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sql);

        if ($this->getTestConfig()->getShopEdition() == 'EE' && in_array($table, $this->getMultiShopTables())) {
            $mapId = !is_null($mapId) ? $mapId : \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getLastInsertId();
            $shopIds = (array)$shopIds;

            foreach ($shopIds as $iShopId) {
                $sql = "REPLACE INTO `{$table}2shop` SET `oxmapobjectid` = ?, `oxshopid` = ?";
                \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sql, array($mapId, $iShopId));
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
     * @param null       $proxyTarget
     *
     * @return MockObject
     *
     * @throws PHPUnit\Framework\Exception
     *
     * @since  Method available since Release 3.0.0
     *
     * @deprecated This is just for compatibility with PHPUnit 4 - use getMockBuilder() to obtain a mock
     */
    public function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false, $proxyTarget = null)
    {
        $mockBuilder = $this->getMockBuilder($originalClassName);
        $mockBuilder->setMethods($methods);
        $mockBuilder->setConstructorArgs($arguments);
        $mockBuilder->setMockClassName($mockClassName);
        if ($callOriginalConstructor) {
            $mockBuilder->enableOriginalConstructor();
        }
        else {
            $mockBuilder->disableOriginalConstructor();
        }
        if ($callOriginalClone) {
            $mockBuilder->enableOriginalClone();
        }
        else {
            $mockBuilder->disableOriginalClone();
        }
        if ($callAutoload) {
            $mockBuilder->enableAutoload();
        }
        else {
            $mockBuilder->disableAutoload();
        }
        if ($cloneArguments) {
            $mockBuilder->enableArgumentCloning();
        }
        else {
            $mockBuilder->disableArgumentCloning();
        }
        if (! is_null($proxyTarget)) {
            $mockBuilder->setProxyTarget($proxyTarget);
        }
        return $mockBuilder->getMock();
    }

    /**
     * Creates a mock builder for the edition file of the class name given
     *
     * @param $className
     *
     * @return \PHPUnit\Framework\MockObject\MockBuilder
     */
    public function getMockBuilder($className): \PHPUnit\Framework\MockObject\MockBuilder
    {
        // TODO: remove this condition when namespaces will be implemented fully.
        if (strpos($className, '\\') === false) {
            $className = strtolower($className);
        }
        $editionClassName = \OxidEsales\Eshop\Core\Registry::get(UtilsObject::class)->getClassName($className);

        return parent::getMockBuilder($editionClassName);

    }
    /**
     * Calls all the queries stored in $_aTeardownSqls
     * Cleans all the tables that were set
     */
    public function cleanUpDatabase()
    {
        if ($tearDownQueries = $this->getTeardownSqls()) {
            foreach ($tearDownQueries as $query) {
                \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
            }
        }

        if ($this->getTestConfig()->shouldRestoreAfterUnitTests() && ($tablesForCleanup = $this->getTablesForCleanup())) {
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
        $superClassName = \OxidEsales\Eshop\Core\Registry::get(UtilsObject::class)->getClassName(strtolower($superClassName));
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
        $directory = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sCompileDir');
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
    public function createFile($fileName, $fileContent = '')
    {
        return $this->getVfsStreamWrapper()->createFile($fileName, $fileContent);
    }

    /**
     * @return VfsStreamWrapper
     */
    public function getVfsStreamWrapper()
    {
        if ($this->vfsStreamWrapper === null) {
            $this->vfsStreamWrapper = new VfsStreamWrapper();
        }

        return $this->vfsStreamWrapper;
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
        $utilsObject = new UtilsObject();
        $extensions = $utilsObject->getModuleVar("aModules");

        \OxidEsales\Eshop\Core\Registry::set($class, null);

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
        \OxidEsales\Eshop\Core\Registry::set($class, null);

        $utilsObject = new UtilsObject();
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
     * Mark test run as skipped if tests are run with Subshop flag enabled.
     * Allows to write Subshop only test case.
     */
    public function markTestSkippedIfSubShop()
    {
        if ($this->getTestConfig()->isSubShop()) {
            $this->markTestSkipped('Test is NOT for subshops!');
        }
    }

    /**
     * Mark test run as skipped if tests are run with NO Subshop flag enabled.
     * Allows to write Subshop only test case.
     */
    public function markTestSkippedIfNoSubShop()
    {
        if (!$this->getTestConfig()->isSubShop()) {
            $this->markTestSkipped('Test is ONLY for subshops!');
        }
    }

    /**
     * Set a given protected property of a given class instance to a given value.
     *
     * Note: Please use this methods only for static 'mocking' or with other hard reasons!
     *       For the most possible non static usages there exist other solutions.
     *
     * @param object $classInstance Instance of the class of which the property will be set
     * @param string $property      Name of the property to be set
     * @param mixed  $value         Value to which the property will be set
     */
    protected function setProtectedClassProperty($classInstance, $property, $value)
    {
        $className = get_class($classInstance);

        $reflectionClass = new ReflectionClass($className);

        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($classInstance, $value);
    }

    /**
     * Get a given protected property of a given class instance.
     *
     * Note: Please use this methods only for static 'mocking' or with other hard reasons!
     *       For the most possible non static usages there exist other solutions.

     * @param object $classInstance Instance of the class of which the property will be set
     * @param string $property      Name of the property to be retrieved
     *
     * @return mixed
     */
    protected function getProtectedClassProperty($classInstance, $property)
    {
        $className = get_class($classInstance);

        $reflectionClass = new ReflectionClass($className);

        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($classInstance);
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
            self::$dbRestore = $factory->createRestorer(self::getStaticTestConfig()->getDatabaseRestorationClass());
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
     * OnlineCaller rethrows exception in method _castExceptionAndWriteToLog
     * this way we mock it from writing to log.
     *
     * @param string $exceptionClassName The name of the exception we want to stub, to not log its output.
     * @param string $saveUnderClassName The name under which we save the stubbed exception in the testing library.
     *
     * @return MockObject The mocked exception.
     *
     * @deprecated since v.3.4.0 (2018-01-11); The method was using deprecated oxTestModules and was made obsolet now
     */
    protected function stubExceptionToNotWriteToLog($exceptionClassName = 'oxException', $saveUnderClassName = 'oxException')
    {
        $exception = $this->getMock($exceptionClassName, ['debugOut']);
        $exception->expects($this->any())->method('debugOut');

        oxTestModules::addModuleObject($saveUnderClassName, $exception);

        return $exception;
    }

    protected function assertViewExists($tableName)
    {
        $generator = oxNew(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);
        $tableNameView = $generator->getViewName($tableName, 0);

        $this->assertTrue($this->existsView($tableName), 'Expected view "' . $tableNameView . '" does not exist!');
    }

    protected function assertViewNotExists($tableName)
    {
        $generator = oxNew(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);
        $tableNameView = $generator->getViewName($tableName, 0);

        $this->assertFalse($this->existsView($tableName), 'Expected that view "' . $tableNameView . '" does not exist, but it does!');
    }

    protected function existsView($tableName)
    {
        $databaseHelper = new oxDatabaseHelper(DatabaseProvider::getDb());

        return $databaseHelper->existsView($tableName);
    }

    /**
     * Test helper to destroy PHP session.
     * Some test might have started the session, so best
     * ensure PHP session is destroyed on test tear down.
     */
    protected function ensureNoPhpSession()
    {
        if ((PHP_SESSION_ACTIVE == session_status()) && session_id()) {
            session_destroy();
        }
    }

    /**
     * @return ProjectConfigurationHandler
     */
    private static function getProjectConfigurationHandler(): ProjectConfigurationHandler
    {
        return new ProjectConfigurationHandler(new ProjectConfigurationHelper());
    }
}
