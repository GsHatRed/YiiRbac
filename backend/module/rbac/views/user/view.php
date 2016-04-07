<?php

use yii\widgets\DetailView;
use common\models\Common;
use backend\module\rbac\models\Role;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
?>
<div class="user-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'realname',
            //'auth_key',
            //'password_hash',
            //'password_reset_token',
            'mobile',
            'email:email',
            [
                'attribute' => 'status',
                'value' => Yii::$app->params['rbac']['userStatus'][$model->status],
            ],
            [
                'attribute' => 'role',
                'value' => implode(',', Role::getDesByUid($model->id)[$model->id]),
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
