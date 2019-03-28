<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Services\Library;

use OxidEsales\Eshop\Core\ConfigFile;

class DatabaseDefaultsFileGenerator
{
    /**
     * @var ConfigFile
     */
    private $config;

    /**
     * @param ConfigFile $config
     */
    public function __construct(ConfigFile $config)
    {
        $this->config = $config;
    }

    /**
     * @return string File path.
     */
    public function generate(): string
    {
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('testing_lib', true) . '.cnf';
        $resource = fopen($file, 'w');
        $fileContents = "[client]"
            . "\nuser=" . $this->config->dbUser
            . "\npassword=" . $this->config->dbPwd
            . "\nhost=" . $this->config->dbHost
            . "\nport=" . $this->config->dbPort
            . "\n";
        fwrite($resource, $fileContents);
        fclose($resource);
        return $file;
    }
}
