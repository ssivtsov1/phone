<?php


use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\SerialColumn;

$this->title = 'Телефонний довідник працівників';
//$this->params['breadcrumbs'][] = $this->title;
?>
<!--<?//= Html::a('Добавити', ['createtransp'], ['class' => 'btn btn-success']) ?>-->
<div class="site-spr">
    <h4><?= Html::encode($this->title) ?></h4>
    <?php if(!isset(Yii::$app->user->identity->role)) { ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => false,
        'emptyText' => 'Нічого не знайдено',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
 
//            'tab_nom',
            'fio',
            'post',
            ['attribute' =>'tel_mob',
                'value' => function ($model){
                    $q = trim($model->tel_mob);

                    $tels = explode(',',$q);
                    $s = '';
                    $i = 0;
                    foreach ($tels as $t) {
                        $i++;
                        $q = only_digit($t);
                        if (strlen($q) == 9) $q = '0' . $q;
                        $q = tel_normal($q);
                        if($i>1)
                            $s.=','.chr(13).$q;
                        else
                            $s=$q;

                    }
                    return $s;

//                    if(strlen($q)==9) $q = '0'.$q;
//                    return tel_normal($q);
                },
                'format' => 'raw'
            ],
            'tel',
            ['attribute' =>'tel_town',
                'value' => function ($model){
                    $q = trim($model->tel_town);
                    return tel_normal($q);
                },
                'format' => 'raw'
            ],
            'main_unit',
            'unit_1',
            'unit_2',
            'email',
            'email_group'
                        
//             [
//                /**
//                 * Указываем класс колонки
//                 */
//                'class' => \yii\grid\ActionColumn::class,
//                 'buttons'=>[
//                  'delete'=>function ($url, $model) {
//                        $customurl=Yii::$app->getUrlManager()->createUrl(['/sprav/delete','id'=>$model['id'],'mod'=>'sprtransp']); //$model->id для AR
//                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-remove-circle"></span>', $customurl,
//                                                ['title' => Yii::t('yii', 'Видалити'),'data' => [
//                                                'confirm' => 'Ви впевнені, що хочете видалити цей запис ?',
//                                                ], 'data-pjax' => '0']);
//                  },
//                  
//                  'update'=>function ($url, $model) {
//                        $customurl=Yii::$app->getUrlManager()->createUrl(['/sprav/update','id'=>$model['id'],'mod'=>'sprtransp']); //$model->id для AR
//                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-pencil"></span>', $customurl,
//                                                ['title' => Yii::t('yii', 'Редагувати'), 'data-pjax' => '0']);
//                  }
//                ],
//                /**
//                 * Определяем набор кнопочек. По умолчанию {view} {update} {delete}
//                 */
//                'template' => '{update} {delete}',
//            ],
        ],
    ]); }?>

<?php if(isset(Yii::$app->user->identity->role)) { ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => false,
        'emptyText' => 'Нічого не знайдено',
        'columns' => [
             [
                /**
                 * Указываем класс колонки
                 */
                'class' => \yii\grid\ActionColumn::class,
                 'buttons'=>[
                  'delete'=>function ($url, $model) {
                        $customurl=Yii::$app->getUrlManager()->createUrl(['/site/delete_emp',
                            'id'=>$model['id'],'mod'=>'viewphone']); //$model->id для AR
                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-remove-circle"></span>', $customurl,
                                                ['title' => Yii::t('yii', 'Видалити'),'data' => [
                                                'confirm' => 'Ви впевнені, що хочете видалити цей запис ?',
                                                ], 'data-pjax' => '0']);
                  },

                  'update'=>function ($url, $model) {
                        $customurl=Yii::$app->getUrlManager()->createUrl(['/site/update_emp',
                            'id'=>$model['id'],'mod'=>'viewphone']); //$model->id для AR
                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-pencil"></span>', $customurl,
                                                ['title' => Yii::t('yii', 'Редагувати'), 'data-pjax' => '0']);
                  }
                ],
                /**
                 * Определяем набор кнопочек. По умолчанию {view} {update} {delete}
                 */
                'template' => '{update} {delete}',
            ],
            ['class' => 'yii\grid\SerialColumn'],
 
            'tab_nom',
            'fio',
            'post',
            ['attribute' =>'tel_mob',
                'value' => function ($model){
                    $q = trim($model->tel_mob);

                    $tels = explode(',',$q);
                    $s = '';
                    $i = 0;
                    foreach ($tels as $t) {
                        $i++;
                        $q = only_digit($t);
                        if (strlen($q) == 9) $q = '0' . $q;
                        $q = tel_normal($q);
                        if($i>1)
                            $s.=','.chr(13).$q;
                        else
                            $s=$q;

                    }
                    return $s;
//                    if(strlen($q)==9) $q = '0'.$q;
//                    return tel_normal($q);
                },
                'format' => 'raw'
            ],
            'tel',
            ['attribute' =>'tel_town',
                'value' => function ($model){
                    $q = trim($model->tel_town);
                    return tel_normal($q);
                },
                'format' => 'raw'
            ],
            'nazv',
            'rate',
            'type_tel',   
             'line',
            'phone_type',           
            'main_unit',
            'unit_1',
            'unit_2',
            'email',
            'email_group'
                        
//             [
//                /**
//                 * Указываем класс колонки
//                 */
//                'class' => \yii\grid\ActionColumn::class,
//                 'buttons'=>[
//                  'delete'=>function ($url, $model) {
//                        $customurl=Yii::$app->getUrlManager()->createUrl(['/sprav/delete','id'=>$model['id'],'mod'=>'sprtransp']); //$model->id для AR
//                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-remove-circle"></span>', $customurl,
//                                                ['title' => Yii::t('yii', 'Видалити'),'data' => [
//                                                'confirm' => 'Ви впевнені, що хочете видалити цей запис ?',
//                                                ], 'data-pjax' => '0']);
//                  },
//                  
//                  'update'=>function ($url, $model) {
//                        $customurl=Yii::$app->getUrlManager()->createUrl(['/sprav/update','id'=>$model['id'],'mod'=>'sprtransp']); //$model->id для AR
//                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-pencil"></span>', $customurl,
//                                                ['title' => Yii::t('yii', 'Редагувати'), 'data-pjax' => '0']);
//                  }
//                ],
//                /**
//                 * Определяем набор кнопочек. По умолчанию {view} {update} {delete}
//                 */
//                'template' => '{update} {delete}',
//            ],
        ],
    ]); }?>

    
</div>



