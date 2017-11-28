<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
