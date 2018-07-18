<?php

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Mink\Driver;

use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Selenium\Client as SeleniumClient;
use Selenium\Exception as SeleniumException;
use Selenium\Locator as SeleniumLocator;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Selenium driver.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class SeleniumDriver extends CoreDriver
{
    const MODIFIER_CTRL  = 'ctrl';
    const MODIFIER_ALT   = 'alt';
    const MODIFIER_SHIFT = 'shift';
    const MODIFIER_META  = 'meta';

    /**
     * Default timeout for Selenium (in milliseconds)
     *
     * @var int
     */
    private $timeout = 60000;

    /**
     * The selenium browser instance
     *
     * @var \Selenium\Browser
     */
    private $browser;

    /**
     * Flag indicating if the browser is started
     *
     * @var bool
     */
    private $started = false;

    /**
     * Instantiates the driver.
     *
     * @param string         $browser Browser name
     * @param string         $baseUrl Base URL for testing
     * @param SeleniumClient $client  The client for getting a browser
     */
    public function __construct($browser, $baseUrl, SeleniumClient $client)
    {
        $this->browser = $client->getBrowser($baseUrl, $browser);
    }

    /**
     * Returns Selenium browser instance.
     *
     * @return \Selenium\Browser
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $this->started = true;
        $this->browser->start();
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        if ($this->started) {
            $this->browser->stop();
        }
        $this->started = false;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->browser->deleteAllVisibleCookies();
    }

    /**
     * {@inheritdoc}
     */
    public function visit($url)
    {
        $this->browser
            ->open($url)
            ->waitForPageToLoad($this->timeout)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUrl()
    {
        return $this->browser->getLocation();
    }

    /**
     * {@inheritdoc}
     */
    public function reload()
    {
        $this->browser
            ->refresh()
            ->waitForPageToLoad($this->timeout)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function forward()
    {
        $this->browser
            ->runScript('history.forward()')
            ->waitForPageToLoad($this->timeout)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function back()
    {
        $this->browser->goBack();
    }

    /**
     * {@inheritdoc}
     */
    public function switchToWindow($name = null)
    {
        $this->browser->selectWindow($name ? $name : 'null');
    }

    /**
     * {@inheritdoc}
     */
    public function switchToIFrame($name = null)
    {
        if ($name) {
            $this->browser->selectFrame('dom=window.frames["'.$name.'"]');
        } else {
            $this->browser->selectFrame('relative=top');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCookie($name, $value = null)
    {
        if (null === $value) {
            $this->browser->deleteCookie($name, 'recurse=true');
        } else {
            $this->browser->createCookie($name.'='.$value, '');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCookie($name)
    {
        if ($this->browser->isCookiePresent($name)) {
            return $this->browser->getCookieByName($name);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->browser->getHtmlSource();
    }

    /**
     * {@inheritdoc}
     */
    public function findElementXpaths($xpath)
    {
        $count = $this->browser->getXpathCount($xpath);

        $elements = array();
        for ($i = 0; $i < $count; $i++) {
            $elements[] = sprintf('(%s)[%d]', $xpath, $i + 1);
        }

        return $elements;
    }

    /**
     * {@inheritdoc}
     */
    public function getTagName($xpath)
    {
        return $this->getDomElement($xpath)->nodeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getText($xpath)
    {
        $result = $this->browser->getText(SeleniumLocator::xpath($xpath));

        return preg_replace("/[ \n]+/", " ", $result);
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml($xpath)
    {
        // cut the tag itself (making innerHTML out of outerHTML)
        return preg_replace('/^\<[^\>]+\>|\<[^\>]+\>$/', '', $this->getOuterHtml($xpath));
    }

    /**
     * {@inheritdoc}
     */
    public function getOuterHtml($xpath)
    {
        $node = $this->getDomElement($xpath);

        if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
            // node parameter was added to the saveHTML() method in PHP 5.3.6
            // @see http://php.net/manual/en/domdocument.savehtml.php
            return $node->ownerDocument->saveHTML($node);
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->appendChild($document->importNode($node, true));

        return rtrim($document->saveHTML());
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($xpath, $name)
    {
        $xpathEscaped = json_encode($xpath);
        $nameEscaped = json_encode((string)$name);

        $script = <<<JS
var node = this.browserbot.locateElementByXPath({$xpathEscaped}, window.document);

JSON.stringify(node.getAttribute({$nameEscaped}))
JS;

        return json_decode($this->browser->getEval($script), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($xpath)
    {
        $xpathEscaped = json_encode($xpath);
        $script = <<<JS
var node = this.browserbot.locateElementByXPath({$xpathEscaped}, window.document),
    tagName = node.tagName.toLowerCase(),
    value = null;
if (tagName === 'input') {
    var type = node.type.toLowerCase();
    if (type === 'checkbox') {
        value = node.checked ? node.value : null;
    } else if (type === 'radio') {
        if (node.checked) {
            value = node.value;
        } else {
            var name = node.getAttribute('name');
            if (name) {
                var formElements = node.form.elements,
                    element;
                for (var i = 0; i < formElements.length; i++) {
                    element = formElements[i];
                    if (element.type.toLowerCase() == 'radio' && element.getAttribute('name') === name && element.checked) {
                        value = element.value;
                        break;
                    }
                }
            }
        }
    } else {
        value = node.value;
    }
} else if (tagName === 'select') {
    if (node.multiple) {
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
        }
    }
} else {
  value = node.value;
}
JSON.stringify(value)
JS;

        return json_decode($this->browser->getEval($script), true);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($xpath, $value)
    {
        $node = $this->getDomElement($xpath);

        if ('select' === $node->tagName) {
            $xpathEscaped = json_encode($xpath);
            $valueEscaped = json_encode($value);

            $script = <<<JS
// Function to triger an event. Cross-browser compliant. See http://stackoverflow.com/a/2490876/135494
var triggerEvent = function (element, eventName) {
    var document = element.ownerDocument;
    var event;
    if (document.createEvent) {
        event = document.createEvent("HTMLEvents");
        event.initEvent(eventName, true, true);
    } else {
        event = document.createEventObject();
        event.eventType = eventName;
    }

    event.eventName = eventName;

    if (document.createEvent) {
        element.dispatchEvent(event);
    } else {
        element.fireEvent("on" + event.eventType, event);
    }
}

var node = this.browserbot.locateElementByXPath({$xpathEscaped}, window.document);
var hasChanged = false;
if (node.multiple) {
    var i, option, l = node.options.length;
    var values = {$valueEscaped};
    for (i = 0; i < l; i++) {
        option = node.options[i];
        if (option.selected && -1 === values.indexOf(option.value)) {
            option.selected = false;
            hasChanged = true;
        } else if (!option.selected && -1 !== values.indexOf(option.value)) {
            option.selected = true;
            hasChanged = true;
        }
    }
    if (hasChanged) {
        triggerEvent(node, 'change');
    }
} else {
    var i, option, l = node.options.length;
    for (i = 0; i < l; i++) {
        option = node.options[i];
        if (option.value == {$valueEscaped}) {
            if (!option.selected) {
                option.selected = true;
                triggerEvent(node, 'change');
            }
            break;
        }
    }
}
JS;

            $this->browser->getEval($script);

            return;
        }

        if ('input' === $node->tagName) {
            $type = $node->hasAttribute('type') ? strtolower($node->getAttribute('type')) : 'text';

            if ('checkbox' === $type) {
                if ($value) {
                    $this->check($xpath);
                } else {
                    $this->uncheck($xpath);
                }

                return;
            }

            if ('radio' ===  $type) {
                $this->selectOption($xpath, $value);

                return;
            }
        }

        $this->browser->type(SeleniumLocator::xpath($xpath), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function check($xpath)
    {
        $this->browser->check(SeleniumLocator::xpath($xpath));
    }

    /**
     * {@inheritdoc}
     */
    public function uncheck($xpath)
    {
        $this->browser->uncheck(SeleniumLocator::xpath($xpath));
    }

    /**
     * {@inheritdoc}
     */
    public function selectOption($xpath, $value, $multiple = false)
    {
        $xpathEscaped = json_encode($xpath);
        $valueEscaped = json_encode($value);
        $multipleJS   = json_encode((bool) $multiple);

        $script = <<<JS
// Function to triger an event. Cross-browser compliant. See http://stackoverflow.com/a/2490876/135494
var triggerEvent = function (element, eventName) {
    var document = element.ownerDocument;
    var event;
    if (document.createEvent) {
        event = document.createEvent("HTMLEvents");
        event.initEvent(eventName, true, true);
    } else {
        event = document.createEventObject();
        event.eventType = eventName;
    }

    event.eventName = eventName;

    if (document.createEvent) {
        element.dispatchEvent(event);
    } else {
        element.fireEvent("on" + event.eventType, event);
    }
}

var node = this.browserbot.locateElementByXPath({$xpathEscaped}, window.document);
if (node.tagName == 'SELECT') {
    var i, option, l = node.options.length;
    for (i = 0; i < l; i++) {
        option = node.options[i];
        if (option.value == {$valueEscaped}) {
            option.selected = true;
        } else if (node.multiple && !$multipleJS) {
            option.selected = false;
        }
    }
    triggerEvent(node, 'change');
} else if (node.tagName != 'INPUT' || node.type.toLowerCase() !== 'radio') {
    throw new Error('The element is not a radio group or select.');
} else if (node.value == {$valueEscaped}) {
    if (!node.checked) {
        node.checked = true;
        triggerEvent(node, 'change');
    }
} else {
    var formElements = node.form.elements,
        name = node.getAttribute('name'),
        found = false,
        element;

    if (!name) {
        throw new Error('The radio button does not have the value "' + value + '"');
    }

    for (var i = 0; i < formElements.length; i++) {
        element = formElements[i];
        if (element.tagName === 'INPUT' && element.type.toLowerCase() == 'radio' && element.name === name) {
            if ({$valueEscaped} === element.value) {
                found = true;
                if (!element.checked) {
                    element.checked = true;
                    triggerEvent(element, 'change');
                }
                break;
            }
        }
    }

    if (!found) {
        throw new Error('The radio group "' + name + '" does not have an option "' + value + '"');
    }
}
JS;

        $this->browser->getEval($script);
    }

    /**
     * {@inheritdoc}
     */
    public function isSelected($xpath)
    {
        $xpathEscaped = json_encode($xpath);

        $script = <<<JS
var node = this.browserbot.locateElementByXPath({$xpathEscaped}, window.document);
node.selected
JS;

        return $this->browser->getEval($script) == 'true';
    }

    /**
     * {@inheritdoc}
     */
    public function click($xpath)
    {
        $this->browser->click(SeleniumLocator::xpath($xpath));
        $readyState = $this->browser->getEval('window.document.readyState');

        if ($readyState == 'loading' || $readyState == 'interactive') {
            $this->browser->waitForPageToLoad($this->timeout);
        }

        $this->getCurrentUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function isChecked($xpath)
    {
        return $this->browser->isChecked(SeleniumLocator::xpath($xpath));
    }

    /**
     * {@inheritdoc}
     */
    public function attachFile($xpath, $path)
    {
        $this->browser->attachFile(SeleniumLocator::xpath($xpath), 'file://'.$path);
    }

    /**
     * {@inheritdoc}
     */
    public function getScreenshot()
    {
        return base64_decode($this->browser->captureScreenshotToString());
    }

    /**
     * {@inheritdoc}
     */
    public function doubleClick($xpath)
    {
        $this->browser->doubleClick(SeleniumLocator::xpath($xpath));
    }

    /**
     * {@inheritdoc}
     */
    public function mouseOver($xpath)
    {
        $this->browser->mouseOver(SeleniumLocator::xpath($xpath));
    }

    /**
     * {@inheritdoc}
     */
    public function keyPress($xpath, $char, $modifier = null)
    {
        $this->keyDownModifier($modifier);
        $this->browser->keyPress(SeleniumLocator::xpath($xpath), $char);
        $this->keyUpModifier($modifier);
    }

    /**
     * {@inheritdoc}
     */
    public function keyDown($xpath, $char, $modifier = null)
    {
        $this->keyDownModifier($modifier);
        $this->browser->keyDown(SeleniumLocator::xpath($xpath), $char);
        $this->keyUpModifier($modifier);
    }

    /**
     * {@inheritdoc}
     */
    public function keyUp($xpath, $char, $modifier = null)
    {
        $this->keyDownModifier($modifier);
        $this->browser->keyUp(SeleniumLocator::xpath($xpath), $char);
        $this->keyUpModifier($modifier);
    }

    /**
     * {@inheritdoc}
     */
    public function executeScript($script)
    {
        if (preg_match('/^function[\s\(]/', $script)) {
            $script = preg_replace('/;$/', '', $script);
            $script = '(' . $script . ')';
        }

        $this->browser->runScript($script);
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateScript($script)
    {
        $script = preg_replace('/^return\s+/', '', $script);
        $script = preg_replace('/;$/', '', $script);

        if (preg_match('/^function[\s\(]/', $script)) {
            $script = '(' . $script . ')';
        }

        $script = sprintf('JSON.stringify(%s)', $script);

        return json_decode($this->browser->getEval($script), true);
    }

    /**
     * {@inheritdoc}
     */
    public function wait($timeout, $condition)
    {
        $condition = 'with (selenium.browserbot.getCurrentWindow()) { '."\n".$condition."\n }";

        try {
            $this->browser->waitForCondition($condition, $timeout);
        } catch (SeleniumException $e) {
            // ignore error
        }

        return $this->browser->getEval($condition) == 'true';
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible($xpath)
    {
        return $this->browser->isVisible(SeleniumLocator::xpath($xpath));
    }

    /**
     * {@inheritdoc}
     */
    public function dragTo($sourceXpath, $destinationXpath)
    {
        $sourceLocator = SeleniumLocator::xpath($sourceXpath);
        $destinationLocator = SeleniumLocator::xpath($destinationXpath);

        $this->browser->mouseMoveAt($sourceLocator, '0,0');
        $this->browser->mouseDownAt($sourceLocator, '0,0');
        $this->browser->mouseMoveAt($destinationLocator, '0,0');
        $this->browser->mouseUpAt($destinationLocator, '0,0');
    }

    /**
     * {@inheritdoc}
     */
    public function maximizeWindow($name = null)
    {
        if (null !== $name) {
            throw new UnsupportedDriverActionException('Maximizing a non-default window is not supported by %s', $this);
        }

        $this->browser->windowMaximize();
    }

    /**
     * Returns a crawler instance for the current source.
     *
     * @return Crawler
     */
    private function getCrawler()
    {
        $content = '<html>'.$this->browser->getHtmlSource().'</html>';

        $contentType = null;
        // get content-type from meta tag
        if (preg_match('/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9]+)/i', $content, $matches)) {
            $contentType = 'text/html;charset='.$matches[1];
        }

        $crawler = new Crawler();
        $crawler->addContent($content, $contentType);

        return $crawler;
    }

    /**
     * Returns a DOM element for the given XPath
     *
     * @param string $xpath
     *
     * @return \DOMElement
     *
     * @throws DriverException when the XPath does not match
     */
    private function getDomElement($xpath)
    {
        $crawler = $this->getCrawler()->filterXPath($xpath);

        if (!count($crawler)) {
            throw new DriverException(sprintf('There is no element matching XPath "%s"', $xpath));
        }

        $crawler->rewind();

        return $crawler->current();
    }

    /**
     * Handles the key down of a keyboard modifier
     *
     * @param string $modifier The modifier to handle (see self::MODIFIER_*)
     */
    protected function keyDownModifier($modifier)
    {
        switch ($modifier) {
            case self::MODIFIER_CTRL:
                throw new UnsupportedDriverActionException('Ctrl key is not supported by %s', $this);
            case self::MODIFIER_ALT:
                $this->browser->altKeyDown();
                break;
            case self::MODIFIER_SHIFT:
                $this->browser->shiftKeyDown();
                break;
            case self::MODIFIER_META:
                $this->browser->metaKeyDown();
                break;
        }
    }

    /**
     * Handles the key up of a keyboard modifier
     *
     * @param string $modifier The modifier to handle (see self::MODIFIER_*)
     */
    protected function keyUpModifier($modifier)
    {
        switch ($modifier) {
            case self::MODIFIER_CTRL:
                throw new UnsupportedDriverActionException('Ctrl key is not supported by %s', $this);
            case self::MODIFIER_ALT:
                $this->browser->altKeyUp();
                break;
            case self::MODIFIER_SHIFT:
                $this->browser->shiftKeyUp();
                break;
            case self::MODIFIER_META:
                $this->browser->metaKeyUp();
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm($xpath)
    {
        $this->browser->submit(SeleniumLocator::xpath($xpath));
    }
}
