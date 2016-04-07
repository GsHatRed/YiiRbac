<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\module\rbac\models\Organization */
?>
<div class="organization-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'status',
            'created_at:datetime',
        ],
    ]) ?>

</div>
