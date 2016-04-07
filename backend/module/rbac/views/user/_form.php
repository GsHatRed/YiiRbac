<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\module\rbac\models\Role;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\widgets\ActiveForm */
$new = false;
$class = 'form-group sr-only';
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>
	
	<?php if(!$model->isNewRecord){ ?>
	<?= $form->field($model, 'status')->dropDownList(Yii::$app->params['rbac']['userStatus']) ?>
	<?php } ?>
	
	<?= $form->field($model, 'role')->dropDownList( Role::getDeses() , ['multiple'=>'multiple']) ?>
	
	<?php if($model->isNewRecord){ 
        $new = true;
        $class = 'form-group';
        ?>
    
    <?= $form->field($model, 'username')->textInput() ?>
    
    <?= $form->field($model, 'realname')->textInput() ?>
    
    <?= $form->field($model, 'mobile')->textInput() ?>
    
    <?= $form->field($model, 'email')->textInput() ?>
    <?php }else{ ?>
    <?=Html::button('修改密码', ['id'=>'editPasswd'])?><br><br>
    <?php } ?>
    <?= $form->field($model, 'password',['options'=>['class'=>$class]])->textInput() ?>
    
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
<script type="text/javascript">
$(function() {
   $("#ajaxCrudModal").removeAttr("tabindex");
   $("<?=$new?'#signupform-role':'#user-role'; ?>").select2({width:'100%'});
});

$("#editPasswd").click(function(){
   $(".field-user-password").removeClass('sr-only');
});


</script>