<?php
use Magento\Framework\Component\ComponentRegistrar;

if (class_exists(ComponentRegistrar::class)) {
    ComponentRegistrar::register(
        ComponentRegistrar::MODULE,
        'TddWizard_Fixtures',
        __DIR__ . '/src'
    );
}
