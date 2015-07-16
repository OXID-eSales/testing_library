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

class oxPrinter extends PHPUnit_TextUI_ResultPrinter
{
    /** @var int */
    private $timeStats;

    /**
     * @inheritdoc
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($this->verbose) {
            echo "        ERROR: '" . $e->getMessage() . "'\n" . $e->getTraceAsString();
        }
        parent::addError($test, $e, $time);
    }

    /**
     * @inheritdoc
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($this->verbose) {
            echo "        FAIL: '" . $e->getMessage() . "'\n" . $e->getTraceAsString();
        }
        parent::addFailure($test, $e, $time);
    }

    /**
     * @inheritdoc
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
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
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        parent::endTestSuite($suite);

        echo "\ntime stats: min {$this->timeStats['min']}, max {$this->timeStats['max']}, avg {$this->timeStats['avg']}, slowest test: {$this->timeStats['slowest']}|\n";
    }

    /**
     * @inheritdoc
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        echo("\n\n" . $suite->getName() . "\n");

        $this->timeStats = array('cnt' => 0, 'min' => 9999999, 'max' => 0, 'avg' => 0, 'startTime' => 0, 'slowest' => '_ERROR_');

        parent::startTestSuite($suite);
    }

    /**
     * @inheritdoc
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if ($this->verbose) {
            echo "\n        " . $test->getName();
        }

        $this->timeStats['startTime'] = microtime(true);

        parent::startTest($test);
    }
}
