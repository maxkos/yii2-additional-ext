<?php

namespace MaxKosYii2\AdditionalExt\traits;

/**
 * Trait EnumTrait
 * @package MaxKosYii2\AdditionalExt\traits
 */
trait EnumTrait
{
    /**
     * @var array
     */
    protected static $_constCache = [];

    /**
     * @param null $prefix
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getConstants($prefix = null)
    {
        $class = get_called_class();

        if(!isset(self::$_constCache[$class]['all']))
        {
            $reflect = new \ReflectionClass(get_called_class());
            self::$_constCache[$class]['all'] = $reflect->getConstants();
        }

        if($prefix)
        {
            if(!isset(self::$_constCache[$class][$prefix]))
            {
                $prefixLen = strlen($prefix);
                foreach(($_constCache = self::$_constCache[$class]['all']) as $key => $value)
                {
                    if(($shortKey = substr($key, 0, $prefixLen)) != $prefix)
                        unset($_constCache[$key]);
                }
                self::$_constCache[$class][$prefix] = $_constCache;
            }

            return self::$_constCache[$class][$prefix];
        }

        return self::$_constCache[$class]['all'];
    }

    /**
     * Return constant value by name
     *
     * @param $key
     *
     * @return null
     * @throws \ReflectionException
     */
    public static function getConstant($key)
    {
        $constants = self::getConstants();

        return array_key_exists($key, $constants) ? $constants[$key] : null;
    }

    /**
     * Return constant name by value
     *
     * @param      $key
     * @param null $prefix
     *
     * @return false|int|string
     * @throws \ReflectionException
     */
    public static function getConstantName($key, $prefix = null)
    {
        $constants = self::getConstants($prefix);

        return array_search($key, $constants);
    }
}