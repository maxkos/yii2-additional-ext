<?php

namespace MaxKosYii2\AdditionalExt\models;

use MaxKosYii2\AdditionalExt\traits\ModelAdditiona;
use yii\base\Model as BaseModel;

class Model extends BaseModel
{
    use ModelAdditiona;

    public static $collectionFields = null;
    public static $collectionExpand = null;
}