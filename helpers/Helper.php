<?php

namespace MaxKosYii2\AdditionalExt\helpers;

/**
 * Class Helper
 * @package MaxKosYii2\AdditionalExt\helpers
 */
class Helper
{

    /**
     * Getting class name without namespace
     *
     * @param string|object $class
     * @return string
     */
    public static function getShortClassName($class)
    {

        if (is_object($class))
            $className = $class::className();
        else
            $className = (string)$class;

        return substr(strrchr($className, '\\'), 1);
    }

    /**
     * Put any variable int to array
     *
     * @param mixed $var
     * @return array
     */
    public static function arrayCast($var)
    {
        if (is_object($var))
            return [$var];

        return (array)$var;
    }

    /**
     * @param $timestamp
     * @return null|string
     */
    public static function timestampToMySQLDateTime($timestamp)
    {
        $datetime = null;
        if ($timestamp === true)
            $datetime = date(MYSQL_DATETIME_FORMAT);
        elseif ($timestamp && $_datetime = date(MYSQL_DATETIME_FORMAT, $timestamp))
            $datetime = $_datetime;

        return $datetime;
    }

}


