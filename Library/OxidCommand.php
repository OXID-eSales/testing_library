<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

require_once 'PHPUnit/TextUI/Command.php';

class OxidCommand extends PHPUnit_TextUI_Command
{

    public function __construct()
    {
        $this->longOptions['dbreset='] = 'dbResetHandler';
    }

    /**
     * @param boolean $exit
     */
    public static function main($exit = true)
    {
        $command = new OxidCommand();
        $command->run($_SERVER['argv'], $exit);
    }

    /**
     * @param array   $argv
     * @param boolean $exit
     */
    public function run(array $argv, $exit = true)
    {
        parent::run($argv, false);
    }

    protected function dbResetHandler($value)
    {
        /* require_once 'unit/oxPrinter.php';
         require_once 'unit/dbRestore.php';
         $dbM = new DbRestore();
         $dbM->dumpDB();*/
    }

}
