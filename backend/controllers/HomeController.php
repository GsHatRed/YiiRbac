<?php
namespace backend\controllers;

use Yii;
use backend\models\LoginForm;
use backend\module\rbac\models\AuthMenuSearch;
use \yii\web\Response;
use common\models\Common;

class HomeController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
//                 'verbs' => [
//                         'class' => VerbFilter::className(),
//                         'actions' => [
//                                 'logout' => ['post'],
//                         ],
//                 ],
        ];
    }
    
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                //'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'height' => '40',
                'minLength' => '4',
                'maxLength' => '5',
             ],
       ];
    }
    
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goBack();
        }
        
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $catpcha = 0;
            $session = Yii::$app->getSession();
            if($session->has('loginTime') && $session->get('loginTime')>2)
                $catpcha = 1;
                
            return $this->renderAjax('login', [
                    'model' => $model,
                    'catpcha' => $catpcha,
                   ]);
        }
    }

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }
        //Common::p(\Yii::$app->getRequest()->post());
        $menus = AuthMenuSearch::getMenuList();
        $nav = array_column($menus, 'name', 'name');
        return $this->renderPartial('index',[
                    'menu' => json_encode($menus) ,
                    'nav' => $nav,
                ]);
    }
    
    public function actionLogout()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goBack();
        }
        
        Yii::$app->user->logout();
        return $this->goHome();
    }
    
    /**
     * 用户修改自己的密码
     */
    public function actionResetPassword()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goBack();
        }
        
        $return = ['status' => true, 'msg' => ''];
        
        $request = Yii::$app->request;
        if($request->isAjax && $request->isPost){
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model = Yii::$app->getUser()->identity;
            $model->scenario = 'resetPassword';
            $model->load($request->post());
            if(!$model->validate()){
                $return['status'] = false;
                $return['msg']    = implode(',', array_values($model->errors)[0]);
            }elseif (!$model->validatePassword($model->oldPassword)){
                $return['status'] = false;
                $return['msg']    = '旧密码错误';
            }elseif (!$model->resetPassword()){
                $return['status'] = false;
                $return['msg']    = '系统异常';
            }
            
            return $return;
        }
    }
}

