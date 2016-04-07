<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\module\rbac\models\AuthItem */
?>
<div class="auth-item-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            //'type',
            'description:ntext',
            //'rule_name',
            //'data:ntext',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
