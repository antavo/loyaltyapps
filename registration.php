<?php
/**
 *
 */

use Magento\Framework\Component\ComponentRegistrar;

// Registering Antavo LoyaltyApps namespace
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Antavo_LoyaltyApps',
    __DIR__
);
