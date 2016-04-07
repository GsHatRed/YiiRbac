<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\module\rbac\models\AuthMenu */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="auth-menu-form">

    <?php $form = ActiveForm::begin(['id'=>'form']); ?>

    <?= $form->field($model, 'level')->dropDownList(Yii::$app->params['rbac']['menu'], ['onchange' => 'getMenu(this)', 'prompt'=>'请选择']) ?>
    
    <?= $form->field($model, 'pid')->dropDownList($preLevelMenu, ['prompt'=>'请选择']) ?>
    
    <?php
    if(empty($model->level) || $model->level<=3 )
        $class = 'form-group';
    else
        $class = 'form-group sr-only';
    echo $form->field($model, 'sort',['options'=>['class' => $class]])->textInput(['maxlength' => true]); ?>
   
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]); ?>
    
    <?php 
    if(empty($model->level) || $model->level>=3 )
        $class = 'form-group';
    else 
        $class = 'form-group sr-only';
    echo $form->field($model, 'uri',['options'=>['class' => $class]])->textInput(['maxlength' => true]) ?>
    
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
<script type="text/javascript">

var csrf = '<?=Yii::$app->request->getCsrfToken();?>';
function getMenu(obj)
{
	var level = obj.value;
	$("#authmenu-pid").val("").trigger("change"); 
	if(level == '' || level==1){
		$("#authmenu-pid option").remove();
		$('.field-authmenu-uri').addClass('sr-only');
		return;
	}else if(level == 2)
		$('.field-authmenu-uri').addClass('sr-only');
	else{
		$('.field-authmenu-uri').removeClass('sr-only');
		if(level == 4)
			$('.field-authmenu-sort').addClass('sr-only');
	}
	if(level<=3)
		$('.field-authmenu-sort').removeClass('sr-only');
	
	$.post("<?=Url::to(['menu/getmenu']) ?>" , {_csrf:csrf, 'level':level}, function(data){
		if($.isEmptyObject(data))
			alert('系统异常');
			
		var options = '<option value="" selected="selected">请选择</option>';
		for(var i in data){
			options += '<option value="'+i+'">'+data[i]+'</option>';
		}
		$("#authmenu-pid").html(options);
    }, 'json')
}

$(function() {
   $("#ajaxCrudModal").removeAttr("tabindex");
   $("#authmenu-pid").select2({width:'100%'});
});


</script>
