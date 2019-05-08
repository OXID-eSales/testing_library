<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use Exception;

class Printer extends \PHPUnit\TextUI\ResultPrinter
{
    /** @var int */
    private $timeStats;

    /**
     * @param string $buffer
     */
    public function write($buffer)
    {
        if ((PHP_SAPI == 'cli')) {
            \fwrite(STDOUT, $buffer);
        } elseif ($this->out) {
            \fwrite($this->out, $buffer);
        } else {
            if (PHP_SAPI != 'cli' && PHP_SAPI != 'phpdbg') {
                $buffer = \htmlspecialchars($buffer, ENT_SUBSTITUTE);
            }

            print $buffer;
        }
        if ($this->autoFlush) {
            $this->incrementalFlush();
        }
    }

    /**
     * @inheritdoc
     */
    public function addError(\PHPUnit\Framework\Test $test, Exception $e, $time)
    {
        if ($this->verbose) {
            $this->write("        ERROR: '" . $e->getMessage() . "'\n" . $e->getTraceAsString());
        }
        parent::addError($test, $e, $time);
    }

    /**
     * @inheritdoc
     */
    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, $time)
    {
        if ($this->verbose) {
            $this->write("        FAIL: '" . $e->getMessage() . "'\n" . $e->getTraceAsString());
        }
        parent::addFailure($test, $e, $time);
    }

    /**
     * @inheritdoc
     */
    public function endTest(\PHPUnit\Framework\Test $test, $time)
    {
        if ($this->verbose) {
            $t = microtime(true) - $this->timeStats['startTime'];
            if ($this->timeStats['min'] > $t) {
                $this->timeStats['min'] = $t;
            }
            if ($this->timeStats['max'] < $t) {
                $this->timeStats['max'] = $t;
                $this->timeStats['slowest'] = $test->getName();
            }
            $this->timeStats['avg'] = ($t + $this->timeStats['avg'] * $this->timeStats['cnt']) / (++$this->timeStats['cnt']);
        }
        parent::endTest($test, $time);
    }

    /**
     * @inheritdoc
     */
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
        parent::endTestSuite($suite);

        if ($this->verbose) {
            $this->write("\ntime stats: min {$this->timeStats['min']}, max {$this->timeStats['max']}, avg {$this->timeStats['avg']}, slowest test: {$this->timeStats['slowest']}|\n");
        }
    }

    /**
     * @inheritdoc
     */
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
        if ($this->verbose) {
            $this->write("\n\n" . $suite->getName() . "\n");

            $this->timeStats = array('cnt' => 0, 'min' => 9999999, 'max' => 0, 'avg' => 0, 'startTime' => 0, 'slowest' => '_ERROR_');
        }

        parent::startTestSuite($suite);
    }

    /**
     * @inheritdoc
     */
    public function startTest(\PHPUnit\Framework\Test $test)
    {
        if ($this->verbose) {
            $this->write("\n        " . $test->getName());

            $this->timeStats['startTime'] = microtime(true);
        }

        parent::startTest($test);
    }
}
