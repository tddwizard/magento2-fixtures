<?php

namespace PHPUnit\Framework {

    /*
     * TestCase::class cannot be used because it would break code generation in Magento 2.1
     * where the code scanner looks for the first "class" keyword.
     */
    if (!class_exists('\PHPUnit\Framework\TestCase')) {
        /*
         * Forward compatibility for PHPUnit 4.x (Magento 2.1)
         */
        abstract class TestCase extends \PHPUnit_Framework_TestCase
        {
            public function expectException($exceptionName)
            {
                $this->setExpectedException($exceptionName);
            }
        }
    }
}
