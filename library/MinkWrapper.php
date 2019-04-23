<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementException;
use Exception;
use InvalidArgumentException;

abstract class MinkWrapper extends BaseTestCase
{
    /** @var int How much time to wait for pages to load. Wait time is multiplied by this value. */
    protected $_iWaitTimeMultiplier = 1;

    /** @var \Selenium\Client Selenium client. Used only with selenium driver. */
    protected $client = null;

    /** @var \Behat\Mink\Session Mink session */
    protected static $minkSession = null;

    /** @var string Currently used mink driver. */
    protected $currentMinkDriver = '';

    /** @var string Used to follow which window is currently selected by driver. */
    protected $selectedWindow = null;

    /** @var string Used to follow which frame is currently selected by driver. */
    protected $selectedFrame = 'relative=top';

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
     * Starts Mink session.
     * Currently supported drivers: selenium.
     *
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
            case 'selenium':
                $client = $this->_getClient();
                $oDriver = new \Behat\Mink\Driver\SeleniumDriver($browserName, shopURL, $client);
                break;
            default:
                throw new Exception('Driver ' . $sDriver . ' not supported any more!');
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

    /**
     * Opens url in browser
     *
     * @param $sUrl
     */
    public function open($sUrl)
    {
        try {
            $this->getMinkSession()->visit($sUrl);
        } catch (\Selenium\Exception $exception) {
            sleep(1);
            $this->getMinkSession()->visit($sUrl);
        }
    }

    /**
     * Selects window
     *
     * @param string $sId
     *
     */
    public function selectWindow($sId)
    {
        $this->getMinkSession()->getDriver()->switchToWindow($sId);
        $this->selectedWindow = $sId;
        if (is_null($sId)) {
            $this->selectedFrame = 'relative=top';
        }
    }

    /**
     * Returns selected window id, null if main window selected
     *
     * @return string
     */
    public function getSelectedWindow()
    {
        return $this->selectedWindow;
    }

    /**
     * Selects frame by name
     *
     * @param string $sFrame
     *
     */
    public function selectFrame($sFrame)
    {
        $this->getMinkSession()->getDriver()->switchToIFrame($sFrame);
        $this->selectedFrame = $sFrame;
    }

    /**
     * Returns frame by name
     *
     * @return string
     */
    public function getSelectedFrame()
    {
        return $this->selectedFrame;
    }

    /**
     * Returns page title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getMinkSession()->getDriver()->getBrowser()->getTitle();
    }

    /**
     * Maximizes browser window
     */
    public function windowMaximize()
    {
        $this->getMinkSession()->getDriver()->getBrowser()->windowMaximize();
    }

    /**
     * @param $sUrl
     * @param $sId
     */
    public function openWindow($sUrl, $sId)
    {
        $this->getMinkSession()->getDriver()->getBrowser()->openWindow($sUrl, $sId);
    }

    /**
     * Goes back
     */
    public function goBack()
    {
        $this->getMinkSession()->back();
    }

    /**
     * Goes forward
     */
    public function goForward()
    {
        $this->getMinkSession()->forward();
    }

    /**
     * Clicks on element
     *
     * @param $sSelector
     */
    public function click($sSelector)
    {
        try {
            $this->getElementLazy($sSelector)->click();
        } catch (ElementException $e) {
            sleep(1);
            $this->getElementLazy($sSelector)->click();
        }
    }

    /**
     * Types text to given element
     *
     * @param $sSelector
     * @param $sText
     */
    public function type($sSelector, $sText)
    {
        try {
            $this->getElementLazy($sSelector)->setValue($sText);
        } catch (ElementException $e) {
            sleep(1);
            $this->getElementLazy($sSelector)->setValue($sText);
        }
    }

    /**
     * Selects select element option
     *
     * @param $sSelector
     * @param $sOptionSelector
     */
    public function select($sSelector, $sOptionSelector)
    {
        $oSelectorsHandler = $this->getMinkSession()->getSelectorsHandler();
        $oSelect = null;

        if (strpos($sSelector, '/') === false) {
            $page = $this->getMinkSession()->getPage();
            $sParsedSelector = $oSelectorsHandler->xpathLiteral($sSelector);
            $oSelect = $page->find('named', array('select', $sParsedSelector));
        }

        if (is_null($oSelect)) {
            $oSelect = $this->getElementLazy($sSelector);
        }

        if (strpos($sOptionSelector, 'index=') === 0) {
            $iIndex = str_replace('index=', '', $sOptionSelector);
            $sOptionSelector = $this->_getSelectOptionByIndex($oSelect, $iIndex);
        } else {
            $sOptionSelector = str_replace(array('label=', 'value='), '', $sOptionSelector);
        }

        if (is_null($oSelect)) {
            $this->fail("Select '$sSelector' was not found!");
        }

        $oOptions = $oSelect->findAll('named', array('option', $oSelectorsHandler->xpathLiteral($sOptionSelector)));

        $oOption = $this->_getExactMatch($oOptions, $sOptionSelector);

        if (is_null($oOption)) {
            $this->fail("Option '$sOptionSelector' was not found in '$sSelector' select ");
        }

        $this->getMinkSession()->getDriver()->selectOption(
            $oSelect->getXpath(), $oOption->getValue(), false
        );

        $this->fireEvent($sSelector, 'change');
    }

    /**
     * Adds selection
     *
     * @param string $sSelector
     * @param string $sOptionSelector
     */
    public function addSelection($sSelector, $sOptionSelector)
    {
        $sOptionSelector = str_replace('label=', '', $sOptionSelector);
        $this->getElementLazy($sSelector)->selectOption($sOptionSelector, true);
    }

    /**
     * Check checkbox
     *
     * @param string $sSelector
     */
    public function check($sSelector)
    {
        $this->getElementLazy($sSelector)->check();
    }

    /**
     * Uncheck checkbox
     *
     * @param string $sSelector
     */
    public function uncheck($sSelector)
    {
        $this->getElementLazy($sSelector)->uncheck();
    }

    /**
     * @param string $sSelector
     * @return bool
     */
    public function isChecked($sSelector)
    {
        return $this->getElementLazy($sSelector)->isChecked();
    }

    /**
     * Execute keyUp action on element
     *
     * @param string $sSelector
     * @param string $sChar
     */
    public function keyUp($sSelector, $sChar)
    {
        $this->getElementLazy($sSelector)->keyUp($sChar);
    }

    /**
     * Execute keyDown action on element
     *
     * @param string $sSelector
     * @param string $sChar
     */
    public function keyDown($sSelector, $sChar)
    {
        $this->getElementLazy($sSelector)->keyDown($sChar);
    }

    /**
     * Execute keyPress action on element
     *
     * @param string $sSelector
     * @param string $sChar
     */
    public function keyPress($sSelector, $sChar)
    {
        $this->getElementLazy($sSelector)->keyPress($sChar);
    }

    /**
     * @param string $sSelector
     */
    public function mouseDown($sSelector)
    {
        $this->fireEvent($sSelector, 'mousedown');
    }

    /**
     * @param string $sSelector
     */
    public function mouseOver($sSelector)
    {
        $this->fireEvent($sSelector, 'mouseover');
    }

    /**
     * Drags element to container
     *
     * @param string $sSelector
     * @param string $sContainer
     */
    public function dragAndDropToObject($sSelector, $sContainer)
    {
        $oElement = $this->getElementLazy($sSelector);
        $oContainer = $this->getElementLazy($sContainer);

        $oElement->dragTo($oContainer);
    }

    /**
     * Checks if given text is present on page
     *
     * @param string $sText text to be searched
     * @return bool
     */
    public function isTextPresent($sText)
    {
        $sHTML = $this->getMinkSession()->getPage()->getText();
        return (stripos($sHTML, $sText) !== false);
    }

    /**
     * Checks whether given element is present on page
     *
     * @param string $sSelector
     * @return bool
     */
    public function isElementPresent($sSelector)
    {
        return $this->getElement($sSelector, false) ? true : false;
    }

    /**
     * Checks if element is visible. If element is not found, waits for it to appear and checks again.
     *
     * @param string $sSelector
     * @return bool
     */
    public function isVisible($sSelector)
    {
        $element = $this->getElement($sSelector, false);
        return $element && $element->isVisible();
    }

    /**
     * Checks whether element is editable
     *
     * @param string $sSelector
     * @return mixed
     */
    public function isEditable($sSelector)
    {
        return $this->getMinkSession()->getDriver()->getBrowser()->isEditable($sSelector);
    }

    /**
     * Overrides original method - waits for element before checking for text
     *
     * @param string $sSelector text to be searched
     *
     * @return string
     */
    public function getText($sSelector)
    {
        $oElement = $this->getElementLazy($sSelector);
        try {
            $sText = $oElement->getText();
        } catch (Exception $e) {
            sleep(1);
            $sText = $oElement->getText();
        }
        return $sText;
    }

    /**
     * Returns element's value
     *
     * @param string $sSelector
     *
     * @return mixed|string
     */
    public function getValue($sSelector)
    {
        $element = $this->getElementLazy($sSelector);
        $mValue = $this->_getValue($element->getXpath());

        try {
            $sType = $element->getAttribute('type');
        } catch (InvalidArgumentException $e) {
            sleep(1);
            $sType = $element->getAttribute('type');
        }
        if ($sType == 'checkbox') {
            $mValue = $mValue ? 'on' : 'off';
        }

        return trim($mValue);
    }

    /**
     * Returns selected option label
     *
     * @param string $sSelector
     *
     * @return null|string
     */
    public function getSelectedLabel($sSelector)
    {
        if (strpos($sSelector, '/') === false) {
            $oSelectorsHandler = $this->getMinkSession()->getSelectorsHandler();
            $page = $this->getMinkSession()->getPage();

            $sParsedSelector = $oSelectorsHandler->xpathLiteral($sSelector);

            $oSelect = $page->find('named', array('select', $sParsedSelector));

            if (is_null($oSelect)) {
                $this->fail("Element '$sSelector' was not found! ");
            }
        } else {
            $oSelect = $this->getElementLazy($sSelector);
        }

        $aOptions = $oSelect->findAll('xpath', '//option[@selected]');

        if (empty($oOptions)) {
            $value = $this->_getValue($oSelect->getXpath());
            $value = $this->getMinkSession()->getSelectorsHandler()->xpathLiteral($value);
            $aOptions = $oSelect->findAll('xpath', '//option[@value=' . $value . ']');
        }
        $oOption = !empty($aOptions) ? array_pop($aOptions) : $oSelect->find('xpath', 'option');

        return $oOption ? $oOption->getText() : '';
    }

    /**
     * Returns selected option label
     *
     * @param string $sSelector
     *
     * @return null|string
     */
    public function getSelectedIndex($sSelector)
    {
        $oSelect = $this->getElementLazy($sSelector);
        $sValue = $oSelect->getValue();
        $oOptions = $oSelect->findAll('css', "option");
        foreach ($oOptions as $iKey => $oOption) {
            if ($oOption->getValue() == $sValue) {
                return $iKey;
            }
        }
        return $oSelect->getText();
    }

    /**
     * Confirms alert confirmation
     */
    public function getConfirmation()
    {
        $this->getMinkSession()->getDriver()->getBrowser()->getConfirmation();
    }

    /**
     * Closes browser window, mainly used for closing popups
     */
    public function close()
    {
        $this->getMinkSession()->getDriver()->getBrowser()->close();
        $this->getMinkSession()->getDriver()->switchToWindow(null);
    }

    /**
     * Returns page html source
     *
     * @return null|string
     */
    public function getHtmlSource()
    {
        try {
            $sSource = $this->getMinkSession()->getPage()->getContent();
        } catch (Exception $e) {
            sleep(1);
            $sSource = $this->getMinkSession()->getPage()->getContent();
        }
        return $sSource;
    }

    /**
     * Waits for PopUp window to appear
     */
    public function waitForPopUp()
    {
    }

    /**
     * Returns count of all elements which can be found by xPath.
     *
     * @param string $sSelector
     *
     * @return int
     */
    public function getXpathCount($sSelector)
    {
        $page = $this->getMinkSession()->getPage();

        return count($page->findAll('xpath', $sSelector));
    }

    /**
     * Returns element
     *
     * @param string $sSelector
     * @param bool   $blFailOnError
     *
     * @return NodeElement|null
     */
    public function getElement($sSelector, $blFailOnError = true)
    {
        $sSelector = trim($sSelector);

        try {
            $oElement = $this->_getElement($sSelector);
        } catch (Exception $e) {
            $oElement = $this->_getElement($sSelector);
        }

        if ($blFailOnError && is_null($oElement)) {
            $this->fail("Element '$sSelector' was not found! ");
        }

        return $oElement;
    }

    /**
     * Returns element. If element is not found, tries to wait for it.
     *
     * @param           $selector
     * @param bool|true $failOnError
     * @param int       $waitTime
     *
     * @return NodeElement|null
     */
    public function getElementLazy($selector, $failOnError = true, $waitTime = 10)
    {
        $element = $this->getElement($selector, false);
        while (!$element && $waitTime > 0) {
            $element = $this->getElement($selector, false);
            $waitTime -= 0.5;
            usleep(500000);
        }

        return $element ?: $this->getElement($selector, $failOnError);
    }

    /**
     * Get attribute from selector with attribute
     *
     * @param string $sSelectorWithAttribute
     *
     * @return mixed|null
     */
    public function getAttribute($sSelectorWithAttribute)
    {
        $mAttribute = null;

        $sSelectorAttributeSeparator = '@';
        $iSeparatorPosition = strrpos($sSelectorWithAttribute, $sSelectorAttributeSeparator);
        if ($iSeparatorPosition !== false) {
            $sSelector = $this->_getSelectorWithoutAttribute($sSelectorWithAttribute, $iSeparatorPosition);
            $sAttributeName = $this->_getAttributeWithoutSelector($sSelectorWithAttribute, $iSeparatorPosition);

            $oElement = $this->getElementLazy($sSelector);
            $mAttribute = $oElement->getAttribute($sAttributeName);
        }

        return $mAttribute;
    }

    /**
     * Call event on element.
     *
     * @param string $sSelector
     * @param string $sEvent
     */
    public function fireEvent($sSelector, $sEvent)
    {
        $this->getMinkSession()->getDriver()->getBrowser()->fireEvent($sSelector, $sEvent);
    }

    /**
     * Waits for page to load. Can make additional check if page is still loading (though not always works).
     *
     * @param int  $iTimeout
     * @param bool $blCheckIfLoading
     *
     * @return null|void
     */
    public function waitForPageToLoad($iTimeout = 10000, $blCheckIfLoading = false)
    {
        $readyState = $blCheckIfLoading ? $this->getMinkSession()->getDriver()->getBrowser()->getEval('window.document.readyState') : 'loading';

        if ($readyState == 'loading' || $readyState == 'interactive') {
            $this->getMinkSession()->getDriver()->getBrowser()->waitForPageToLoad($iTimeout * $this->_iWaitTimeMultiplier);
        }
    }

    /**
     * Waits for jQuery to finish. Includes waiting for ajax requests or animations to finish.
     *
     * @param int $iTimeout
     */
    public function waitForJQueryToFinish($iTimeout = 10000)
    {
        $this->getMinkSession()->wait($iTimeout * $this->_iWaitTimeMultiplier,
            "(typeof jQuery !== 'undefined' && 0 === jQuery.active && 0 === jQuery(':animated').length)"
        );
    }

    /**
     * Returns array with all open windows.
     *
     * @return array
     */
    public function getAllWindowNames()
    {
        return $this->getMinkSession()->getDriver()->getBrowser()->getAllWindowNames();
    }


    /**
     * Waits for frame to load by frame name
     *
     * @param string $sFrame         frame name
     * @param int    $iTimeout       time to wait for frame
     * @param bool   $blIgnoreResult Ignores if frame does not load
     *
     * @throws Exception
     */
    public function waitForFrameToLoad($sFrame, $iTimeout = 10000, $blIgnoreResult = true)
    {
        try {
            $this->getMinkSession()->getDriver()->getBrowser()->waitForFrameToLoad($sFrame,
                $iTimeout * $this->_iWaitTimeMultiplier);
        } catch (Exception $e) {
            if (!$blIgnoreResult) {
                throw $e;
            }
        }
    }

    /**
     * Returns script result
     *
     * @param string $sScript
     * @return string
     */
    public function getEval($sScript)
    {
        return $this->getMinkSession()->getDriver()->getBrowser()->getEval($sScript);
    }

    /**
     * Types value to locator element.
     *
     * @param string $locator
     * @param string $value
     *
     * @return mixed
     */
    public function typeKeys($locator, $value)
    {
        try {
            return $this->getMinkSession()->getDriver()->getBrowser()->typeKeys($locator, $value);
        } catch (\Selenium\Exception $e) {
            sleep(1);
            return $this->getMinkSession()->getDriver()->getBrowser()->typeKeys($locator, $value);
        }
    }

    /**
     * Captures screen shot to given file.
     *
     * @param string $sFileName
     *
     * @return string
     */
    public function getScreenShot($sFileName)
    {
        $oDriver = $this->getMinkSession()->getDriver();
        if ($oDriver instanceof \Behat\Mink\Driver\SeleniumDriver) {
            return $this->getMinkSession()->getDriver()->getBrowser()->captureEntirePageScreenshot($sFileName, "");
        }

        return '';
    }

    /**
     * Call getCurrentUrl()
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->getMinkSession()->getDriver()->getCurrentUrl();
    }

    /**
     * @param string $sSelector
     *
     * @return NodeElement|mixed|null
     */
    protected function _getElement($sSelector)
    {
        if (strpos($sSelector, 'link=') === 0) {
            return $this->_getElementByLink($sSelector);
        }
        if (strpos($sSelector, 'css=') === 0) {
            return $this->_getElementByCss($sSelector);
        }
        if (strpos($sSelector, '/') === false) {
            return $this->_getElementByIdOrName($sSelector);
        }

        return $this->getMinkSession()->getPage()->find('xpath', $sSelector);
    }

    /**
     * Returns element by given id or name
     *
     * @param string $sSelector
     *
     * @return NodeElement|null
     */
    protected function _getElementByIdOrName($sSelector)
    {
        $sSelector = str_replace(array('name=', 'id='), array('', ''), $sSelector);

        if (strpos($sSelector, '.') || strpos($sSelector, '[')) {
            $oElement = $this->_getElementByIdOrNameXpath($sSelector);
        } else {
            $oElement = $this->_getElementByIdOrNameCSS($sSelector);
        }

        return $oElement;
    }

    /**
     * Returns element by given link
     *
     * @param string $sSelector
     *
     * @return mixed
     */
    protected function _getElementByLink($sSelector)
    {
        $sSelector = str_replace('link=', '', $sSelector);

        $sParsedSelector = $this->getMinkSession()->getSelectorsHandler()->xpathLiteral($sSelector);
        $oElements = $this->getMinkSession()->getPage()->findAll('named', array('link', $sParsedSelector));

        if (empty($oElements)) {
            $aSelectorParts = explode(' ', $sSelector);
            $aSelectorParts = array_map(array($this->getMinkSession()->getSelectorsHandler(), 'xpathLiteral'),
                $aSelectorParts);
            $sFormedSelector = "//a[contains(.," . implode(") and contains(.,", $aSelectorParts) . ")]";
            $oElements = $this->getMinkSession()->getPage()->findAll('xpath', $sFormedSelector);
        }

        return $this->_getExactMatch($oElements, $sSelector);
    }

    /**
     * @param string $sSelector
     *
     * @return NodeElement|null
     */
    protected function _getElementByCss($sSelector)
    {
        $sSelector = str_replace('css=', '', $sSelector);
        $oElement = $this->getMinkSession()->getPage()->find('css', $sSelector);
        return $oElement;
    }

    /**
     * @param string $sSelector
     *
     * @return NodeElement|null
     */
    protected function _getElementByIdOrNameCSS($sSelector)
    {
        $oElement = $this->getMinkSession()->getPage()->find('css', "#" . $sSelector . ",*[name='$sSelector']");
        return $oElement;
    }

    /**
     * @param string $sSelector
     *
     * @return NodeElement|null
     */
    protected function _getElementByIdOrNameXpath($sSelector)
    {
        $sSelector = $this->getMinkSession()->getSelectorsHandler()->xpathLiteral($sSelector);
        return $this->getMinkSession()->getPage()->find('xpath', "//*[@id=$sSelector or @name=$sSelector]");
    }

    /**
     * @param string $sSelectorWithAttribute
     * @param int    $iSeparatorPosition
     *
     * @return string
     */
    protected function _getSelectorWithoutAttribute($sSelectorWithAttribute, $iSeparatorPosition)
    {
        $sSelector = substr($sSelectorWithAttribute, 0, $iSeparatorPosition);

        if (substr($sSelector, -1) == '/') {
            $sSelector = substr($sSelector, 0, -1);
        }

        return $sSelector;
    }

    /**
     * @param string $sSelectorWithAttribute
     * @param int    $iSeparatorPosition
     *
     * @return string
     */
    protected function _getAttributeWithoutSelector($sSelectorWithAttribute, $iSeparatorPosition)
    {
        $sAttributeName = substr($sSelectorWithAttribute, $iSeparatorPosition + 1);
        return $sAttributeName;
    }

    /**
     * @param NodeElement $oSelect
     * @param int         $iIndex
     *
     * @return string
     */
    protected function _getSelectOptionByIndex($oSelect, $iIndex)
    {
        $oOptions = $oSelect->findAll('css', "option");
        foreach ($oOptions as $iKey => $oOption) {
            /** @var \Behat\Mink\Element\NodeElement $oOption  */
            if ($iIndex == $iKey) {
                return $oOption->getValue();
            }
        }

        return $oOption ? $oOption->getValue() : "";
    }

    /**
     * @param array[NodeElement] $aElements
     * @param string             $sValue
     *
     * @return NodeElement|null
     */
    protected function _getExactMatch($aElements, $sValue)
    {
        foreach ($aElements as $oElement) {
            /** @var NodeElement $oElement */
            if (strcasecmp($oElement->getValue(), $sValue) == 0 || strcasecmp($oElement->getText(), $sValue) == 0) {
                return $oElement;
            }
        }

        return null;
    }

    /**
     * @param string $xpath
     *
     * @return mixed
     */
    public function _getValue($xpath)
    {
        $xpathEscaped = json_encode($xpath);
        $script = <<<JS
var node = this.browserbot.locateElementByXPath({$xpathEscaped}, window.document),
tagName = node.tagName.toLowerCase(),
value = null;
if (tagName == 'input' || tagName == 'textarea') {
var type = node.getAttribute('type');
if (type == 'checkbox') {
value = node.checked;
} else if (type == 'radio') {
var name = node.getAttribute('name');
if (name) {
var fields = window.document.getElementsByName(name),
i, l = fields.length;
for (i = 0; i < l; i++) {
var field = fields.item(i);
if (field.checked) {
value = field.value;
break;
}
}
}
} else {
value = node.value;
}
} else if (tagName == 'select') {
if (node.getAttribute('multiple')) {
value = [];
for (var i = 0; i < node.options.length; i++) {
if (node.options[i].selected) {
value.push(node.options[i].value);
}
}
} else {
var idx = node.selectedIndex;
if (idx >= 0) {
value = node.options.item(idx).value;
} else {
value = null;
}
}
} else {
value = node.getAttribute('value');
}
JSON.stringify(value)
JS;
        $sResult = json_decode($this->getMinkSession()->getDriver()->getBrowser()->getEval($script));

        return preg_replace("/[ \n]+/", " ", $sResult);
    }
}
