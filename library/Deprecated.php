<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Backward compatibility, do not use it for new tests.
 *
 * @deprecated use OxidEsales\TestingLibrary\UnitTestCase instead
 */
class OxidTestCase extends OxidEsales\TestingLibrary\UnitTestCase
{
}

/**
 * Backward compatibility, do not use it for new tests.
 *
 * @deprecated use OxidEsales\TestingLibrary\UnitTestCase instead
 */
class oxUnitTestCase extends OxidEsales\TestingLibrary\UnitTestCase
{

}

/**
 * Backward compatibility, do not use it for new tests.
 *
 * @deprecated use OxidEsales\TestingLibrary\AcceptanceTestCase instead
 */
class oxTestCase extends OxidEsales\TestingLibrary\AcceptanceTestCase
{
}

/**
 * Backward compatibility, do not use it for new tests.
 *
 * @deprecated use OxidEsales\TestingLibrary\AcceptanceTestCase instead
 */
class oxAcceptanceTestCase extends OxidEsales\TestingLibrary\AcceptanceTestCase
{
}

use \OxidEsales\Eshop\Core\UtilsObject;

/**
 * adds new module to specified class
 * Usable if you want to check how many calls of class AA method BB
 *    done while testing class XX.
 * Or can be used to disable some AA method like BB (e.g. die),
 *    which gets called while testing XX.
 * Since there are no modules in testing data, this function does not
 *    check module parent module
 *
 * e.g.
 *  - we need to disable \OxidEsales\Eshop\Core\Utils::showMessageAndDie
 *     class modUtils extends oxutils {
 *        function showMessageAndDie (){}
 *     };
 *  - and then in your test function
 *     oxAddClassModule('modUtils', 'oxutils');
 *  - and after doing some ...
 *     oxRemClassModule('modUtils');
 *
 * @param $sModuleClass
 * @param $sClass
 * @param bool $prependStrategy
 *
 * @deprecated since v4.0.0; Use native PHPUnit functionality like mocks for achieving similar result.
 */
function oxAddClassModule($sModuleClass, $sClass, $prependStrategy = false)
{
    $oFactory = new \OxidEsales\Eshop\Core\UtilsObject();
    $aModules = $oFactory->getModuleVar("aModules");

    //unset _possible_ registry instance
    \OxidEsales\Eshop\Core\Registry::set($sClass, null);

    if ($aModules[$sClass]) {
        $sModuleClass = $prependStrategy
            ? $sModuleClass . '&' . $aModules[$sClass]
            : $aModules[$sClass] . '&' . $sModuleClass;
    }
    $aModules[strtolower($sClass)] = $sModuleClass;

    $oFactory->setModuleVar("aModules", $aModules);
}

/**
 * @param $sModuleClass
 * @param string $sClass
 *
 * @deprecated since v4.0.0; Use native PHPUnit functionality like mocks for achieving similar result.
 */
function oxRemClassModule($sModuleClass, $sClass = '')
{
    \OxidEsales\Eshop\Core\Registry::set($sClass, null);

    $oFactory = new \OxidEsales\Eshop\Core\UtilsObject();
    $aModules = $oFactory->getModuleVar("aModules");

    if (!$aModules) {
        $aModules = array();
    }

    if ($sClass) {
        unset($aModules[$sClass]);
    } else {
        while (($sKey = array_search($sModuleClass, $aModules)) !== false) {
            unset($aModules[$sKey]);
        }
    }
    $oFactory->setModuleVar("aModules", $aModules);
}

/**
 * Class oxTestModules
 *
 * @deprecated
 */
class oxTestModules
{

    private static $_addedmods = array();

    private static function _getNextName($sOrig)
    {
        $base = $sOrig . '__oxTestModule_';
        $cnt = 0;
        while (class_exists($base . $cnt, false)) {
            ++$cnt;
        }

        return $base . $cnt;
    }

    /**
     * addVar adds module and creates function in it
     *
     * @param string $class   target class
     * @param string $varName target variabe
     * @param string $access  public | private | public static, whatever
     * @param string $default default value
     *
     * @static
     * @access public
     * @return void
     */
    public static function addVariable($class, $varName, $access = 'public', $default = 'null')
    {
        $class = strtolower($class);
        $name = self::_getNextName($class);
        if (is_array(self::$_addedmods[$class]) && $cnt = count(self::$_addedmods[$class])) {
            $last = self::$_addedmods[$class][$cnt - 1];
        } else {
            $last = \OxidEsales\Eshop\Core\Registry::getUtilsObject()->getClassName(strtolower($class));
        }
        eval ("class $name extends $last { $access \$$varName = $default;}");
        oxAddClassModule($name, $class);
        self::$_addedmods[$class][] = $name;
    }

    /**
     * addFunction adds module and creates function in it
     *
     * @param mixed $class   target class
     * @param mixed $fncName target function
     * @param mixed $func    function - if it is '{...}' then it is function code ($aA is arguments array), else it is taken as param to call_user_func_array
     *
     * @static
     * @access public
     * @return string
     */
    public static function addFunction($class, $fncName, $func)
    {
        $originalErrorReportingLevel = error_reporting();
        error_reporting($originalErrorReportingLevel & ~E_NOTICE);

        try {
            $class = strtolower($class);
            $name = self::_getNextName($class);

            if (is_array(self::$_addedmods[$class]) && $cnt = count(self::$_addedmods[$class])) {
                $last = self::$_addedmods[$class][$cnt - 1];
            } else {
                $last = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsObject::class)->getClassName(strtolower($class));
            }
            if (preg_match('/^{.*}$/ms', $func)) {
                $sCode = "\$aA = func_get_args(); " . trim($func, '{}');
            } else {
                if (preg_match('/^[a-z0-9_-]*$/i', trim($func))) {
                    $func = "'$func'";
                }
                $sCode = " \$arg = func_get_args(); return call_user_func_array($func, \$arg);";
            }

            $aFncParams = array();
            if (strpos($fncName, '(') !== false) {
                $aMatches = null;
                preg_match("@(.*?)\((.*?)\)$@", trim($fncName), $aMatches);

                $fncName = trim($aMatches[1]);
                if (trim($aMatches[2])) {
                    $aFncParams = explode(',', $aMatches[2]);
                } else {
                    $aFncParams = array();
                }
            }

            if (method_exists($last, $fncName)) {
                $oReflection = new ReflectionClass($last);
                $aMethodParams = $oReflection->getMethod($fncName)->getParameters();

                $fncName .= '(';
                $blFirst = true;
                foreach ($aMethodParams AS $iKey => $oParam) {

                    if (!$blFirst) {
                        $fncName .= ', ';
                    } else {
                        $blFirst = false;
                    }

                    if (isset($aFncParams[$iKey])) {
                        $fncName .= $aFncParams[$iKey];

                        if (strpos($aFncParams[$iKey], '=') === false && $oParam->isDefaultValueAvailable()) {
                            $fncName .= ' = ' . var_export($oParam->getDefaultValue(), true);
                        }

                        continue;
                    }

                    if ($oParam->getClass()) {
                        $fncName .= $oParam->getClass()->getName() . ' ';
                    }
                    $fncName .= '$' . $oParam->getName();
                    if ($oParam->isDefaultValueAvailable()) {
                        $fncName .= ' = ' . var_export($oParam->getDefaultValue(), true);
                    }
                }
                $fncName .= ')';
            } else {
                if (empty($aFncParams)) {
                    $fncName .= '($p1=null, $p2=null, $p3=null, $p4=null, $p5=null, $p6=null, $p7=null, $p8=null, $p9=null, $p10=null)';
                } else {
                    $fncName .= '(' . implode(', ', $aFncParams) . ')';
                }
            }

            eval ("class $name extends $last { function $fncName { $sCode }}");
            oxAddClassModule($name, $class);

            self::$_addedmods[$class][] = $name;

            return $name;
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            error_reporting($originalErrorReportingLevel);
        }
    }

    /**
     * internal class->object map
     *
     * @var array
     */
    protected static $_aModuleMap = array();
    protected static $_oOrigOxUtilsObj = null;

    /**
     * add object to be returned from oxNew for a class
     *
     * @param string $sClassName
     * @param object $oObject
     *
     */
    public static function addModuleObject($sClassName, $oObject)
    {
        \OxidEsales\Eshop\Core\Registry::set($sClassName, null);
        UtilsObject::setClassInstance($sClassName, $oObject);
    }

    /**
     * publicize method = creates a wrapper for it named p_XXX instead of _XXX
     *
     * @param mixed $class
     * @param mixed $fnc
     *
     * @static
     * @access public
     * @return string
     */
    public static function publicize($class, $fnc)
    {
        return self::addFunction($class, preg_replace('/^_/', 'p_', $fnc), "array(\$this, '$fnc')");
    }

    /**
     * clean Ups loaded modules
     *
     * @static
     * @access public
     * @return void
     */
    public static function cleanUp()
    {
        self::$_aModuleMap = array();
        self::$_oOrigOxUtilsObj = null;
        foreach (self::$_addedmods as $class => $arr) {
            oxRemClassModule('allmods', $class);
        }
        self::$_addedmods = array();
    }

    /**
     * cleans every module attached
     */
    public static function cleanAllModules()
    {
        \OxidEsales\Eshop\Core\Registry::getConfig()->setConfigParam('aModules', array());
    }
}

/**
 * creates static cleaner subclasses and nulls parent class protected static property
 *
 * @deprecated since v4.0.0
 */
class oxTestsStaticCleaner
{
    /**
     * get class name
     *
     * @param string $sClass
     *
     * @return string
     */
    protected static function _getChildClass($sClass)
    {
        return __CLASS__ . '_' . $sClass;
    }

    /**
     * create cleaner and execute it
     *
     * @param string $sClass
     * @param string $sProperty
     *
     */
    public static function clean($sClass, $sProperty)
    {
        $sNewCl = self::_getChildClass($sClass);
        if (!class_exists($sNewCl)) {
            eval ("class $sNewCl extends $sClass { public function __construct(){} public function __cleaner(\$sProperty) { $sClass::\${\$sProperty}=null; }}");
        }
        $o = new $sNewCl();
        $o->__cleaner($sProperty);
    }
}

/**
 * adds or replaces oxConfig functionality.
 * [because you just can not use module emulation functionality with oxConfig]
 * usage:
 *     to initialize just create a new instance of the class.
 *  to replace OR attach some oxConfig function use addClassFunction method:
 *  to end with mod, use remClassFunction or just cleanup.
 *
 *   e.g.
 *
 *   Executor
 *     $a = $this->getConfig();
 *     $a->addClassFunction('getDB', array($this, 'getMyDb'));
 *
 *   OR
 *
 *   Observer
 *     $a = $this->getConfig();
 *     $a->addClassFunction('getDB', array($this, 'countGetDbCalls'), false);
 *
 *
 * this class is also usable to override some oxConfig variable by using
 *     addClassVar function (if second parameter is null [default], the initial value of
 *  overriden variable is the orginal oxConfig's value)
 *
 * Also, since all tests are INDEPENDANT, no real changes are made to the real instance.
 * NOTE: after cleanup, all oxConfig variable changes while modConfig was active are LOST.
 *
 * @deprecated since v4.0.0
 */
abstract class modOXID
{
    protected $_takeover = array();
    protected $_checkover = array();
    protected $_vars = array();
    protected $_params = array();
    protected $_oRealInstance = null;

    public function getRealInstance()
    {
        return $this->_oRealInstance;
    }

    public function modAttach($oObj = null)
    {
        $this->cleanup();
    }

    public function cleanup()
    {
        $this->_takeover = array();
        $this->_checkover = array();
        $this->_vars = array();
        $this->_params = array();
    }

    public static function globalCleanup()
    {
        // cleaning up core info
        $oConfig = new \OxidEsales\Eshop\Core\Base();
        $oConfig->setConfig(null);
        $oConfig->setSession(null);
        $oConfig->setUser(null);
        $oConfig->setAdminMode(null);

        if (method_exists($oConfig, 'setRights')) {
            $oConfig->setRights(null);
        }
        oxTestModules::cleanAllModules();
    }

    public function addClassFunction($sFunction, $callback, $blTakeOver = true)
    {
        $sFunction = strtolower($sFunction);
        if ($blTakeOver) {
            $this->_takeover[$sFunction] = $callback;
        } else {
            $this->_checkover[$sFunction] = $callback;
        }
    }

    public function remClassFunction($sFunction)
    {
        $sFunction = strtolower($sFunction);
        if (isset($this->_takeover[$sFunction])) {
            unset($this->_takeover[$sFunction]);
        }
        if (isset($this->_checkover[$sFunction])) {
            unset($this->_checkover[$sFunction]);
        }
    }

    public function addClassVar($name, $value = null)
    {
        $this->_vars[$name] = (isset($value)) ? $value : $this->_oRealInstance->$name;
    }

    public function remClassVar($name)
    {
        if (array_key_exists($name, $this->_vars)) {
            unset($this->_vars[$name]);
        }
    }

    public function __call($func, $var)
    {
        $funca = strtolower($func);
        if (isset($this->_takeover[$funca])) {
            return call_user_func_array($this->_takeover[$funca], $var);
        } else {
            if (isset($this->_checkover[$funca])) {
                call_user_func_array($this->_checkover[$funca], $var);
            }

            return call_user_func_array(array($this->_oRealInstance, $func), $var);
        }
    }

    public function __get($nm)
    {
        // maybe should copy var line in __set function ???
        // if it would help to clone object properties...
        if (array_key_exists($nm, $this->_vars)) {
            return $this->_vars[$nm];
        }

        return $this->_oRealInstance->getConfigParam($nm);
    }

    /**
     * All tests are INDEPENDENT, so no real changes should be made to the real instance.
     * NOTE: after cleanup, all changes to oxConfig while modConfig was active are LOST.
     *
     * @param $nm
     * @param $val
     */
    public function __set($nm, $val)
    {
        $this->_vars[$nm] = $val;
    }

    public function __isset($nm)
    {
        if (array_key_exists($nm, $this->_vars)) {
            return isset($this->_vars[$nm]);
        }

        return isset($this->_oRealInstance->$nm);
    }

    public function __unset($nm)
    {
        if (array_key_exists($nm, $this->_vars)) {
            $this->_vars[$nm] = null;

            return;
        }
        unset($this->_oRealInstance->$nm);
    }
}

/**
 * Class modDB
 *
 * @deprecated
 */
class modDB extends modOXID
{
    // needed 4 modOXID
    public static $unitMOD = null;
    protected static $_inst = null;

    function modAttach($oObj = null)
    {
        parent::modAttach();
        $this->_oRealInstance = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        if (!$oObj) {
            $oObj = $this;
        }
        self::$unitMOD = $oObj;
        $this->addClassFunction('getDB', function () {return modDB::$unitMOD;});
    }

    static function getInstance()
    {
        if (!self::$_inst) {
            self::$_inst = new modDB();
        }
        if (!self::$unitMOD) {
            self::$_inst->modAttach();
        }

        return self::$_inst;
    }

    public function cleanup()
    {
        $this->remClassFunction('getDB');
        self::$unitMOD = null;
        parent::cleanup();
    }
}

if (!function_exists('replaceDirSeperator')) {
    /**
     * @param string $sDir
     * @return mixed
     * @deprecated since v4.0.0
     */
    function replaceDirSeperator($sDir)
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            return str_replace('/', '\\', $sDir);
        }

        return $sDir;
    }
}

if (!function_exists('preparePathArray')) {
    /**
     * @param string $aPaths
     * @param string $sBasePath
     * @deprecated since v4.0.0
     */
    function preparePathArray(&$aPaths, $sBasePath)
    {
        foreach (array_keys($aPaths) as $key) {
            $aPaths[$key] = $sBasePath . $aPaths[$key];
        }
        $aPaths = array_map('replaceDirSeperator', $aPaths);
    }
}

if (!function_exists('findphp')) {
    /**
     * @param $baseDir
     * @param array $aDirBlackList
     * @param array $aFileBlackList
     * @param array $aFileWhiteList
     * @return array
     * @deprecated since v4.0.0
     */
    function findphp($baseDir, $aDirBlackList = array(), $aFileBlackList = array(), $aFileWhiteList = array())
    {
        $baseDir = preg_replace('#/$#', '', $baseDir);
        $baseDir = replaceDirSeperator($baseDir);

        $dirs = array($baseDir);

        preparePathArray($aDirBlackList, $baseDir);
        preparePathArray($aFileBlackList, $baseDir);
        preparePathArray($aFileWhiteList, $baseDir);


        //get directories (do not go to blacklist)
        foreach ($dirs as $dir) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $sdir) {
                if (array_search($sdir, $aDirBlackList) === false) {
                    $dirs[] = $sdir;
                }
            }
        }

        // get PHP files form directories
        $aFiles = array();
        foreach ($dirs as $dir) {
            $aFiles = array_merge($aFiles, glob($dir . DIRECTORY_SEPARATOR . "*.php", GLOB_NOSORT));
        }

        //remove files existing in file blacklist
        foreach ($aFileBlackList as $sFile) {
            $iNR = array_search($sFile, $aFiles);
            if ($iNR !== false) {
                unset ($aFiles[$iNR]);
            }
        }
        // add files from white list
        foreach ($aFileWhiteList as $sFile) {
            $aFiles[] = $sFile;
        }

        return $aFiles;
    }
}

if (!function_exists('preg_stripper')) {
    /**
     * @param $matches
     * @return null|string|string[]
     * @deprecated since v4.0.0
     */
    function preg_stripper($matches)
    {
        return preg_replace('/.*/', '', $matches[0]);
    }
}

if (!function_exists('stripCodeLines')) {
    /**
     * @param $sFile
     * @param $sCCarrayDir
     * @return array|mixed
     * @throws Exception
     * @deprecated since v4.0.0
     */
    function stripCodeLines($sFile, $sCCarrayDir)
    {
        if (!file_exists($sFile)) {
            throw new Exception("\n" . 'File "' . $sFile . '" does not exists, skipping');
        }


        $sFileContentMD5 = md5_file($sFile);
        $sCCFileName = $sCCarrayDir . md5($sFile) . "." . $sFileContentMD5;
        // delete unneeded files
        $aArray = glob($sCCarrayDir . md5($sFile) . ".*");
        $blFound = false;
        if (count($aArray)) {
            while ($aArray) {
                $sF = array_pop($aArray);
                if (!$blFound && $sF === $sCCFileName) {
                    $blFound = true;
                } else {
                    unlink($sF);
                }
            }
        }
        if (!$blFound) {
            $aFile = file_get_contents($sFile);

            $aFile = str_replace(' ', '', $aFile);
            $aFile = str_replace("\t", '', $aFile);
            $aFile = str_replace("\r", '', $aFile);

            $aFile = preg_replace('#//.*#', '', $aFile);
            $aFile = preg_replace_callback('#/\*.*?\*/#sm', 'preg_stripper', $aFile);

            // for variables
            $aFile = preg_replace('#(public|static|protected|private|var|\{|\}).*;#', '', $aFile);
            //for functions
            $aFile = preg_replace('#(public|static|protected|private|var|\{|\})#', '', $aFile);

            $aFile = preg_replace('#^class.*?$#m', '', $aFile);
            $aFile = preg_replace_callback('/\?>.*?<\?php/sm', 'preg_stripper', $aFile);
            $aFile = preg_replace_callback('/\?>.*?<\?/sm', 'preg_stripper', $aFile);
            $aFile = preg_replace_callback('/\.*?<\?php/sm', 'preg_stripper', $aFile);
            $aFile = preg_replace_callback('/\.*?<\?/sm', 'preg_stripper', $aFile);

            $aFile = preg_replace_callback('/\?>.*/sm', 'preg_stripper', $aFile);

            $aFile = preg_replace('#^\$[a-zA-Z0-9_]+;$#m', '', $aFile);

            $aFile = preg_replace('#^function[a-zA-Z0-9_]+\(.*?\)\{?$#m', '', $aFile);
            $aFile = preg_replace('#.+#', '1', $aFile);

            $aFile = preg_replace('#^$#m', '0', $aFile);
            $aFile = str_replace("\n", '', $aFile);
            $aCC = array();
            for ($i = 0; $i < strlen($aFile); $i++) {
                if ($aFile[$i] === '1') {
                    $aCC[$i + 1] = -1;
                }
            }
            file_put_contents($sCCFileName, serialize($aCC));

            return $aCC;
        } else {
            return unserialize(file_get_contents($sCCFileName));
        }
    }
}
