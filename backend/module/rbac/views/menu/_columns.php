<?php
use yii\helpers\Url;
use backend\module\rbac\models\AuthMenuSearch;

$data = AuthMenuSearch::getAllMenu();
$data = $data[0]+$data[1]+$data[2];
$menus = array_column($data, 'name', 'id');
return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'name',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'sort',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'level',
        'value' => function ($row) {
            return Yii::$app->params['rbac']['menu'][$row->level];
        },
        'filter' => Yii::$app->params['rbac']['menu'],
    ],
//        [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'id',
//    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'pid',
        'value' => function ($row) {
            $row = AuthMenuSearch::getByid($row->pid);
            return empty($row)?'':$row['name'];// 如果是数组数据则为 $data['name'] ，例如，使用 SqlDataProvider 的情形。
        },
        'filter' => $menus,
        'filterInputOptions' => [
                'class' => 'form-control select2'
        ]
    ],
//     [
//         'class'=>'\kartik\grid\DataColumn',
//         'attribute'=>'created_at',
//         'format' => 'datetime',
//     ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'uri',
        'value' => function ($row) {
            return empty($row->uri)?'':$row->uri;
         },
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip'],
        'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete', 
                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Are you sure?',
                          'data-confirm-message'=>'Are you sure want to delete this item'], 
    ],

];   
