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
namespace OxidEsales\TestingLibrary\Services\Library;

/**
 * Class used for uploading files in services.
 */
class Request
{
    /** @var array Request parameters */
    private $parameters = array();

    /**
     * Sets parameters to request
     *
     * @param array $parameters
     */
    public function __construct($parameters = null)
    {
        $this->parameters = $_REQUEST;
        if (!empty($parameters)) {
            $this->parameters = array_merge($this->parameters, $parameters);
        }
    }

    /**
     * Returns request parameter
     *
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        return array_key_exists($name, $this->parameters) ? $this->parameters[$name] : $default;
    }

    /**
     * Returns uploaded file parameter
     *
     * @param string $name param name
     *
     * @return mixed
     */
    public function getUploadedFile($name)
    {
        $filePath = '';
        if (array_key_exists($name, $_FILES)) {
            $filePath = $_FILES[$name]['tmp_name'];
        } elseif (array_key_exists($name, $this->parameters)) {
            $filePath = substr($this->parameters[$name], 1);
        }

        return $filePath;
    }
}
