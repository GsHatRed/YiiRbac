<?php

namespace frontend\module\forum\v1;

use Yii;
use yii\web\Response;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'frontend\module\forum\v1\controllers';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
    
    public function beforeAction($action)
    {
        //yii::$app->response->format = Response::FORMAT_JSON;
        //die(json_encode( [ Yii::$app->request->get(), Yii::$app->request->post() ] ));
        //Common::p($action);
        return parent::beforeAction($action);
    }
}
