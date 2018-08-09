<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\AssertionFailedError;
use Exception;
use PHPUnit\Framework\TestSuite;

class Printer extends \PHPUnit\TextUI\ResultPrinter
{
    /** @var int */
    private $timeStats;

    /**
     * @inheritdoc
     */
    public function addError(PHPUnit\Framework\Test $test, Exception $e, $time)
    {
        if ($this->verbose) {
            echo "        ERROR: '" . $e->getMessage() . "'\n" . $e->getTraceAsString();
        }
        parent::addError($test, $e, $time);
    }

    /**
     * @inheritdoc
     */
    public function addFailure(PHPUnit\Framework\Test $test, PHPUnit\Framework\AssertionFailedError $e, $time)
    {
        if ($this->verbose) {
            echo "        FAIL: '" . $e->getMessage() . "'\n" . $e->getTraceAsString();
        }
        parent::addFailure($test, $e, $time);
    }

    /**
     * @inheritdoc
     */
    public function endTest(PHPUnit\Framework\Test $test, $time)
    {
        $t = microtime(true) - $this->timeStats['startTime'];
        if ($this->timeStats['min'] > $t) {
            $this->timeStats['min'] = $t;
        }
        if ($this->timeStats['max'] < $t) {
            $this->timeStats['max'] = $t;
            $this->timeStats['slowest'] = $test->getName();
        }
        $this->timeStats['avg'] = ($t + $this->timeStats['avg'] * $this->timeStats['cnt']) / (++$this->timeStats['cnt']);

        parent::endTest($test, $time);
    }

    /**
     * @inheritdoc
     */
    public function endTestSuite(PHPUnit\Framework\TestSuite $suite)
    {
        parent::endTestSuite($suite);

        echo "\ntime stats: min {$this->timeStats['min']}, max {$this->timeStats['max']}, avg {$this->timeStats['avg']}, slowest test: {$this->timeStats['slowest']}|\n";
    }

    /**
     * @inheritdoc
     */
    public function startTestSuite(PHPUnit\Framework\TestSuite $suite)
    {
        echo("\n\n" . $suite->getName() . "\n");

        $this->timeStats = array('cnt' => 0, 'min' => 9999999, 'max' => 0, 'avg' => 0, 'startTime' => 0, 'slowest' => '_ERROR_');

        parent::startTestSuite($suite);
    }

    /**
     * @inheritdoc
     */
    public function startTest(PHPUnit\Framework\Test $test)
    {
        if ($this->verbose) {
            echo "\n        " . $test->getName();
        }

        $this->timeStats['startTime'] = microtime(true);

        parent::startTest($test);
    }
}
