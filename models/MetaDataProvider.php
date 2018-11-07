<?php

namespace MaxKosYii2\AdditionalExt\models;

use yii;
use yii\base\UnknownPropertyException;
use ReflectionMethod;

/**
 * Class MetaDataProvider
 *
 * @package MaxKosYii2\AdditionalExt\models
 */
abstract class MetaDataProvider
{
    /**
     * MetaDataProvider constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        if(!empty($config))
            Yii::configure($this, $config);
    }

    /**
     * @param $attribute
     * @return mixed
     * @throws UnknownPropertyException
     */
    public function __get($attribute)
    {
        $getter = 'get' . self::getCamelCaseAttribute($attribute);

        if(! method_exists($this, $getter))
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $attribute);

        return $this->$getter();
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function hasGetter($attribute)
    {
        return method_exists($this, 'get' . self::getCamelCaseAttribute($attribute));
    }

    /**
     * @param $attribute
     * @return mixed
     */
    protected static function getCamelCaseAttribute($attribute)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getMethodsInformation()
    {
        $methods = [];

        foreach (get_class_methods(static::class) as $methodName)
        {
            // Class name should be started at 'get'
            if(! preg_match('/^get/', $methodName))
                continue;

            $method = new ReflectionMethod(static::class, $methodName);
            $phpDoc = $method->getDocComment();

            if(preg_match('/@description([^\r\n]+)/i', $phpDoc, $matches))
            {
                $methodKey = preg_replace('([A-Z])', '_$0', $methodName);
                $methodKey = preg_replace('/^get_/', '', $methodKey);
                $methodKey = strtolower($methodKey);

                $methods[$methodKey] = trim($matches[1]);
            }
        }

        return $methods;
    }

}