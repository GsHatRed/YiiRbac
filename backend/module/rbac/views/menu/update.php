<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\module\rbac\models\AuthMenu */
?>
<div class="auth-menu-update">

    <?= $this->render('_form', [
        'model' => $model,
		'preLevelMenu' => $preLevelMenu,
    ]) ?>

</div>
