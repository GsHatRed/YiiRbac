<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'components' => [
        'user' => [
            'class' => 'backend\component\User',
            'identityClass' => 'backend\models\User',
            //'enableAutoLogin' => true,
            'loginUrl' => ['home/login'], 
            'returnUrl' => '/home/index', 
            //'autoRenewCookie' => false,
            'authTimeout' => 7200,
            'admin' => [ 'admin' ],//超级用户角色ID
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@runtime/logs/'.date('Y-m-d').'.log',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache',
        ],
    ],
    'params' => $params,
	'modules' => [
        'rbac' =>  [
            'class' => 'backend\module\rbac\Module',
        ],
        'gridview' =>  [
            'class' => 'kartik\grid\Module'
        ],
    ],
    //rbac检验行为类
    'as rbaccontrol' => [
        'class' => 'backend\module\rbac\component\RbacControl',
        //不需要检查权限的操作
        'except' => [
            'home/login', 'home/captcha',
            'home/index', 'home/logout',
            'site/error' , 'home/reset-password',
        ],
        'rule' => [
            'class' => 'backend\module\rbac\component\RbacRule',
            'allow' => true,
            //'ips' => ['192.168.1.1', '192.168.3.5']
        ],
    ],
];
