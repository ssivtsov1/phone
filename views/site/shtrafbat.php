<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\SerialColumn;

$this->title = "Список працівників, які перевисили ліміт по мобільному зв'язку за попередній місяць.";
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-spr">
    <h4><?= Html::encode($this->title) ?></h4>
    <p class="text-danger">
        <?= Html::encode("Для перевірки залишку бонусних хвилин потрібно нажати *112# або *112*1# (в залежності від пакету).") ?> 
    </p>
   
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => false,
        'emptyText' => 'Нічого не знайдено',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'year',
            //'month',
            ['attribute' =>'month',
                'value' => function ($model){
                    $q = $model->month;

                    switch ($q){
                         case 1:
                            return 'Січень';
                            break;
                         case 2:
                            return 'Лютий';
                            break;   
                         case 3:
                            return 'Березень';
                            break;
                         case 4:
                            return 'Квітень';
                            break;
                         case 5:
                            return 'Травень';
                            break;
                         case 6:
                            return 'Червень';
                            break;
                         case 7:
                            return 'Липень';
                            break;
                         case 8:
                            return 'Серпень';
                            break;
                         case 9:
                            return 'Вересень';
                            break;
                         case 10:
                            return 'Жовтень';
                            break;
                         case 11:
                            return 'Листопад';
                            break;
                         case 12:
                            return 'Грудень';
                            break;
                         default:
                             return '';
                                       
                    }

//                    if(strlen($q)==9) $q = '0'.$q;
//                    return tel_normal($q);
                },
                'filter'=>array(1 => 'Січень',
                        2 => 'Лютий',
                        3 => 'Березень',
                        4 => 'Квітень',
                        5 => 'Травень',
                        6 => 'Червень',
                        7 => 'Липень',
                        8 => 'Серпень',
                        9 => 'Вересень',
                        10 => 'Жовтень',
                        11 => 'Листопад',
                        12 => 'Грудень',
                       ),        
                'format' => 'raw'
            ],
            'fio',
            ['attribute' =>'tel',
                'value' => function ($model){
                    $q = trim($model->tel);

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
            'main_unit',
            'shtraf',
        ],
    ]); ?>
    
</div>



