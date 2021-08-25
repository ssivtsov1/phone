<?php


use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Вхід в адмін. частину довідника:';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h3><?= Html::encode($this->title) ?></h3>

    <p>Будь ласка заповніть поля для входу:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username') ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

<!--        --><?//= $form->field($model, 'rememberMe')->checkbox() ?>

<!--            <div style="color:#999;margin:1em 0">-->
<!--                If you forgot your password you can --><?//= Html::a('reset it', ['site/request-password-reset']) ?><!--.-->
<!--            </div>-->

            <div class="form-group">
                <?= Html::submitButton('Увійти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
