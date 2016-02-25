<?php
/**
 * Created by PhpStorm.
 * User: Rahul
 * Date: 27/07/15
 * Time: 12:16 PM.
 */
namespace practo\healthByteBundle\Manager;

use practo\healthByteBundle\Helper\Helper;

/**
 * Class BaseManager.
 */
class BaseManager
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Helper $helper
     */
    public function setHelper(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }
}
