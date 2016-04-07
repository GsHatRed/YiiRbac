<?php 
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yii\helpers\Html;

$data = $searchModel::getAllMenu();
$data = $data[0]+$data[1]+$data[2];
$menus = array_column($data, 'name', 'id');

$this->registerJs(
   '$("document").ready(function(){
        $("#menu").on("pjax:end", function() {
            $.pjax.reload({container:"#crud-datatable-pjax"});  //Reload GridView
        });
    });'
);
?>

<?php yii\widgets\Pjax::begin(['id' => 'menu']) ?>

    <?php $form = ActiveForm::begin(['id'=>'form', 'method'=>'get','options'=>['class'=>'form-inline', 
            'style'=>'margin:5px 0 15px 0;padding:1% 0 7% 0', 'data-pjax' => true ]]); ?>
    
    <?= $form->field($searchModel, 'level',['options'=>['class'=>'form-group col-md-3']])->dropDownList(Yii::$app->params['rbac']['menu'], ['prompt'=>'请选择']) ?>
        
    <?= $form->field($searchModel, 'pid',['options'=>
            ['class'=>'form-group col-md-3'], 'inputOptions' => 
            ['class' => 'form-control select2']])->dropDownList($menus, ['prompt'=>'请选择']) ?>
    
    <?= $form->field($searchModel, 'name',['options'=>['class'=>'form-group col-md-3']])->textInput(['maxlength' => true]); ?>

    <?= $form->field($searchModel, 'uri',['options'=>['class' => 'form-group col-md-3']])->textInput(['maxlength' => true]) ?>
    
    <div class="form-group col-md-3 ">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?= Html::submitButton( 'search', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
    
<?php yii\widgets\Pjax::end() ?>