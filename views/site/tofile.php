<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?= $form->field($model, 'file')->fileInput() ?>

<?= Html::submitButton('OK', ['class' => 'btn btn-primary']); ?>

<?php ActiveForm::end(); ?>

