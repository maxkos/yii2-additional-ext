<?php

namespace MaxKosYii2\AdditionalExt\models;

use Yii;
use yii\db\ActiveRecordInterface;
use yii\helpers\ArrayHelper;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Class MultipleModel
 *
 * @package MaxKosYii2\AdditionalExt\models
 */
class MultipleModel extends Model
{
    /**
     * Creates and populates a set of models.
     *
     * @param       $modelClass
     * @param array $multipleModels
     * @param array $options
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function createMultiple($modelClass, $multipleModels = [], $options = [])
    {

        $updateScenario = !empty($options['update']['scenario']) ? $options['update']['scenario'] : null;
        unset($options['update']['scenario']);

        if (!empty($options['_nested'])) {
            $_nested = $options['_nested'];
            unset($options['_nested']);
            foreach ($_nested as $key => $item) {
                $itemAfter = null;
                if (is_array($item)) {
                    $itemClass = $item[0];
                    $itemAfter = $item[1];
                }
                else
                    $itemClass = $item;
                $_model = new $itemClass();
                if ($_model instanceof ActiveRecord)
                    $_model->loadDefaultValues(true);

                $_nested[$key] = [
                    'itemClass' => $itemClass,
                    'model' => $_model,
                    'data'  => Yii::$app->request->post($_model->formName()),
                    'after' => $itemAfter,
                ];
            }
        }

        /** @var ActiveRecord $model */
        $model = new $modelClass(isset($options['insert']) ? $options['insert'] : $options);
        if ($model instanceof ActiveRecord)
            $model->loadDefaultValues(true);

        $formName = $model->formName();
        $post = Yii::$app->request->post($formName);
        $models = [];

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                $__model = null;
                if (is_array($item))
                    $item['position'] = $i;
                elseif ($item instanceof ActiveRecord && $item->hasAttribute('position'))
                    $item->setAttribute('position', $i);

                if ($__model = self::inMultipleModels($item, $multipleModels)) {
                    if (!empty($options['update']))
                        $__model->setAttributes($options['update']);

                    if ($updateScenario)
                        $__model->setScenario($updateScenario);

                    $__model->setAttributes($item);
                }
                else {
                    $__model = clone $model;
                    $__model->setAttributes($item);
                    $__model->setAttributes(array_diff_key($options['insert'], ['scenario' => true]));

                }

                if (isset($_nested) && is_array($_nested)) {
                    foreach ($_nested as $_key_nested => $_child) {

                        $_subModels = [];
                        if (!empty($_child['data'][$i]) && is_array($_child['data'][$i])) {
                            foreach ($_child['data'][$i] as $key_child => $child) {
                                $_submodel = null;
                                if (!empty($child['id']) && !empty($_child['itemClass'])) {
                                    $itemClass = $_child['itemClass'];
                                    $_submodel = $itemClass::findOne($child['id']);
                                }
                                if (!$_submodel)
                                    $_submodel = clone $_child['model'];

                                $_submodel->setAttributes($child);
                                $_submodel->loadDefaultValues(true);
                                $_subModels[$key_child] = $_submodel;
                                unset($_submodel);
                            }
                        }
                        if (!empty($after = $_child['after']) && is_callable($after)) {
                            $after($_subModels, $__model, $i);
                        }
                        $__model->$_key_nested = $_subModels;
                    }
                }
                $models[$i] = $__model;
            }
        }


        unset($model, $formName, $post);

        return $models;
    }

    /**
     * @param $models ActiveRecord[]
     * @param $attributeName
     * @param bool $isMultiplie
     *
     * @return mixed
     */
    public static function loadMultipleFiles($models, $attributeName, $isMultiplie = false)
    {

        foreach ($models as $index => $model) {
            $upload = UploadedFile::getInstance($model, "[{$index}]{$attributeName}");
            if ($upload) {
                $model->$attributeName = $upload;
            }
            elseif ($model->$attributeName === 'remove') {
                $model->$attributeName = null;
            }
            else
                $model->$attributeName = $model->getOldAttribute($attributeName);

        }

        return $models;
    }

    /**
     * @param $data
     * @param $multipleModels
     *
     * @return null
     */
    public static function inMultipleModels($data, $multipleModels)
    {
        $in = false;
        $model = null;
        foreach ($multipleModels as $_model) {
            $primaryKey = $_model->primaryKey;

            $primaryKeyName = $_model->primaryKey();
            if (is_array($primaryKeyName) && count($primaryKeyName) == 1)
                $primaryKeyName = current($primaryKeyName);

            if (is_array($primaryKeyName) && count($primaryKeyName) > 1) {
                $_in = null;
                foreach ($primaryKey as $key => $val) {
                    if (!$_in = !empty($data[$key]) && $data[$key] == $val ? (!is_null($_in) && $_in ? $_in : true) : false)
                        break;
                }

                if ($_in) {
                    $in = $_in;
                    $model = $_model;
                    break;
                }
            }
            else {
                if ($in = !empty($data[$primaryKeyName]) && $data[$primaryKeyName] == $primaryKey ? true : false) {
                    $model = $_model;
                    break;
                }
            }
        }

        return $in ? $model : null;
    }

    /**
     * @param $multipleModels ActiveRecord[]
     *
     * @return array
     */
    public static function getPrimaryKeys($multipleModels, $primaryKey = 'primaryKey' )
    {
        $primaryKeys = [];

        foreach ($multipleModels as $multipleModel) {
            if (!$multipleModel->$primaryKey || (is_array($multipleModel->$primaryKey) && empty(array_filter($multipleModel->$primaryKey))))

                continue;
            else
                $primaryKeys[] = $multipleModel->$primaryKey;
        }

        return $primaryKeys;
    }

    /**
     * @param $multipleModels
     * @param $multipleModelsTwo
     *
     * @return array
     */
    public static function diffPrimaryKeys($multipleModels, $multipleModelsTwo)
    {
        $diff = [];
        if (!empty($multipleModels[0]) && is_array($multipleModels[0])) {
            $diff = array_filter($multipleModels, function ($v) use ($multipleModelsTwo) {
                $_in = true;
                foreach ($multipleModelsTwo as $multipleModelTwo) {
                    if (empty(array_diff($v, $multipleModelTwo))) {
                        $_in = false;
                        break;
                    }
                    else {
                        continue;
                    }
                }

                return $_in;
            });
        }
        elseif (!empty($multipleModels[0])) {
            $diff = array_diff($multipleModels, array_filter($multipleModelsTwo));
        }

        return $diff;
    }

    /**
     * @param Model $models
     * @param null  $attributeNames
     * @param array $options
     *
     * @return bool
     */
    public static function validateMultiple(Model $models, $attributeNames = null, $options = [])
    {
        $valid = true;
        foreach ($models as $model) {
            $valid = $model->validate($attributeNames) && $valid;
            if (!empty($options['_nested']))
                foreach (array_keys($options['_nested']) as $_childName) {

                    if (isset($model->$_childName))
                    {
                        foreach ($model->$_childName as $childItem) {

                            $valid = $childItem->validate() && $valid;
                        }
                        if (!$valid)
                            $model->addError($_childName, 'Nested values is not valid');
                    }

                }

        }

        return $valid;
    }
}