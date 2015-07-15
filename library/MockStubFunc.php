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

use PHPUnit_Framework_MockObject_Invocation as Invocation;

/**
 * Class for creating stub objects.
 */
class MockStubFunc implements \PHPUnit_Framework_MockObject_Stub
{
    /** @var string */
    private $_func;

    /**
     * Constructor
     *
     * @param string $sFunc
     */
    public function __construct($sFunc)
    {
        $this->_func = $sFunc;
    }

    /**
     * Fakes the processing of the invocation $invocation by returning a
     * specific value.
     *
     * @param Invocation $invocation
     * The invocation which was mocked and matched by the current method
     * and argument matchers.
     *
     * @return mixed
     */
    public function invoke(Invocation $invocation)
    {
        if (is_string($this->_func) && preg_match('/^\{.+\}$/', $this->_func)) {
            $args = $invocation->parameters;
            $_this = $invocation->object;

            return eval($this->_func);
        } else {
            return call_user_func_array($this->_func, $invocation->parameters);
        }
    }

    /**
     * Returns user called function.
     *
     * @return string
     */
    public function toString()
    {
        return 'call user-specified function ' . $this->_func;
    }
}
