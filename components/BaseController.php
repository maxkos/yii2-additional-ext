<?php

namespace MaxKosYii2\AdditionalExt\components;

use Yii;
use yii\helpers\HtmlPurifier;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class BaseController
 * @package MaxKosYii2\AdditionalExt\components
 */
class BaseController extends Controller
{

    /**
     * @param        $model
     * @param null   $successMessage
     * @param string $actionFirst
     * @param string $actionSecond
     *
     * @return Response
     */
    public function redirectOnSuccess($model, $successMessage = null, $actionFirst = 'update', $actionSecond = 'index')
    {
        if ($successMessage !== null)
            $this->setSuccessFlash($successMessage);

        if (!isset($_POST['submit-type']))
            return $this->redirect([$actionFirst, 'id' => $model->primaryKey]);
        else
            return $this->redirect([$actionSecond]);
    }

    /**
     * @param      $message
     * @param bool $append
     * @param bool $removeAfterAccess
     */
    public function setSuccessFlash($message, $append = false, $removeAfterAccess = true)
    {
        $this->setFlash('success', $message, $append, $removeAfterAccess);
    }

    /**
     * @param      $message
     * @param bool $append
     * @param bool $removeAfterAccess
     */
    public function setWarningFlash($message, $append = false, $removeAfterAccess = true)
    {
        $this->setFlash('warning', $message, $append, $removeAfterAccess);
    }

    /**
     * @param      $message
     * @param bool $append
     * @param bool $removeAfterAccess
     */
    public function setFailureFlash($message, $append = false, $removeAfterAccess = true)
    {
        $this->setFlash('error', $message, $append, $removeAfterAccess);
    }

    /**
     * @param      $message
     * @param bool $append
     * @param bool $removeAfterAccess
     */
    public function setInfoFlash($message, $append = false, $removeAfterAccess = true)
    {
        $this->setFlash('info', $message, $append, $removeAfterAccess);
    }

    /**
     * @param      $key
     * @param      $message
     * @param bool $append
     * @param bool $removeAfterAccess
     */
    public function setFlash($key, $message, $append = false, $removeAfterAccess = true)
    {
        $message = HtmlPurifier::process($message);

        if ($append)
            Yii::$app->getSession()->addFlash($key, $message, $removeAfterAccess);
        else
            Yii::$app->getSession()->setFlash($key, $message, $removeAfterAccess);
    }

    /**
     * @param string $message
     * @param array  $data
     *
     * @return array
     */
    public function ajaxSuccess($message = '', $data = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->ajaxResponse(true, $message, $data);
    }

    /**
     * @param string $message
     * @param array  $data
     *
     * @return array
     */
    public function ajaxError($message = '', $data = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->ajaxResponse(false, $message, $data);
    }

    /**
     * @param        $success
     * @param string $message
     * @param array  $data
     *
     * @return array
     */
    protected function ajaxResponse($success, $message = '', $data = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $output = ['success' => $success];

        if ($message)
            $output['message'] = $message;

        if ($data)
            $output['data'] = $data;

        return $output;
    }

    /**
     * @param      $message
     * @param bool $append
     */
    public function setSuccessToastr($message, $append = false)
    {
        $this->setToastr('success', $message, $append);
    }

    /**
     * @param      $message
     * @param bool $append
     */
    public function setErrorToastr($message, $append = false)
    {
        $this->setToastr('error', $message, $append);
    }

    /**
     * @param      $key
     * @param      $message
     * @param bool $append
     */
    public function setToastr($key, $message, $append = false)
    {
        $session = Yii::$app->getSession();

        $toastr = [];

        if($append)
            $toastr = $session->get('toastr', []);

        $toastr[] = [$key => $message];

        $session->set('toastr', $toastr);
    }

}