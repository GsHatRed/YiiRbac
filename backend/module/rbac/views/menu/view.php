<?php

use yii\widgets\DetailView;
use backend\module\rbac\models\AuthMenuSearch;

/* @var $this yii\web\View */
/* @var $model backend\module\rbac\models\AuthMenu */
?>
<div class="auth-menu-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'id',
            [
                'attribute' => 'pid',
                'value' => empty(AuthMenuSearch::getByid($model->pid))?'':AuthMenuSearch::getByid($model->pid)['name'],
            ],
            'sort',
            'name',
            'created_at:datetime',
            [
               'attribute' => 'level',
               'value' => Yii::$app->params['rbac']['menu'][$model->level],
            ],
            [
               'attribute' => 'uri',
               'value' => $model->uri? $model->uri : '',
            ],
        ],
    ]) ?>

</div>
