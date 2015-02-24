<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

/**
 * Class used for uploading files in services.
 */
class Request
{
    /**
     * Returns request parameter
     *
     * @param string $name
     * @param null   $default
     *
     * @return null
     */
    public function getParameter($name, $default = null)
    {
        return array_key_exists($name, $_REQUEST) ? $_REQUEST[$name] : $default;
    }

    /**
     * Returns uploaded file parameter
     *
     * @param string $name param name
     *
     * @return null
     */
    public function getUploadedFile($name)
    {
        return array_key_exists($name, $_FILES) ? $_FILES[$name] : null;
    }
}
