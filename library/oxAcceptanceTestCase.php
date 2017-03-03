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

require_once TEST_LIBRARY_PATH . 'oxMinkWrapper.php';
require_once TEST_LIBRARY_PATH . 'oxObjectValidator.php';
require_once TEST_LIBRARY_PATH . 'oxTranslator.php';
require_once TEST_LIBRARY_PATH . 'oxRetryTestException.php';
require_once TEST_LIBRARY_PATH . "oxTestModuleLoader.php";

/**
 * Base class for Selenium tests.
 */
class oxAcceptanceTestCase extends oxMinkWrapper
{
    /** @var int How much time to wait for pages to load. Wait time is multiplied by this value. */
    protected $_iWaitTimeMultiplier = 1;

    /** @var int How many times to retry after server error. */
    protected $retryTimes = 2;

    /** @var bool Whether to start mink session before test run. New tests can start session in runtime. */
    protected $_blStartMinkSession = false;

    /** @var string Default Mink driver. */
    protected $_blDefaultMinkDriver = 'selenium';

    /** @var bool Is logging of function calls length enabled */
    protected $_blEnableLog = false;

    /** @var array List of frames. Used to go to correct frame from the top frame. */
    protected $_aFramePaths = array(
        "basefrm" => "basefrm",
        "header" => "header",
        "edit" => "basefrm/edit",
        "list" => "basefrm/list",
        "navigation" => "navigation/adminnav",
        "adminnav" => "navigation/adminnav",
        "dynexport_main" => "basefrm/dynexport_main",
        "dynexport_do" => "basefrm/dynexport_do",
    );

    /** @var bool Tracks the start of tests run. */
    protected static $testsSuiteStarted = false;

    /** @var string Tests suite path. */
    protected static $testsSuitePath = '';

    /** @var string Used to follow which frame is currently selected by driver. */
    private $selectedFrame = 'relative=top';

    /** @var string Used to follow which window is currently selected by driver. */
    private $selectedWindow = null;

    /** @var string Currently used mink driver. */
    private $currentMinkDriver = '';

    /** @var int How many retry times are left. */
    private $retryTimesLeft;

    /** @var oxTranslator Translator object */
    protected static $translator = null;

    /** @var oxObjectValidator Object validator object */
    private $validator = null;

    /** @var \Selenium\Client Selenium client. Used only with selenium driver. */
    private $client = null;

    /** @var \Behat\Mink\Session Mink session */
    private static $minkSession = null;

    /** @var oxTestModuleLoader Module loader. */
    private static $moduleLoader = null;

    /**
     * Configuation object for test parameters
     * @var oxTestConfig
     */
    private $oTestConfig;

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->retryTimesLeft = $this->retryTimes;
    }

    /**
     * Sets up default environment for tests.
     */
    protected function setUp()
    {
        $this->selectedFrame = 'relative=top';
        $this->currentMinkDriver = $this->_blDefaultMinkDriver;
        $this->selectedWindow = null;

        $currentTestsSuitePath = $this->getSuitePath();
        if (self::$testsSuitePath !== $currentTestsSuitePath) {
            self::$testsSuitePath = $currentTestsSuitePath;
            $this->setUpTestsSuite($currentTestsSuitePath);
        }
        $this->getTranslator()->setLanguage(1);

        $this->clearTemp();
        if ($this->isMinkSessionStarted()) {
            $this->clearCookies();
        }
    }

    /**
     * Sets up shop before running test case.
     * Does not use setUpBeforeClass to keep this method non-static.
     *
     * @param string $testSuitePath
     */
    public function setUpTestsSuite($testSuitePath)
    {
        if (!self::$testsSuiteStarted) {
            self::$testsSuiteStarted = true;
            $this->dumpDb('reset_suite_db_dump');
        } else {
            $this->restoreDb('reset_suite_db_dump');
        }

        $this->activateModules();
        $this->addTestData($testSuitePath);

        $this->dumpDb('reset_test_db_dump');
    }

    /**
     * Adds tests sql data to database.
     *
     * @param string $sTestSuitePath
     */
    public function addTestData($sTestSuitePath)
    {
        $config = $this->getTestConfig();
        $testDataPath = realpath($sTestSuitePath . '/testData/');
        if ($testDataPath) {
            $target = $config->getRemoteDirectory() ? $config->getRemoteDirectory() : $config->getShopPath();
            $oFileCopier = new oxFileCopier();
            $oFileCopier->copyFiles($testDataPath, $target);
        }

        $sTestSuitePath = realpath($sTestSuitePath . '/testSql/');

        $sFileName = $sTestSuitePath . '/demodata.sql';
        if (file_exists($sFileName)) {
            $this->importSql($sFileName);
        }

        $sFileName = $sTestSuitePath . '/demodata_' . SHOP_EDITION . '.sql';
        if (file_exists($sFileName)) {
            $this->importSql($sFileName);
        }

        $sFileName = $sTestSuitePath . '/demodata_EE_mall.sql';
        if ($config->getShopEdition() == 'EE' && $config->isSubShop() && file_exists($sFileName)) {
            $this->importSql($sFileName);
        }
    }

    /**
     * Restores database after every test.
     *
     * @return null
     */
    protected function tearDown()
    {
        $this->restoreDB('reset_test_db_dump');

        parent::tearDown();
    }

    /**
     * Runs the bare test sequence.
     */
    public function runBare()
    {
        if ($this->_blStartMinkSession) {
            $this->startMinkSession();
        }

        parent::runBare();

        $this->retryTimesLeft = $this->retryTimes;
    }

    /**
     * Prints error message, closes active browsers windows and stops.
     *
     * @param string    $sErrorMsg       Message to display about error place (more easy to find for programmers).
     * @param Exception $oErrorException Exception to throw on error.
     */
    public function stopTesting($sErrorMsg, $oErrorException = null)
    {
        if ($oErrorException) {
            try {
                $this->onNotSuccessfulTest($oErrorException);
            } catch (Exception $oE) {
                if ($oE instanceof PHPUnit_Framework_ExpectationFailedException) {
                    $sErrorMsg .= "\n\n---\n" . $oE->getCustomMessage();
                }
            }
        }
        echo $sErrorMsg;
        echo " Selenium tests terminated.";
        $this->stopMinkSession();

        exit(1);
    }

    /* --------------------- Functions for both admin and frontend -----------------------------*/

    /**
     * Opens new browser window
     *
     * @param string $sUrl         Url to open
     * @param bool   $blClearCache Clears cache before opening page
     */
    public function openNewWindow($sUrl, $blClearCache = true)
    {
        $this->selectedFrame = 'relative=top';
        try {
            $this->selectWindow(null);
            $this->windowMaximize(null);
        } catch (\Behat\Mink\Exception\Exception $e) {
            // Do nothing if methods not implemented, for example with headless driver.
        }

        if ($blClearCache) {
            $this->clearTemp();
        }

        try {
            $this->open($sUrl);
        } catch (Exception $e) {
            usleep(500000);
            $this->open($sUrl);
        }
        $this->checkForErrors();
    }

    /**
     * $_oTranslator setter
     *
     * @param object $oTranslator
     */
    public static function setTranslator($oTranslator)
    {
        self::$translator = $oTranslator;
    }

    /**
     * $_oTranslator getter
     *
     * @return oxTranslator
     */
    public static function getTranslator()
    {
        if (is_null(self::$translator)) {
            self::$translator = new oxTranslator();
        }
        return self::$translator;
    }

    /**
     * Calls oxTranslator and tries to translate a string
     * throws fail if string is found, but can't be translated
     *
     * @param string $sString
     *
     * @return null
     */
    public static function translate($sString)
    {
        $sString = self::getTranslator()->translate($sString);
        $aUntranslated = self::getTranslator()->getUntranslated();
        if (count($aUntranslated) > 0) {
            self::fail("Untranslated strings: " . implode(', ', $aUntranslated));
        }
        return $sString;
    }

    /* --------------------- eShop frontend side only functions ---------------------- */

    /**
     * opens shop frontend and runs checkForErrors().
     *
     * @param bool        $blForceMainShop Opens main shop even if SubShop is being tested.
     * @param bool        $blClearCache    Whether to clear cache.
     * @param bool|string $blForceSubShop  Opens sub shop even if man shop is being tested.
     *
     * @return null
     */
    public function openShop($blForceMainShop = false, $blClearCache = false, $mForceSubShop = false)
    {
        $this->openNewWindow(shopURL, $blClearCache);

        if ($this->getTestConfig()->isSubShop() || $mForceSubShop) {
            if (!$blForceMainShop) {
                if (!is_string($mForceSubShop)) {
                    $mForceSubShop = "link=subshop";
                }
                $this->clickAndWait($mForceSubShop);
            } else {
                $sShopNr = $this->getShopVersionNumber();
                $this->clickAndWait("link=OXID eShop " . $sShopNr);
            }
        }
        $this->checkForErrors();
    }

    /**
     * Selects shop language in frontend.
     *
     * @param string $language Language title.
     */
    public function switchLanguage($language)
    {
        $this->click("languageTrigger");
        $this->waitForItemAppear("languages");
        $this->clickAndWait("//ul[@id='languages']//li/a/span[text()='" . $language . "']");
        $this->getTranslator()->setLanguageByName($language);
    }

    /**
     * Selects shop currency in frontend.
     *
     * @param string $currency Currency title.
     */
    public function switchCurrency($currency)
    {
        $this->click("//p[@id='currencyTrigger']/a");
        $this->waitForItemAppear("currencies");
        $this->clickAndWait("//ul[@id='currencies']//*[text()='$currency']");
    }

    /**
     * Login customer by using login fly out form.
     *
     * @param string  $userName     User name (email).
     * @param string  $userPass     User password.
     * @param boolean $waitForLogin If needed to wait until user get logged in.
     */
    public function loginInFrontend($userName, $userPass, $waitForLogin = true)
    {
        $this->selectWindow(null);
        $this->click("//ul[@id='topMenu']/li[1]/a");
        try {
            $this->waitForItemAppear("loginBox", 2);
        } catch (Exception $e) {
            $this->click("//ul[@id='topMenu']/li[1]/a");
            $this->waitForItemAppear("loginBox", 2);
        }
        $this->type("//div[@id='loginBox']//input[@name='lgn_usr']", $userName);
        $this->type("//div[@id='loginBox']//input[@name='lgn_pwd']", $userPass);

        $this->clickAndWait("//div[@id='loginBox']//button[@type='submit']");
        if ($waitForLogin) {
            $this->waitForElement("//a[@id='logoutLink']");
        }
    }

    /**
     * Open article page.
     *
     * @param string $articleId
     * @param bool   $clearCache
     * @param string $shopId
     */
    public function openArticle($articleId, $clearCache = false, $shopId = null)
    {
        $aParams = array(
            'cl' => 'details',
            'anid' => $articleId,
        );

        $this->openNewWindow($this->_getShopUrl($aParams, $shopId), $clearCache);
    }

    /**
     * Adds article to basket
     *
     * @param string $articleId        Article id
     * @param int    $amount           Amount of items to add
     * @param string $controller       Controller name which should be opened after article is added
     * @param array  $additionalParams Additional parameters (like persparam[details] for label)
     * @param int    $shopId           Shop id
     */
    public function addToBasket(
        $articleId,
        $amount = 1,
        $controller = 'basket',
        $additionalParams = array(),
        $shopId = null
    ) {
        $oInput = $this->getElement('stoken', false);
        if ($oInput) {
            $aParams['stoken'] = $oInput->getValue();
        }
        $aParams['cl'] = $controller;
        $aParams['fnc'] = 'tobasket';
        $aParams['aid'] = $articleId;
        $aParams['am'] = $amount;
        $aParams['anid'] = $articleId;

        $aParams = array_merge($aParams, $additionalParams);

        $this->openNewWindow($this->_getShopUrl($aParams, $shopId), false);
    }

    /**
     * mouseOver element and then click specified link.
     *
     * @param string $element1 MouseOver element.
     * @param string $element2 Clickable element.
     */
    public function mouseOverAndClick($element1, $element2)
    {
        $this->mouseOver($element1);
        $this->waitForItemAppear($element2);
        $this->clickAndWait($element2);
    }

    /**
     * Performs search for selected parameter.
     *
     * @param string $searchParam search parameter.
     */
    public function searchFor($searchParam)
    {
        $this->type("//input[@id='searchParam']", $searchParam);
        $this->keyPress("searchParam", "\\13"); //presing enter key
        $this->waitForPageToLoad(10000);
        $this->checkForErrors();
    }

    /**
     * Opens basket.
     *
     * @param string $language active language in shop.
     */
    public function openBasket($language = "English")
    {
        if ($language == 'Deutsch') {
            $sLink = "Warenkorb zeigen";
        } else {
            $sLink = "Display cart";
        }
        $this->click("//div[@id='miniBasket']/img");
        $this->waitForItemAppear("//div[@id='basketFlyout']//a[text()='" . $sLink . "']");
        $this->clickAndWait("//div[@id='basketFlyout']//a[text()='" . $sLink . "']");
    }

    /**
     * Selects specified value from dropdown (sorting, items per page etc).
     *
     * @param int    $elementId  Drop down element id.
     * @param string $itemValue  Item to select.
     * @param string $extraIdent Additional identification for element.
     */
    public function selectDropDown($elementId, $itemValue = '', $extraIdent = '')
    {
        $this->assertElementPresent($elementId);
        $this->assertFalse($this->isVisible("//div[@id='" . $elementId . "']//ul"));
        $this->click("//div[@id='" . $elementId . "']//p");
        $this->waitForItemAppear("//div[@id='" . $elementId . "']//ul");
        if ('' == $itemValue) {
            $this->clickAndWait("//div[@id='" . $elementId . "']//ul/" . $extraIdent . "/a");
        } else {
            $this->clickAndWait(
                "//div[@id='" . $elementId . "']//ul/" . $extraIdent . "/a[text()='" . $itemValue . "']"
            );
        }
    }

    /**
     * Selects specified value from dropdown (for multidimensional variants).
     *
     * @param string $elementId            Container id.
     * @param int    $elementNr            Select list number (e.g. 1, 2).
     * @param string $itemValue            Item to select.
     * @param string $sSelectedCombination Waits for selected combination to change.
     */
    public function selectVariant($elementId, $elementNr, $itemValue, $sSelectedCombination = '')
    {
        $this->assertElementPresent($elementId);
        $this->assertFalse($this->isVisible("//div[@id='" . $elementId . "']/div[" . $elementNr . "]//ul"));
        $this->click("//div[@id='" . $elementId . "']/div[" . $elementNr . "]//p");

        $this->waitForItemAppear("//div[@id='" . $elementId . "']/div[" . $elementNr . "]//ul");
        $this->click("//div[@id='" . $elementId . "']/div[" . $elementNr . "]//ul//a[text()='" . $itemValue . "']");

        if (!empty($sSelectedCombination)) {
            $this->waitForText("%SELECTED_COMBINATION%: $sSelectedCombination");
        }
    }

    /* -------------------------- Admin side only functions ------------------------ */

    /**
     * login to admin with default admin pass and opens needed menu.
     *
     * @param string $menuLink1     Menu link (e.g. master settings, shop settings).
     * @param string $menuLink2     Sub menu link (e.g. administer products, discounts, vat).
     * @param bool   $forceMainShop Force main shop.
     * @param string $user          Shop admin username.
     * @param string $pass          Shop admin password.
     * @param string $language      Shop admin language.
     */
    public function loginAdmin(
        $menuLink1 = null,
        $menuLink2 = null,
        $forceMainShop = false,
        $user = "admin@myoxideshop.com",
        $pass = "admin0303",
        $language = "English"
    ) {
        $this->openNewWindow(shopURL . "admin");
        $this->waitForElement('usr');
        $this->waitForElement('pwd');
        $this->type("usr", $user);
        $this->type("pwd", $pass);
        $this->select("lng", "$language");
        $this->select("prf", "Standard");
        $this->clickAndWait("//input[@type='submit']");

        $this->frame("navigation");

        if ($this->getTestConfig()->isSubShop() && !$forceMainShop) {
            $this->selectAndWaitFrame("selectshop", "label=subshop", "basefrm");
        }

        if ($menuLink1 && $menuLink2) {
            $this->selectMenu($menuLink1, $menuLink2);
        } else {
            $this->frame("basefrm");
        }
    }

    /**
     * login to admin for PayPal shop with admin pass and opens needed menu.
     *
     * @param string $menuLink1     Menu link (e.g. master settings, shop settings).
     * @param string $menuLink2     Sub menu link (e.g. administer products, discounts, vat).
     * @param string $editElement   Element to check in edit frame (optional).
     * @param string $listElement   Element to check in list frame (optional).
     * @param bool   $forceMainShop Force main shop.
     * @param string $user          Shop admin username.
     * @param string $pass          Shop admin password.
     * @param string $language      Shop admin language.
     */
    public function loginAdminForModule(
        $menuLink1,
        $menuLink2,
        $editElement = null,
        $listElement = null,
        $forceMainShop = false,
        $user = "admin",
        $pass = "admin",
        $language = "English"
    ) {
        $this->loginAdmin($menuLink1, $menuLink2, $forceMainShop, $user, $pass, $language);
    }

    /**
     * login to admin with admin pass, selects subshop and opens needed menu.
     *
     * @param string $menuLink1 Menu link (e.g. master settings, shop settings).
     * @param string $menuLink2 Sub menu link (e.g. administer products, discounts, vat).
     * @param string $user      Shop admin username.
     * @param string $pass      Shop admin password.
     */
    public function loginSubshopAdmin($menuLink1, $menuLink2, $user = "admin@myoxideshop.com", $pass = "admin0303")
    {
        $this->openNewWindow(shopURL . "admin");
        $this->waitForElement('user');
        $this->waitForElement('pwd');
        $this->type("user", $user);
        $this->type("pwd", $pass);
        $this->select("chlanguage", "label=English");
        $this->select("profile", "label=Standard");
        $this->clickAndWait("//input[@type='submit']");

        $this->frame("navigation");

        $this->selectAndWaitFrame("selectshop", "label=subshop", "basefrm");

        $this->selectMenu($menuLink1, $menuLink2);
    }

    /**
     * login to trusted shops in admin.
     *
     * @param string $link1 Navigation group link.
     * @param string $link2 Navigation link.
     * @param string $user  shop admin username.
     * @param string $pass  shop admin password.
     */
    public function loginAdminTs(
        $link1 = "link=Seal of quality",
        $link2 = "link=Trusted Shops",
        $user = "admin@myoxideshop.com",
        $pass = "admin0303"
    ) {
        oxDb::getInstance()->getDb()->Execute(
            "UPDATE `oxconfig` SET `OXVARVALUE` = 0xce92 WHERE `OXVARNAME` = 'sShopCountry';"
        );

        $this->openNewWindow(shopURL . "admin");
        $this->type("user", $user);
        $this->type("pwd", $pass);
        $this->select("chlanguage", "label=English");
        $this->select("profile", "label=Standard");
        $this->clickAndWait("//input[@type='submit']");

        $this->frame("navigation");

        if ($this->getTestConfig()->isSubShop()) {
            $this->selectAndWaitFrame("selectshop", "label=subshop", "edit");
        }
        $this->waitForElement($link1);
        $this->click($link1);
        $this->click($link2);

        $this->waitForFrameToLoad('basefrm', 10000);

        if (OXID_VERSION_PE_CE) :
            $this->openTab('Interface');
        endif;

        //testing edit frame for errors
        $this->frame("edit");
    }

    /**
     * selects other menu in admin interface.
     *
     * @param string $menuLink1 menu link (e.g. master settings, shop settings).
     * @param string $menuLink2 sub menu link (e.g. administer products, discounts, vat).
     */
    public function selectMenu($menuLink1, $menuLink2)
    {
        $this->selectWindow(null);

        $this->frame('navigation');

        $this->waitForElement("link=" . $menuLink1);
        $this->click("link=" . $menuLink1);
        $this->click("link=" . $menuLink2);

        $this->waitForFrameToLoad('basefrm', 5000, true);
        $this->frame("basefrm");
        if ($this->isElementPresent('edit')) {
            $this->waitForFrameToLoad('edit', 5000, true);
            $this->frame("edit");
            $sFrameToLoad = "list";
        } else {
            $sFrameToLoad = $this->isElementPresent('list') ? 'list' : 'basefrm';
        }

        $this->frame($sFrameToLoad);
    }

    /**
     * Logs out of admin
     *
     * @param string $sLocator logout link locator
     */
    public function logoutAdmin($sLocator = "link=Logout")
    {
        $this->frame("header");
        $this->waitForElement($sLocator);
        $this->click($sLocator);

        try {
            $this->waitForPageToLoad(10000);
        } catch (Exception $e) {
            $this->openNewWindow(shopURL . "admin");
        }

        $this->checkForErrors();
    }

    /**
     * select frame in Admin interface.
     *
     * @param string $sFrame          Name of the frame.
     * @param bool   $blForceReselect Switches frame even if it is currently selected
     * @param bool   $blFollowPath    If path to frame is defined, it selects all frames in path
     *
     * @return null
     */
    public function frame($sFrame, $blForceReselect = false, $blFollowPath = true)
    {
        if (!$blForceReselect && $this->getSelectedFrame() == $sFrame) {
            return;
        }

        if ($blFollowPath && $this->_aFramePaths[$sFrame]) {
            $aPath = explode("/", $this->_aFramePaths[$sFrame]);
            $this->_selectFrameByPath($aPath);
        } else {
            $this->selectFrame($sFrame);
        }

        $this->checkForErrors();
    }

    /**
     * Returns given frame parent. If none selected - returns current frame parent
     *
     * @param string $sFrame
     *
     * @return string real frame name
     */
    public function selectParentFrame($sFrame = null)
    {
        $sFrame = $sFrame ? $sFrame : $this->getSelectedFrame();

        if ($this->_aFramePaths[$sFrame]) {
            $aPath = explode("/", $this->_aFramePaths[$sFrame]);
            $sFrame = array_pop($aPath);
            $this->_selectFrameByPath($aPath);
        } else {
            $this->selectFrame("relative=top");
        }

        return $sFrame;
    }

    /**
     * Clicks new item button
     *
     * @param string $sButtonSelector
     */
    public function clickCreateNewItem($sButtonSelector = "btn.new")
    {
        $this->frame('edit');
        $this->click($sButtonSelector);
        $this->waitForFrameToLoad('list', 5000);
        $this->waitForFrameToLoad('edit', 5000, true);
    }

    /**
     * Opens admin list item. Activates edit frame after
     *
     * @param string $sSorterSelector
     */
    public function changeListSorting($sSorterSelector)
    {
        $this->frame('list');
        $this->clickAndWaitFrame($sSorterSelector);
        $this->checkForErrors();
    }

    /**
     * Opens admin list item. Activates edit frame after
     *
     * @param string $sItemName
     * @param string $sSearchColumn
     */
    public function openListItem($sItemName, $sSearchColumn = '')
    {
        $sItemName = $this->translate($sItemName);
        $this->frame('list');
        $sItemLocator = ((strpos($sItemName, 'link=') === false) ? 'link=' : '') . $sItemName;

        if ($sSearchColumn && !$this->isElementPresent($sItemLocator)) {
            $this->type("where$sSearchColumn", $sItemName);
            $this->clickAndWaitFrame('submitit');
        }
        $this->clickAndWaitFrame($sItemLocator, 'edit');
        $this->frame('edit');
        $this->checkForErrors();
    }

    /**
     * Opens admin list item. Activates edit frame after
     *
     * @param string $sPageSelector
     */
    public function openListPage($sPageSelector)
    {
        $this->frame('list');
        $this->clickAndWaitFrame($sPageSelector);
        $this->checkForErrors();
    }

    /**
     * clicks entered link in list frame and selects edit frame.
     *
     * @param string $tabName tab name that should be opened.
     */
    public function openTab($tabName)
    {
        $this->frame('list');
        $tabName = "//div[@class='tabs']//a[text()='$tabName']";
        $this->clickAndWaitFrame($tabName, 'edit');
        $this->frame('edit');
    }

    /**
     * Asserts that two variables are equal.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     * @param int    $delta
     * @param int    $maxDepth
     * @param bool   $canonicalize
     * @param bool   $ignoreCase
     */
    public static function assertEquals(
        $expected,
        $actual,
        $message = '',
        $delta = 0,
        $maxDepth = 10,
        $canonicalize = false,
        $ignoreCase = false
    ) {
        $expected = self::translate($expected);
        $actual = self::translate($actual);

        $expected = self::_clearString($expected);
        $sMessage = "'$expected' != '$actual' with message: " . $message;

        parent::assertEquals($expected, $actual, $sMessage, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * Opens admin list item. Activates edit frame after
     *
     * @param string $sLanguage
     * @param string $sSelectLocator
     */
    public function changeAdminListLanguage($sLanguage, $sSelectLocator = 'changelang')
    {
        $sSelectedFrame = $this->getSelectedFrame();
        $this->frame('list');
        $this->_changeAdminLanguage($sLanguage, $sSelectLocator);
        $this->frame($sSelectedFrame);
    }

    /**
     * Opens admin list item. Activates edit frame after
     *
     * @param string $sLanguage
     * @param string $sSelectLocator
     */
    public function changeAdminEditLanguage($sLanguage, $sSelectLocator = 'subjlang')
    {
        $this->frame('edit');
        $this->_changeAdminLanguage($sLanguage, $sSelectLocator);
    }

    /**
     * Selects language and checks if it stays selected. If not - re-selects.
     *
     * @param string $sLanguage
     * @param string $sSelectLocator
     */
    protected function _changeAdminLanguage($sLanguage, $sSelectLocator)
    {
        $this->selectAndWaitFrame($sSelectLocator, "label=$sLanguage", "edit");
        if ($this->getSelectedLabel($sSelectLocator) != $sLanguage) {
            $this->selectAndWaitFrame($sSelectLocator, "label=$sLanguage", "edit");
        }
        $this->checkForErrors();
    }

    /**
     * Clicks delete item button in list
     *
     * @param string $sId List item id.
     */
    public function clickDeleteListItem($sId = '1')
    {
        $this->frame('list');
        $this->clickAndConfirm("del.$sId", "edit");
    }

    /**
     * Selects popUp window and waits till it is fully loaded.
     *
     * @param string $popUpElement element used to check if popUp is fully loaded.
     */
    public function usePopUp($popUpElement = "//div[@id='container1_c']/table/tbody[2]/tr[1]/td[1]")
    {
        $this->waitForPopUp("ajaxpopup", 15000);
        $this->selectWindow("ajaxpopup");
        $this->windowMaximize("ajaxpopup");
        $this->waitForElement($popUpElement);
        $this->checkForErrors();
    }

    /**
     * Waits for element to show up in specific place.
     *
     * @param string $value       expected text to show up.
     * @param string $locator     place where specified text must show up.
     * @param int    $iTimeToWait timeout
     *
     * @return null
     */
    public function waitForAjax($value, $locator, $iTimeToWait = 20)
    {
        $iTimeToWait = $iTimeToWait * $this->_iWaitTimeMultiplier;
        for ($iSecond = 0; $iSecond <= $iTimeToWait; $iSecond++) {
            try {
                if ($this->isElementPresent($locator) && $value == $this->getText($locator)) {
                    return;
                }
            } catch (Exception $e) {
            }
            if ($iSecond >= $iTimeToWait) {
                $this->retryTest("Ajax timeout while waiting for '${locator}' or value is not equal to '${value}' ");
            }
            usleep(500000);
        }
    }

    /**
     * Drags and drops element to specified location.
     *
     * @param string $item      element which will be dragged and dropped.
     * @param string $container place where to drop specified element.
     */
    public function dragAndDrop($item, $container)
    {
        $this->click($item);
        $this->checkForErrors();
        $this->dragAndDropToObject($item, $container);
        if ($this->isElementPresent($item))  {
            sleep(1);
        }
    }

    /* ------------------------ Selenium API related functions, override functions ---------------------- */

    /**
     * Opens new window in popUp
     *
     * @param string $sUrl
     * @param string $sId
     */
    public function openWindow($sUrl, $sId)
    {
        parent::openWindow($sUrl, $sId);
        $this->selectWindow($sId);
        $this->waitForPageToLoad(10000);
    }

    /**
     * Clicks link/button and waits till page will be loaded. then checks for errors.
     * recommended to use in frontend. use in admin only, if this click wont relode frames.
     *
     * @param string $locator  Link/button locator in the page.
     * @param int    $iSeconds How much time to wait for element.
     *
     * @return null
     */
    public function clickAndWait($locator, $iSeconds = 10)
    {
        $locator = $this->translate($locator);
        if ($this->getSelectedFrame() != 'relative=top') {
            $this->clickAndWaitFrame($locator);
            return;
        }

        $this->click($locator);
        try {
            $this->waitForPageToLoad($iSeconds * 1000);
        } catch (Exception $e) {
        }

        $this->checkForErrors();
    }

    /**
     * Selects label in select list and waits till page will be loaded. then checks for errors.
     * recommended to use in frontend. use in admin only, if this select wont reload frames.
     *
     * @param string $locator   select list locator.
     * @param string $selection option to select.
     * @param string $element   element locator for additional check if page is fully loaded (optional).
     *
     * @return null
     */
    public function selectAndWait($locator, $selection, $element = null)
    {
        if ($this->getSelectedFrame() != 'relative=top') {
            $this->selectAndWaitFrame($locator, $selection);
            return;
        }

        $this->waitForElement($locator);
        $this->select($locator, $selection);
        $this->waitForPageToLoad(10000);

        if ($element) {
            $this->waitForElement($element);
        }
        $this->checkForErrors();
    }

    /**
     * selects element and waits till needed frame will be loaded. same frame as before will be selected.
     *
     * @param string $locator select list locator.
     * @param string $frame   frame which should be also loaded (this frame will be loaded after current frame is loaded).
     * @return null
     */
    public function clickAndWaitFrame($locator, $frame = '')
    {
        $this->click($locator);
        $this->waitForFrameAfterAction($frame);
        $this->checkForErrors();
    }

    /**
     * selects element and waits till needed frame will be loaded. same frame as before will be selected.
     *
     * @param string $locator   select list locator.
     * @param string $selection option to select.
     * @param string $frame     frame which should be also loaded (this frame will be loaded after current frame is loaded).
     * @return null
     */
    public function selectAndWaitFrame($locator, $selection, $frame = '')
    {
        $this->waitForElement($locator);
        $this->select($locator, $selection);
        $this->waitForFrameAfterAction($frame);
        $this->checkForErrors();
    }

    /**
     * Clicks button and confirms dialog.
     * JavaScript confirmations will NOT pop up a visible dialog.
     * By default, the confirm action is as manually clicking OK.
     * This can be changed by prior execution of the chooseCancelOnNextConfirmation command.
     * If an confirmation is generated but you do not get/verify it, the next Selenium action will fail.
     *
     * @param string $locator locator for delete button.
     * @param string $frame   frame which should be also loaded (this frame will be loaded after current frame is loaded).
     * @return null
     */
    public function clickAndConfirm($locator, $frame = "")
    {
        $this->waitForElement($locator);
        $this->click($locator);
        $this->getConfirmation();
        $this->waitForFrameAfterAction($frame);

        $this->checkForErrors();
    }

    /**
     * Waits for frames to load after action.
     * If $sFrame is passed, will wait for this frame after main frame was loaded
     *
     * @param string $sFrame
     */
    protected function waitForFrameAfterAction($sFrame = '')
    {
        $sSelectedFrame = $this->getSelectedFrame();
        $sFrame = $sFrame ? $sFrame : $sSelectedFrame;

        if ($sFrame && $sSelectedFrame != $sFrame) {
            $this->waitForFrameToLoad($sSelectedFrame, 5000, true);
        }
        $this->waitForFrameToLoad($sFrame, 5000, true);
    }

    /**
     * Waits till element will appear in page (only IF such element DID NOT EXIST BEFORE).
     *
     * @param string $sLocator       element locator.
     * @param int    $iSeconds       How much time to wait for element.
     * @param bool   $blIgnoreResult whether not to fail if element will not appear in given time.
     * @return null
     */
    public function waitForElement($sLocator, $iSeconds = 10, $blIgnoreResult = false)
    {
        $this->_waitForAppear('isElementPresent', $sLocator, $iSeconds, $blIgnoreResult);
    }

    /**
     * Waits till element will appear in page (only IF such element DID NOT EXIST BEFORE).
     *
     * @param string $sLocator       element locator.
     * @param int    $iTimeToWait    How much time to wait for element.
     * @param bool   $blIgnoreResult whether not to fail if element will not appear in given time.
     * @return null
     */
    public function waitForEditable($sLocator, $iTimeToWait = 10, $blIgnoreResult = false)
    {
        $this->_waitForAppear('isEditable', $sLocator, $iTimeToWait, $blIgnoreResult);
    }

    /**
     * Waits for element to show up (only IF such element ALREADY EXIST AS HIDDEN AND WILL BE SHOWN AS VISIBLE).
     *
     * @param string $sLocator       element locator.
     * @param int    $iTimeToWait    time to wait for element.
     * @param bool   $blIgnoreResult whether not to fail if element will not appear in given time.
     * @return null
     */
    public function waitForItemAppear($sLocator, $iTimeToWait = 10, $blIgnoreResult = false)
    {
        $sLocator = $this->translate($sLocator);
        $this->_waitForAppear('isElementPresent', $sLocator, $iTimeToWait, $blIgnoreResult);
        $this->_waitForAppear('isVisible', $sLocator, $iTimeToWait, $blIgnoreResult);
    }

    /**
     * Waits for element to disappear (only IF such element WILL BE MARKED AS NOT VISIBLE).
     *
     * @param string $sLocator    element locator.
     * @param int    $iTimeToWait time to wait for element
     * @return null
     */
    public function waitForItemDisappear($sLocator, $iTimeToWait = 10)
    {
        $sLocator = $this->translate($sLocator);
        $this->_waitForDisappear('isVisible', $sLocator, $iTimeToWait);
    }

    /**
     * Waits till text will appear in page. If array is passed, waits for any of texts in array to appear.
     *
     * @param string|array $mTextMsg    If Array of Messages is passed, returns when either of given texts if found
     * @param bool         $printSource print source (default false).
     * @param int          $iTimeToWait timeout (default 10).
     * @return null
     */
    public function waitForText($mTextMsg, $printSource = false, $iTimeToWait = 10)
    {
        $mTextMsg = $this->translate($mTextMsg);
        $this->_waitForAppear('isTextPresent', $mTextMsg, $iTimeToWait);
    }

    /**
     * Waits till text will disappear from page.
     *
     * @param string $textLine    text.
     * @param int    $iTimeToWait timeout (default 10).
     * @return null
     */
    public function waitForTextDisappear($textLine, $iTimeToWait = 10)
    {
        $textLine = $this->translate($textLine);
        $this->_waitForDisappear('isTextPresent', $textLine, $iTimeToWait);
    }

    /**
     * Waits for specified method with given parameter to return true.
     * If multiple parameters is passed, waits till true is returned on any of them.
     *
     * @param string       $sMethod
     * @param string|array $mParams
     * @param int          $sTimeToWait
     * @param bool         $blIgnoreResult
     */
    protected function _waitForAppear($sMethod, $mParams, $sTimeToWait = 10, $blIgnoreResult = false)
    {
        $aParams = is_array($mParams) ? $mParams : array($mParams);

        $sTimeToWait = $sTimeToWait * 2 * $this->_iWaitTimeMultiplier;
        $blResetFrame = true;
        for ($iSecond = 0; $iSecond <= $sTimeToWait; $iSecond++) {
            if ($this->_isElementAppeared($sMethod, $aParams)) {
                return;
            }
            if ($blResetFrame && $iSecond >= $sTimeToWait / 2) {
                if ($this->getSelectedWindow() == null) {
                    $this->frame($this->getSelectedFrame(), true);
                }
                $blResetFrame = false;
            } else {
                if ($iSecond >= $sTimeToWait) {
                    if ($blIgnoreResult) {
                        return;
                    } else {
                        $sMessage = "Timeout waiting for '" . implode(' | ', $aParams) . "'.";
                        $this->retryTest($sMessage);
                    }
                }
            }
            usleep(500000);
        }
    }

    /**
     * @param string $sMethod
     * @param array  $aParams
     * @return bool
     */
    protected function _isElementAppeared($sMethod, $aParams)
    {
        foreach ($aParams as $sParam) {
            try {
                if ($this->$sMethod($sParam)) {
                    return true;
                }
            } catch (Exception $e) {
            }
        }
        return false;
    }

    /**
     * Waits for specified method with given message to return true.
     *
     * @param string $sMethod
     * @param string $sMessage
     * @param int    $sTimeToWait
     */
    protected function _waitForDisappear($sMethod, $sMessage, $sTimeToWait = 30)
    {
        $sTimeToWait = $sTimeToWait * 2 * $this->_iWaitTimeMultiplier;
        for ($iSecond = 0; $iSecond <= $sTimeToWait; $iSecond++) {
            try {
                if (!$this->$sMethod($sMessage)) {
                    return;
                }
            } catch (Exception $e) {
            }

            if ($iSecond >= $sTimeToWait) {
                $this->fail("Timeout waiting for '$sMessage'");
            }
            usleep(500000);
        }
    }

    /**
     * Overrides original method - waits for element before checking for text
     *
     * @param string $sLocator text to be searched
     * @return bool
     */
    public function getText($sLocator)
    {
        $sLocator = $this->translate($sLocator);
        $this->waitForElement($sLocator);
        return parent::getText($sLocator);
    }

    /**
     * selects element and waits till needed frame will be loaded. same frame as before will be selected.
     *
     * @param string $sLocator select list locator.
     * @return null
     */
    public function click($sLocator)
    {
        $sLocator = $this->translate($sLocator);
        return parent::click($sLocator);
    }

    /**
     * @param $sSelector
     * @param $sOptionSelector
     */
    public function select($sSelector, $sOptionSelector)
    {
        $sSelector = $this->translate($sSelector);
        $sOptionSelector = $this->translate($sOptionSelector);
        return parent::select($sSelector, $sOptionSelector);
    }

    /**
     * Checks if element is visible. If element is not found, waits for it to appear and checks again.
     *
     * @param string $sLocator
     * @return bool
     */
    public function isVisible($sLocator)
    {
        $sLocator = $this->translate($sLocator);
        return parent::isVisible($sLocator);
    }

    /**
     * Skip test code until given date.
     *
     * @param string $sDate Date string in format 'Y-m-d'.
     *
     * @return bool
     */
    public function skipTestBlockUntil($sDate)
    {
        $blSkip = false;
        $oDate = DateTime::createFromFormat('Y-m-d', $sDate);
        if (time() >= $oDate->getTimestamp()) {
            $blSkip = true;
        }
        return $blSkip;
    }

    /**
     * Asserts that element is present.
     *
     * @param string $sLocator element locator
     * @param string $sMessage fail message
     * @return void
     */
    public function assertElementPresent($sLocator, $sMessage = '')
    {
        $sLocator = $this->translate($sLocator);
        $sFailMessage = "Element $sLocator was not found! " . $sMessage;
        $this->assertTrue($this->isElementPresent($sLocator), $sFailMessage);
    }

    /**
     * Asserts that element is not present.
     *
     * @param string $sLocator element locator
     * @param string $sMessage fail message
     * @return void
     */
    public function assertElementNotPresent($sLocator, $sMessage = '')
    {
        $sFailMessage = "Element $sLocator was found though it should not be present! " . $sMessage;
        $this->assertFalse($this->isElementPresent($sLocator), $sFailMessage);
    }

    /**
     * Asserts that text is present.
     *
     * @param string $sText    text to search
     * @param string $sMessage fail message
     * @return void
     */
    public function assertTextPresent($sText, $sMessage = '')
    {
        $sText = $this->translate($sText);
        $sFailMessage = "Text '$sText' was not found! " . $sMessage;
        $this->assertTrue($this->isTextPresent($sText), $sFailMessage);
    }

    /**
     * Asserts that text is not present.
     *
     * @param string $sText    text to search
     * @param string $sMessage fail message
     * @return void
     */
    public function assertTextNotPresent($sText, $sMessage = '')
    {
        $sText = $this->translate($sText);
        $sFailMessage = "Text '$sText' should not be found! " . $sMessage;
        $this->assertFalse($this->isTextPresent($sText), $sFailMessage);
    }

    /**
     * Asserts that element is visible.
     *
     * @param string $sLocator element to search
     * @param string $sMessage fail message
     * @return void
     */
    public function assertElementVisible($sLocator, $sMessage = '')
    {
        $sFailMessage = "Element '$sLocator' is not visible! " . $sMessage;
        $this->assertTrue($this->isVisible($sLocator), $sFailMessage);
    }

    /**
     * Asserts that element is not visible.
     *
     * @param string $sLocator element to search
     * @param string $sMessage fail message
     * @return void
     */
    public function assertElementNotVisible($sLocator, $sMessage = '')
    {
        $sFailMessage = "Element '$sLocator' should not be visible! " . $sMessage;
        $this->assertFalse($this->isVisible($sLocator), $sFailMessage);
    }

    /**
     * Asserts that element is editable.
     *
     * @param string $sLocator element to search
     * @param string $sMessage fail message
     * @return void
     */
    public function assertElementEditable($sLocator, $sMessage = '')
    {
        $sFailMessage = "Element '$sLocator' is not editable! " . $sMessage;
        $this->assertTrue($this->isEditable($sLocator), $sFailMessage);
    }

    /**
     * Asserts that element is not editable.
     *
     * @param string $sLocator element to search
     * @param string $sMessage fail message
     * @return void
     */
    public function assertElementNotEditable($sLocator, $sMessage = '')
    {
        $sFailMessage = "Element '$sLocator' should not be editable! " . $sMessage;
        $this->assertFalse($this->isEditable($sLocator), $sFailMessage);
    }

    /**
     * Asserts that element is checked.
     *
     * @param string $sSelector
     * @param string $sMessage
     */
    public function assertChecked($sSelector, $sMessage = '')
    {
        $sFormedMessage = "Element '$sSelector' was expected to be checked! $sMessage";
        $this->assertTrue($this->isChecked($sSelector), $sFormedMessage);
    }

    /**
     * Asserts that element is not checked.
     *
     * @param string $sSelector
     * @param string $sMessage
     */
    public function assertNotChecked($sSelector, $sMessage = '')
    {
        $sFormedMessage = "Element '$sSelector' was not expected to be checked! $sMessage";
        $this->assertFalse($this->isChecked($sSelector), $sFormedMessage);
    }

    /**
     * Asserting that element value is equal to given value.
     *
     * @param string $sSelector
     * @param string $sExpectedValue
     * @param string $sMessage
     */
    public function assertElementValue($sSelector, $sExpectedValue, $sMessage = '')
    {
        $oElement = $this->getElement($sSelector);
        $sValue = ($oElement->getTagName() == 'textarea') ? $oElement->getText() : $oElement->getValue();
        $sFormedMessage = "Element '$sSelector' does not match expected value! $sMessage";
        $this->assertEquals($sExpectedValue, $sValue, $sFormedMessage);
    }

    /* ------------------------ Mink related functions ---------------------------------- */

    /**
     * @return \Behat\Mink\Session
     */
    public function getMinkSession()
    {
        if (!$this->isMinkSessionStarted()) {
            $this->startMinkSession();
        }

        return self::$minkSession;
    }

    /**
     * @param string $driver
     */
    public function startMinkSession($driver = '')
    {
        $this->stopMinkSession();
        $this->currentMinkDriver = $driver ? $driver : $this->currentMinkDriver;

        $driverInterface = $this->_getMinkDriver($this->currentMinkDriver);
        self::$minkSession = new \Behat\Mink\Session($driverInterface);
        self::$minkSession->start();
    }

    /**
     * Stops Mink session if it is started.
     */
    public static function stopMinkSession()
    {
        if (self::isMinkSessionStarted()) {
            self::$minkSession->stop();
        }
    }

    /**
     * Returns whether mink session was started.
     *
     * @return bool
     */
    public static function isMinkSessionStarted()
    {
        return self::$minkSession && self::$minkSession->isStarted();
    }

    /**
     * @param string $sDriver Driver name
     *
     * @throws Exception
     *
     * @return \Behat\Mink\Driver\DriverInterface
     */
    protected function _getMinkDriver($sDriver)
    {
        $browserName = $this->getTestConfig()->getBrowserName();
        switch ($sDriver) {
            case 'selenium2':
                $oDriver = new \Behat\Mink\Driver\Selenium2Driver($browserName);
                break;
            case 'sahi':
                $oDriver = new \Behat\Mink\Driver\SahiDriver($browserName, $this->_getClient());
                break;
            case 'goutte':
                $aClientOptions = array();
                $oGoutteClient = new \Behat\Mink\Driver\Goutte\Client();
                $oGoutteClient->setClient(new \Guzzle\Http\Client('', $aClientOptions));
                $oDriver = new \Behat\Mink\Driver\GoutteDriver($oGoutteClient);
                break;
            case 'zombie':
                $oDriver = new \Behat\Mink\Driver\ZombieDriver();
                break;
            case 'selenium':
                $client = $this->_getClient();
                $oDriver = new \Behat\Mink\Driver\SeleniumDriver($browserName, shopURL, $client);
                break;
            default:
                throw new Exception('Driver ' . $sDriver . ' was not found!');
                break;
        }

        return $oDriver;
    }

    /**
     * @return \Selenium\Client
     */
    protected function _getClient()
    {
        if (is_null($this->client)) {
            $config = $this->getTestConfig();
            $this->client = new \Selenium\Client($config->getSeleniumServerIp(), $config->getSeleniumServerPort());
        }

        return $this->client;
    }

//----------------------------- Tests BoilerPlate related functions ------------------------------------

    /**
     * Creates a dump of the current database, stored in the file '/tmp/tmp_db_dump'
     * the dump includes the data and sql insert statements.
     *
     * @param string $sTmpPrefix temp file name.
     * @throws Exception on error while dumping.
     * @return null
     */
    public function dumpDB($sTmpPrefix = null)
    {
        if ($this->oTestConfig->shouldRestoreAfterAcceptanceTests()) {
            $oServiceCaller = new oxServiceCaller($this->getTestConfig());
            $oServiceCaller->setParameter('dumpDB', true);
            $oServiceCaller->setParameter('dump-prefix', $sTmpPrefix);
            $oServiceCaller->callService('ShopPreparation', 1);
        }
    }

    /**
     * Checks which tables of the db changed and then restores these tables.
     *
     * Uses dump file '/tmp/tmp_db_dump' for comparison and restoring.
     *
     * @param string $sTmpPrefix temp file name
     * @throws Exception on error while restoring db
     * @return null
     */
    public function restoreDB($sTmpPrefix = null)
    {

        if ($this->oTestConfig->shouldRestoreAfterAcceptanceTests()) {
            $oServiceCaller = new oxServiceCaller($this->getTestConfig());
            $oServiceCaller->setParameter('restoreDB', true);
            $oServiceCaller->setParameter('dump-prefix', $sTmpPrefix);
            $oServiceCaller->callService('ShopPreparation', 1);
        }
    }

    /**
     * Adds some test data to database.
     *
     * @param string $sFilePath
     *
     * @return null
     */
    public function importSql($sFilePath)
    {
        if (filesize($sFilePath)) {
            $oServiceCaller = new oxServiceCaller($this->getTestConfig());
            $oServiceCaller->setParameter('importSql', '@' . $sFilePath);
            $oServiceCaller->callService('ShopPreparation', 1);
        }
    }

    /**
     * executes given sql. for EE version cash is also cleared.
     * @param string $sql sql line.
     */
    public function executeSql($sql)
    {
        oxDb::getDb()->execute($sql);
        if ($this->getTestConfig()->getShopEdition() == 'EE') {
            oxDb::getDb()->execute("delete from oxcache");
        }
    }

    /**
     * Call shop seleniums connector to execute code in shop.
     * @example call to update information to database.
     *
     * @param string $sClass          class name.
     * @param string $sFnc            function name.
     * @param string $sId             id of object.
     * @param array  $aClassParams    params to set to object.
     * @param array  $aFunctionParams params to set to object.
     * @param string $sShopId         object shop id.
     * @param string $sLang           object shop id.
     *
     * @return mixed
     */
    public function callShopSC(
        $sClass,
        $sFnc,
        $sId = null,
        $aClassParams = array(),
        $aFunctionParams = array(),
        $sShopId = null,
        $sLang = 'en'
    ) {
        $oServiceCaller = new oxServiceCaller($this->getTestConfig());
        $oServiceCaller->setParameter('cl', $sClass);
        $oServiceCaller->setParameter('fnc', $sFnc);
        $oServiceCaller->setParameter('oxid', $sId);
        $oServiceCaller->setParameter('lang', $sLang);

        $oServiceCaller->setParameter('classparams', $aClassParams);
        $oServiceCaller->setParameter('functionparams', $aFunctionParams);

        try {
            $mResponse = $oServiceCaller->callService('ShopObjectConstructor', $sShopId);
        } catch (Exception $oException) {
            $this->fail("Exception caught calling ShopObjectConstructor with message: '{$oException->getMessage()}'");
        }

        return $mResponse;
    }

    /**
     * Call shop seleniums connector to execute code in shop.
     * @example call to update information to database.
     *
     * @param string  $sElementTable Name of element table
     * @param integer $sShopId       Subshop id
     * @param integer $sParentShopId Parent subshop id
     * @param integer $sElementId    Element id
     *
     * @return mixed
     */
    public function assignElementToSubShopSC($sElementTable, $sShopId, $sParentShopId = 1, $sElementId = null)
    {
        $oServiceCaller = new oxServiceCaller($this->getTestConfig());
        $oServiceCaller->setParameter('elementtable', $sElementTable);
        $oServiceCaller->setParameter('shopid', $sShopId);
        $oServiceCaller->setParameter('parentshopid', $sParentShopId);
        $oServiceCaller->setParameter('elementid', $sElementId);

        $mResponse = $oServiceCaller->callService('SubShopHandler', $sShopId);

        if (is_string($mResponse) && strpos($mResponse, 'EXCEPTION:') === 0) {
            $this->fail("Exception caught calling ShopObjectConstructor with message: '$mResponse'");
        }

        return $mResponse;
    }

    /**
     * @return oxObjectValidator
     */
    public function getObjectValidator()
    {
        if (!$this->validator) {
            $this->validator = new oxObjectValidator();
        }

        return $this->validator;
    }

    /**
     * Returns data value from file
     *
     * @param $sVarName
     * @param $sFilePath
     * @return string
     */
    public function getArrayValueFromFile($sVarName, $sFilePath)
    {
        $aData = null;
        if (file_exists($sFilePath)) {
            $aData = include $sFilePath;
        }

        return $aData[$sVarName];
    }

//----------------------------- Other functions, PHPUnit fixes, etc ------------------------------------

    /**
     * Return main shop number.
     * To use to form link to main shop and etc.
     *
     * @return string
     */
    public function getShopVersionNumber()
    {
        return '5';
    }

    /**
     * tests if none of php possible errors are displayed into shop frontend page.
     *
     * @return null
     */
    public function checkForErrors()
    {
        $sHTML = $this->getHtmlSource();
        $aErrorTexts = array(
            "Warning: " => "PHP Warning is in the page",
            "ADODB_Exception" => "ADODB Exception is in the page",
            "Fatal error: " => "PHP Fatal error is in the page",
            "Catchable fatal error: " => " Catchable fatal error is in the page",
            "Notice: " => "PHP Notice is in the page",
            "exception '" => "Uncaught exception is in the page",
            "does not exist or is not accessible!" => "Warning about not existing function is in the page ",
            "ERROR: Tran" => "Missing translation for constant (ERROR: Translation for...)",
            "EXCEPTION_" => "Exception - component not found (EXCEPTION_)",
            "oxException" => "Exception is in page"
        );

        foreach ($aErrorTexts as $sError => $sMessage) {
            if (strpos($sHTML, $sError) !== false) {
                $this->fail($sMessage);
            }
        }
    }

    /**
     * Returns clean heading text without any additional info as rss labels and so..
     *
     * @param string $element path to element.
     * @return string
     */
    public function getHeadingText($element)
    {
        $text = $this->getText($element);
        if ($this->isElementPresent($element . "/a")) {
            $search = $this->getText($element . "/a");
            $text = str_replace($search, "", $text);
        }
        return trim($text);
    }

    /**
     * Calls ModuleInstaller Service and activates all given modules in shop before tests are run.
     */
    public function activateModules()
    {
        $testConfig = $this->getTestConfig();
        $modulesToActivate = $testConfig->getModulesToActivate();
        if ($modulesToActivate) {
            $serviceCaller = new oxServiceCaller();
            $serviceCaller->setParameter('modulestoactivate', $modulesToActivate);
            $serviceCaller->callService('ModuleInstaller', 1);
        }
    }

    /**
     * Removes \n signs and it leading spaces from string. keeps only single space in the ends of each row.
     *
     * @param string $sLine not formatted string (with spaces and \n signs).
     * @return string formatted string with single spaces and no \n signs.
     */
    public function clearString($sLine)
    {
        return trim(preg_replace("/[ \t\r\n]+/", ' ', $sLine));
    }

    /**
     * Clears shop cache
     */
    public function clearCache()
    {
        $this->clearTemp();
        $this->clearCookies();
    }

    /**
     * Clears browser cookies, (with _cc file)
     *
     * @return null
     */
    public function clearCookies()
    {
        $testConfig = new oxTestConfig();
        $shopUrl = preg_replace("|(https?://[^:/]*?):[0-9]+|", '$1', $testConfig->getShopUrl());
        $this->open($shopUrl . '/_cc.php');
        if ($this->getHtmlSource() != '<head></head><body></body>') {
            $this->stopMinkSession();
        }

        $this->getTranslator()->setLanguage(1);
    }

    /**
     * Clears shop cache.
     *
     * @return null
     */
    public function clearTemp()
    {
        $oServiceCaller = new oxServiceCaller($this->getTestConfig());
        try {
            $oServiceCaller->callService('ClearCache', 1);
        } catch (Exception $e) {
            $this->fail('Failed to clear cache with message: ' . $e->getMessage());
        }
    }

    /**
     * Logs method loading times
     *
     * @param string $sMethod
     * @param int    $iTime
     */
    public function addToLog($sMethod, $iTime)
    {
        if (!$this->_blEnableLog) {
            return;
        }
        $sLogFile = oxPATH . '/perf_logs.txt';
        if (file_exists($sLogFile)) {
            $aData = unserialize(file_get_contents($sLogFile));
        } else {
            $aData = array();
        }
        if (!$aData[$sMethod]) {
            $aData[$sMethod] = array('time' => 0, 'count' => 0, 'messages' => array());
        }
        $aData[$sMethod]['time'] += intval($iTime * 10000);
        $aData[$sMethod]['count']++;

        file_put_contents($sLogFile, serialize($aData));
    }

    /**
     * If retry count is still not over, reruns the test.
     *
     * @param string $message Failure message to show if retry is not available.
     *
     * @throws oxRetryTestException
     */
    public function retryTest($message = '')
    {
        throw new oxRetryTestException($message);
    }

    /**
     * Clear spaces and new lines as Mink do.
     * @param $sToClear
     * @return mixed
     */
    protected static function _clearString($sToClear)
    {
        $sToClear = preg_replace("/[ \n]+/", " ", $sToClear);
        return $sToClear;
    }

    /**
     * Fix for showing stack trace with phpunit 3.6 and later
     *
     * @param Exception $exception
     *
     * @throws Exception
     */
    protected function onNotSuccessfulTest(Exception $exception)
    {
        if ($this->retryTimesLeft > 0 && $this->shouldRetryTest($exception)) {
            $this->retryTimesLeft--;
            $this->stopMinkSession();
            $this->runBare();
            return;
        }

        if ($this->shouldReformatExceptionMessage($exception)) {
            $exception = $this->formException($exception);
        }

        $this->stopMinkSession();
        throw $exception;
    }

    /**
     * Checks whether test should be retried.
     *
     * @param Exception $e
     *
     * @return bool
     */
    protected function shouldRetryTest(Exception $e)
    {
        $isForcedRetry = $e instanceof oxRetryTestException;
        $isSeleniumServerError = $e instanceof \Selenium\Exception;

        $isServerProblems = false;
        if ($this->isMinkSessionStarted()) {
            $isServerProblems = $this->isInternalServerError() || $this->isServiceUnavailable();
        }

        return $isForcedRetry || $isServerProblems || $isSeleniumServerError;
    }

    /**
     * Checks whether test should be retried.
     *
     * @param Exception $exception
     *
     * @return bool
     */
    protected function shouldReformatExceptionMessage(Exception $exception)
    {
        $isAssertionException = $exception instanceof PHPUnit_Framework_AssertionFailedError;
        $isTestSkipped = $exception instanceof PHPUnit_Framework_SkippedTest
            || $exception instanceof PHPUnit_Framework_IncompleteTest;

        return $isAssertionException && !$isTestSkipped;
    }

    /**
     * @param Exception $exception
     *
     * @return string
     */
    protected function formExceptionMessage($exception)
    {
        $trace = PHPUnit_Util_Filter::getFilteredStacktrace($exception, false);

        $errorMessage = $this->_getScreenShot();
        $errorMessage .= $exception->getMessage();
        $errorMessage .= "\nSelected Frame: '" . $this->getSelectedFrame() . "'";
        $errorMessage .= "\n\n" . $this->_formTrace($trace);

        return $errorMessage;
    }

    /**
     * Take a screenshot and return information about it.
     * Return an empty string if the screenshotPath and screenshotUrl
     * properties are empty.
     * Issue #88.
     *
     * @access protected
     * @return string
     */
    protected function _getScreenShot()
    {
        $sPath = $this->_getScreenShotPath();
        if ($sPath) {
            $sFileName = basename(__FILE__) . '_' . $this->getName(false) . '_' . time() . '.png';

            $this->getScreenShot($sPath . $sFileName);

            return 'Screenshot: ' . $this->getTestConfig()->getScreenShotsUrl() . '/' . $sFileName . "\n";
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    protected function _getScreenShotPath()
    {
        $sPath = $this->getTestConfig()->getScreenShotsPath();
        return $sPath ? rtrim($sPath, '/\\') . DIRECTORY_SEPARATOR : null;
    }

    /**
     * Checks whether any currently opened windows contains internal server error
     *
     * @return bool
     */
    protected function isInternalServerError()
    {
        $sHTML = $this->getHtmlSource();
        if (strpos($sHTML, '500 Internal Server Error') !== false) {
            return true;
        }

        return false;
    }


    /**
     * Checks if currently opened window contains Service unavailable
     *
     * @return bool
     */
    protected function isServiceUnavailable()
    {
        $documentSource = $this->getHtmlSource();

        $result = false;
        if (strpos($documentSource, '503 Service Unavailable') !== false) {
            $result = true;
        }

        return $result;
    }


    /**
     * Forms trace message from given array.
     *
     * @param array $aTrace
     * @return string
     */
    protected function _formTrace($aTrace)
    {
        if (!is_array($aTrace)) {
            return $aTrace;
        }
        $aSkipMethods = array('main', 'runBare', '');
        $sResult = '';
        $aReversedTrace = array_reverse($aTrace);
        foreach ($aReversedTrace as $aCall) {
            if (strpos($aCall['file'], '/usr') === 0 || strpos($aCall['file'], '/tmp') === 0) {
                continue;
            }
            $sResult .= (!in_array($aCall['function'], $aSkipMethods)) ? $this->_parseTraceCall($aCall) : '';
        }
        return $sResult;
    }

    /**
     * Forms readable trace line from given trace call array
     *
     * @param array $aTraceCall
     * @return string
     */
    protected function _parseTraceCall($aTraceCall)
    {
        return sprintf(
            "%s:%s (%s)\n",
            $aTraceCall['file'],
            (isset($aTraceCall['line']) ? $aTraceCall['line'] : '?'),
            $aTraceCall['function']
        );
    }

    /**
     * Forms shop url with given parameters
     *
     * @param array $aParams
     * @param null  $sShopId
     * @return string
     */
    protected function _getShopUrl($aParams = array(), $sShopId = null)
    {
        if ($sShopId && oxSHOPID != 'oxbaseshop') {
            $aParams['shp'] = $sShopId;
        } elseif (isSUBSHOP) {
            $aParams['shp'] = oxSHOPID;
        }

        return shopURL . "index.php?" . http_build_query($aParams);
    }

    /**
     * @param $aPath
     */
    protected function _selectFrameByPath($aPath)
    {
        $this->selectFrame('relative=top');
        foreach ($aPath as $sFrame) {
            if ($sFrame != 'list' || $this->isElementPresent('list')) {
                $this->selectFrame($sFrame);
            }
        }
    }

    /**
     * Returns path of currently running tests suite.
     *
     * @return string
     */
    protected function getSuitePath()
    {
        $class = new ReflectionClass(get_class($this));
        return dirname($class->getFileName());
    }

    /**
     * Newly forms Exception according provided Exception object.
     *
     * @param Exception $exception
     *
     * @return Exception|PHPUnit_Framework_AssertionFailedError
     */
    protected function formException(Exception $exception)
    {
        $exceptionClassName = get_class($exception);

        if ($exception instanceof PHPUnit_Framework_ExpectationFailedException) {
            $exceptionClassName = get_class(new PHPUnit_Framework_AssertionFailedError());
        }

        $newException = new $exceptionClassName(
            $this->formExceptionMessage($exception),
            $exception->getCode()
        );

        return $newException;
    }

    /**
     * Returns database restorer object.
     *
     * @return oxTestModuleLoader
     */
    protected static function _getModuleLoader()
    {
        if (is_null(self::$moduleLoader)) {
            self::$moduleLoader = new oxTestModuleLoader();
        }

        return self::$moduleLoader;
    }

}

/**
 * Backward compatibility, do not use it for new tests.
 * @deprecated use oxAcceptanceTestCase instead
 */
class oxTestCase extends oxAcceptanceTestCase
{
}
