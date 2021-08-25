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

    <?php
        // Установка статусов в соответствии с доступами
        switch($role) {
        case 3: // Полный доступ
            echo $form->field($model, 'status')->dropDownList(ArrayHelper::map(status_sch::find()->all(), 'id', 'nazv'));
            break;
        case 2:  // финансовый отдел
            echo $form->field($model, 'status')->dropDownList(
                ArrayHelper::map(status_sch::find()->where('id=:status1',[':status1' => 2])->
                orwhere('id=:status2',[':status2' => 3])->
                all(), 'id', 'nazv'));
                break;

        case 1:  // бухгалтерия
            echo $form->field($model, 'status')->dropDownList(
                ArrayHelper::map(status_sch::find()->where('id=:status1',[':status1' => 5])->
                orwhere('id=:status2',[':status2' => 7])->
                all(), 'id', 'nazv'));
            break;
        }
    ?>

    <?php
        if($model->status==5){
            if(!empty($model->act_work)) {
               echo $form->field($model, 'act_work')->textInput(['readonly' => true]);
               echo $form->field($model, 'date_akt')->textInput(['readonly' => true]);
           }
        }
            
    ?>

    <?php
    if($model->status==8){
            echo $form->field($model, 'why_refusal')->textInput();
    }
    ?>

        <table width="600px" class="table table-bordered ">
    <tr>
        <th width="30%">
             <?= Html::encode("Виконавча служба") ?>
        </th> 
        <th width="30%">
             <?= Html::encode("Відповідальна особа") ?>
        </th> 
        <th width="10%">
             <?= Html::encode("Моб. телефон") ?>
        </th> 
        <th width="10%">
             <?= Html::encode("Міський телефон") ?>
        </th>
        <th width="10%">
             <?= Html::encode("Внутр. телефон") ?>
        </th>
        <th width="10%">
             <?= Html::encode("Доп. телефон") ?>
        </th>
     </tr> 
     <?php
        $y = count($data_koord);
        for($i=0;$i<$y;$i++) {
     ?>
     <tr>
    
     <td>
         <?= Html::encode($data_koord[$i]->nazv) ?>
     </td>
     <td>
         <?= Html::encode($data_koord[$i]->name_koord) ?>
     </td>
     <td>
         <?= Html::encode($data_koord[$i]->tel_mobile) ?>
     </td>
     <td>
         <?= Html::encode($data_koord[$i]->tel_town) ?>
     </td>
     <td>
         <?= Html::encode($data_koord[$i]->tel) ?>
     </td>
     <td>
         <?= Html::encode($data_koord[$i]->tel_dop) ?>
     </td>
     
     </tr>
     <?php
        }
     ?>
    </table>  
        
    <?= $form->field($model, 'okpo')->textInput() ?>
    <?= $form->field($model, 'inn')->textInput() ?>
    <?= $form->field($model, 'regsvid')->textInput() ?>
    <?= $form->field($model, 'nazv')->textarea() ?>

    <?php if($model->priz_nds==0): $model->plat_yesno='ні';?>

        <?= $form->field($model, 'plat_yesno')->textInput(['readonly' => true]) ?>
    <?php endif; ?>
    <?php if($model->priz_nds==1): $model->plat_yesno='так';?>
        <?= $form->field($model, 'plat_yesno')->textInput(['readonly' => true]) ?>
    <?php endif; ?>

    <?= $form->field($model, 'tel',
            ['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-phone"></span></span>{input}</div>'] )->textInput() ?>
    <?= $form->field($model, 'addr')->textarea() ?>
    <?= $form->field($model, 'email',
            ['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-envelope"></span></span>{input}</div>'])->textInput() ?>
    <?= $form->field($model, 'comment')->textarea() ?>
    <?= $form->field($model, 'schet')->textInput() ?>
    <?= $form->field($model, 'contract')->textInput() ?>
    <?= $form->field($model, 'usluga')->textarea(['rows' => 3, 'cols' => 25]) ?>

    <?= $form->field($model, 'summa')->textInput() ?>
    <?= $form->field($model, 'summa_beznds')->textInput() ?>
    <?= $form->field($model, 'summa_work')->textInput() ?>
    <?= $form->field($model, 'summa_delivery')->textInput() ?>
    <?= $form->field($model, 'summa_transport')->textInput() ?>
    <?= $form->field($model, 'adres')->textarea(['onDblClick' => 'rmenu($(this).val(),"#viewschet-adres")']) ?>
           <div class='rmenu' id='rmenu-viewschet-adres'></div>
 <!--    -->   <?//= $form->field($model, 'res')->textInput() ?>
<!--    --><?//= $form->field($model, 'date_z')->textInput() ?>

        <? if($model->status>1): ?>
            <?= $form->field($model, 'date_opl')->
            widget(\yii\jui\DatePicker::classname(), [
                'language' => 'uk'
            ]) ?>
        <? endif;?>

        <?= $form->field($model, 'date_z')->
        widget(\yii\jui\DatePicker::classname(), [
            'language' => 'uk'
        ]) ?>

        <? if($model->status>1): ?>
        <?= $form->field($model, 'date_exec')->
        widget(\yii\jui\DatePicker::classname(), [
            'language' => 'uk'
        ]) ?>
        <? endif;?>

    <?= $form->field($model, 'date',
                    ['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-calendar"></span></span>{input}</div>'])->textInput() ?>

    <?= $form->field($model, 'time',
                    ['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-time"></span></span>{input}</div>'])->textInput() ?>

    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'ОК' : 'OK', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
         <?php
        if($model->status>1){ ?>
        <?= Html::a('Сформувати рахунок',['site/opl'], [
            'data' => [
                'method' => 'post',
                'params' => [
                    'sch' => $nazv,
                ],
            ],'class' => 'btn btn-info']); ?>
       <?php } ?> 
        
    <?php
        if($model->status==5){
            if(!empty($model->act_work)) { ?>    
    <?= Html::a('Акт виконаних робіт',['site/act_work'], [
            'data' => [
                'method' => 'post',
                'params' => [
                    'sch' => $nazv,
                    'mail'=> $mail
                ],
            ],'class' => 'btn btn-info']); ?>

                <?= Html::a('Договір',['site/contract'], [
                    'data' => [
                        'method' => 'post',
                        'params' => [
                            'sch' => $nazv,
                            'mail'=> $mail
                        ],
                    ],'class' => 'btn btn-info']); ?>
         
                <?= Html::a('Повідом.',['site/message'], [
                    'data' => [
                        'method' => 'post',
                        'params' => [
                            'sch' => $nazv,
                            'mail'=> $mail
                        ],
                    ],'class' => 'btn btn-info']); ?>
        <?php }} ?>
        
    </div>

    <?php ActiveForm::end(); ?>
    </div>
</div>


