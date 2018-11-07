<?php

namespace  MaxKosYii2\AdditionalExt\filters;

use yii\base\ActionFilter;
use yii\web\HttpException;

/**
 * Class AjaxOnly
 * @package MaxKosYii2\AdditionalExt\filters
 */
class AjaxOnly extends ActionFilter {

    public function beforeAction($action)
    {
        if (! \Yii::$app->request->isAjax)
            throw new HttpException(400, 'Your request is invalid.');

        return true;
    }
}