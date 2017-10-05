<?php

namespace PHPUnit\Framework {

    if (!class_exists(TestCase::class)) {
        /*
         * Forward compatibility for PHPUnit 4.x (Magento 2.1)
         */

        abstract class TestCase extends \PHPUnit_Framework_TestCase
        {
        }
    }
}
