<?php

define('ROOT_DIRECTORY', __DIR__ . '/../');

require_once __DIR__ .'/../base.php';
$testConfig = new OxidEsales\TestingLibrary\TestConfig();

require_once $testConfig->getShopPath() . '/vendor/autoload.php';