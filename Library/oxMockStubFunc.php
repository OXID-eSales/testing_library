<?php

/**
 * Class for creating stub objects.
 */
class oxMockStubFunc implements PHPUnit_Framework_MockObject_Stub
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
     * @param PHPUnit_Framework_MockObject_Invocation $invocation
     * The invocation which was mocked and matched by the current method
     * and argument matchers.
     *
     * @return mixed
     */
    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
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
