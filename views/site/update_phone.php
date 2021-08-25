<?php
//namespace app\models;
use yii\helpers\Html;
//use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\models\spr_res;
use app\models\status_sch;
$role = Yii::$app->user->identity->role;
//debug($model)

?>
<script>
   window.onload=function(){
    $(document).click(function(e){

	  if ($(e.target).closest("#recode-menu").length) return;

	   $("#rmenu").hide();

	  e.stopPropagation();

	  });
   }        
</script>

<br>
<div class="row">
    <div class="col-lg-6">
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'enableAjaxValidation' => false,]); ?>

    <?= $form->field($model, 'fio')->textInput() ?>
    <?= $form->field($model, 'post')->textInput() ?>
   
    <?= $form->field($model, 'tel_mob',
            ['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-phone"></span></span>{input}</div>'] )->textInput() ?>
    <?= $form->field($model, 'tel_town',
            ['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-phone-alt"></span></span>{input}</div>'] )->textInput() ?>
    <?= $form->field($model, 'tel',
            ['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-earphone"></span></span>{input}</div>'] )->textInput() ?>
    
    <?= $form->field($model, 'line')->textInput() ?>
    <?= $form->field($model, 'type_tel')->textInput() ?>
    <?= $form->field($model, 'phone_type')->textInput() ?>
<!--   <p>  </p>-->
    <?= $form->field($model, 'photo')->fileInput() ?>

    <? if($model->photo)
        echo Html::a('Видалити фото', ['del_photo', 'id' => $model->id,
            'file_path' => $model->photo,'sql' => $sql], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Ви впевнені, що хочете видалити це фото?',
                'method' => 'post',
            ]]);  ?>

        <br>
        <br>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'ОК' : 'OK', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    </div>
</div>


