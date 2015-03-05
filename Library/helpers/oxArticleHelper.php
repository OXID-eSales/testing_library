<?php


/**
 * Helper class for oxArticle.
 */
class oxArticleHelper extends oxArticle
{
    /**
     * Constructor
     *
     * @param array $params Parameters
     */
    public function __construct($params = null)
    {
        $this->cleanup();
        parent::__construct($params);
    }

    /**
     * Clean oxArticle static variables.
     */
    public static function cleanup()
    {
        self::resetArticleCategories();
        self::resetCache();
        self::resetAmountPrice();
    }

    /**
     * Get private field value.
     *
     * @param string $name Field name
     *
     * @return mixed
     */
    public function getVar($name)
    {
        return $this->{'_' . $name};
    }

    /**
     * Set private field value.
     *
     * @param string $name  Field name
     * @param string $value Field value
     */
    public function setVar($name, $value)
    {
        $this->{'_' . $name} = $value;
    }

    /**
     * Reset cached private variable values.
     */
    public static function resetCache()
    {
        parent::$_aArticleVendors = array();
        parent::$_aArticleManufacturers = array();
        parent::$_aLoadedParents = null;
        parent::$_aSelList = null;
    }

    /**
     * Clean private variable values.
     */
    public static function resetArticleCategories()
    {
        parent::$_aArticleCats = array();
    }

    /**
     * Reset cached private variable values.
     */
    public static function resetAmountPrice()
    {
        parent::$_blHasAmountPrice = null;
    }
}
