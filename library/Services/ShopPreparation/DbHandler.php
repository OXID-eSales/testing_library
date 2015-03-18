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

class DbHandler
{
    /**
     * @var string
     */
    private $_sTemporaryFolder = '';

    /** @var oxConfigFile */
    private $_configFile;

    /**
     *
     */
    public function __construct()
    {
        $this->_configFile = new oxConfigFile(SHOP_PATH . "config.inc.php");
    }

    /**
     * Set temporary folder
     *
     * @param string $sTemporaryFolder folder path
     */
    public function setTemporaryFolder( $sTemporaryFolder )
    {
        $this->_sTemporaryFolder = $sTemporaryFolder;
    }

    /**
     * Return temporary folder path
     *
     * @return string
     */
    public function getTemporaryFolder()
    {
        return $this->_sTemporaryFolder;
    }

    /**
     * Creates a dump of the current database, and store in temporary folder.
     * The dump includes the data and sql insert statements.
     *
     * @param string $sDumpFilePrefix dump file name prefix.
     */
    public function dumpDB( $sDumpFilePrefix = null )
    {
        $sFileName = $this->_getDumpFileName( $sDumpFilePrefix );
        $this->_executeCommand( $this->_getExportCommand( $sFileName ) );
    }

    /**
     * Restore db from existing dump
     *
     * @param string $sDumpFilePrefix dump file name prefix.
     */
    public function restoreDB( $sDumpFilePrefix = null )
    {
        $this->import( $this->_getDumpFileName( $sDumpFilePrefix ) );
    }

    /**
     * Execute sql statements from sql file
     *
     * @param string $sSqlFile sql file name
     */
    public function import( $sSqlFile )
    {
        if ( file_exists($sSqlFile) ) {
            $this->_executeCommand( $this->_getImportCommand( $sSqlFile ) );
        }
    }

    /**
     * Returns CLI import command, execute sql from given file
     *
     * @param string $sFileName - file name
     *
     * @return string
     */
    protected function _getImportCommand( $sFileName )
    {
        $sCmd  = 'mysql -h' . escapeshellarg( $this->_getDbHost() );
        $sCmd .= ' -u' . escapeshellarg( $this->_getDbUser() );
        $sCmd .= ' -p' . escapeshellarg( $this->_getDbPwd() );
        $sCmd .= ' --default-character-set=' . $this->getCharsetMode() .' '. escapeshellarg( $this->_getDbName() );
        $sCmd .= '  < ' . escapeshellarg( $sFileName ) . ' 2>&1';

        return $sCmd;
    }

    /**
     * Returns CLI command for db export to given file name
     *
     * @param string $sFileName file name
     *
     * @return string
     */
    protected function _getExportCommand( $sFileName )
    {
        $sCommand  = 'mysqldump -h' . escapeshellarg( $this->_getDbHost() );
        $sCommand .= ' -u' . escapeshellarg( $this->_getDbUser() );
        $sCommand .= ' -p' . escapeshellarg( $this->_getDbPwd() );
        $sCommand .= ' --add-drop-table ' . escapeshellarg( $this->_getDbName() );
        $sCommand .= '  > ' . escapeshellarg( $sFileName );

        return $sCommand;
    }

    /**
     * Execute shell command
     *
     * @param $sCommand
     *
     * @throws Exception
     */
    protected function _executeCommand( $sCommand )
    {
        exec($sCommand, $sOutput, $ret);

        if ( $ret > 0 ) {
            sleep(1);
            exec($sCommand, $sOutput, $ret);

            if ($ret > 0) {
                $sOutput = implode( "\n", $sOutput );
                throw new Exception( $sOutput );
            }
        }
    }

    /**
     * Create dump file name
     *
     * @param string $sDumpFilePrefix - dump file prefix
     *
     * @return string
     */
    protected function _getDumpFileName( $sDumpFilePrefix = null )
    {
        if ( empty( $sDumpFilePrefix ) ) {
            $sDumpFilePrefix = 'tmp_db_dump';
        }

        $sFileName = $this->getTemporaryFolder() . '/' . $sDumpFilePrefix . '_' . $this->_getDbName();

        return $sFileName;
    }

    /**
     * Returns charset mode
     *
     * @return string
     */
    private function getCharsetMode()
    {
        return $this->_configFile->iUtfMode ? 'utf8' : 'latin1';
    }

    /**
     * @return string
     */
    protected function _getDbName()
    {
        return $this->_configFile->dbName;
    }

    /**
     * @return string
     */
    protected function _getDbUser()
    {
        return $this->_configFile->dbUser;
    }

    /**
     * @return string
     */
    protected function _getDbPwd()
    {
        return $this->_configFile->dbPwd;
    }
    /**
     * @return string
     */
    protected function _getDbHost()
    {
        return $this->_configFile->dbHost;
    }
}
