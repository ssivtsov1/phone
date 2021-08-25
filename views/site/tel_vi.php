<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\SerialColumn;

$this->title = 'Працівники Вінниці';

?>
<div class="site-spr">
    <h4><?= Html::encode($this->title) ?></h4>
    <?php if(!isset(Yii::$app->user->identity->role)) { ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'emptyText' => 'Нічого не знайдено',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            ['attribute' =>'fio',
                'label' => "",
                'encodeLabel' => false
            ],
            ['attribute' =>'post',
                'label' => "",
                'encodeLabel' => false
            ],
            ['attribute' =>'tel',
                'label' => "Внутр. номер",
                'encodeLabel' => false
            ],
            ['attribute' =>'tel_town',
                'label' => "Міський номер",
                'encodeLabel' => false
            ],
                        
    ],
    ]); }?>

    
</div>



