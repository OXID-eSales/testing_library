<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary;

use PHPUnit\Framework\MockObject\Invocation;

/**
 * Class for creating stub objects.
 */
class MockStubFunc implements \PHPUnit\Framework\MockObject\Stub
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
            $args = $invocation->getParameters();
            $_this = $invocation->getObject();

            return eval($this->_func);
        } else {
            return call_user_func_array($this->_func, $invocation->getParameters());
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
