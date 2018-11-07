<?php

namespace MaxKosYii2\AdditionalExt\models;

use MaxKosYii2\AdditionalExt\helpers\Helper;
use MaxKosYii2\AdditionalExt\traits\ModelAdditiona;
use yii\db\ActiveRecord as BaseActiveRecord;

/**
 * Class ActiveRecord
 * @package MaxKosYii2\AdditionalExt\models
 */
class ActiveRecord extends BaseActiveRecord
{
    use ModelAdditiona;

    public static $collectionFields = null;
    public static $collectionExpand = null;

    /**
     * Scenarios
     */
    const SCENARIO_CREATE = 'insert';
    const SCENARIO_EDIT = 'update';

    /**
     * List of traits using in class
     *
     * @var array
     */
    protected static $_traits = [];

    /**
     *
     */
    public function init()
    {
        parent::init();

        $this->runTraitsMethod(__FUNCTION__);
    }

    /**
     * @return array
     */
    public function rules()
    {
        $finalRules = [];

        if ($traitRules = $this->runTraitsMethod(__FUNCTION__)['array']) {
            foreach ($traitRules as $trait => $rules) {
                $finalRules = array_merge($finalRules, $rules);
            }
        }

        return $finalRules;
    }

    /**
     * @return array
     */
    protected function _getTraits()
    {
        $class = get_called_class();

        if (!isset(self::$_traits[$class])) {
            $classes = [$class => $class];
            $classes += class_parents($class);

            // Remove Yii2 base classes from traits list
            unset($classes['yii\db\ActiveRecord']);
            unset($classes['yii\db\BaseActiveRecord']);
            unset($classes['yii\base\Model']);
            unset($classes['yii\base\Component']);
            unset($classes['yii\base\Object']);
            unset($classes['yii\base\BaseObject']);

            $traits = [];
            foreach ($classes as $classForTrait)
                $traits += class_uses($classForTrait);

            self::$_traits[$class] = $traits;
        }

        return self::$_traits[$class];
    }

    /**
     * Check, if variable static::$_name exist, return it.
     * If no, return static::$name
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function getTraitProperty($name)
    {
        return (($traitVar = "_{$name}") && isset(static::$$traitVar)) ? static::$$traitVar : static::$$name;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $valid = parent::beforeSave($insert);
        $valid &= $this->runTraitsMethod(__FUNCTION__)['bool'];

        return $valid;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $valid = parent::beforeDelete();
        $valid &= $this->runTraitsMethod(__FUNCTION__)['bool'];

        return $valid;
    }

    /**
     * Call method from all traits where it defined
     *
     * @param string $method
     *
     * @return array
     */
    public function runTraitsMethod($method)
    {
        $resultBool = true;
        $resultArray = [];

        foreach ($this->_getTraits() as $trait) {
            $trait = Helper::getShortClassName($trait);
            $traitMethod = $trait . $method;

            if (method_exists($this, $traitMethod)) {
                $resultMixed = $this->$traitMethod();
                $resultBool &= $resultMixed;
                $resultArray[$trait] = $resultMixed;
            }
        }

        return ['bool' => $resultBool, 'array' => $resultArray];
    }

    /**
     * @param       $pathTmpl
     * @param array $placeholders
     *
     * @return string
     */
    public function renderPath($pathTmpl, $placeholders = [])
    {
        $_placeholders = [];
        foreach ((array)$placeholders as $name => $value)
            $_placeholders['{' . $name . '}'] = $value;

        return strtr($pathTmpl, $_placeholders);
    }

}