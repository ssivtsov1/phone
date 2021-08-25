<script>

window.addEventListener('load', function () {

    var tableOffset = $(".table").offset().top;
    var $header = $(".table > thead").clone();
    var $body = $(".table > tbody").clone();
    var $fixedHeader = $("#header-fixed").append($header);
//var $fixedtab = $("#header-fixed").append($body);
    $("#header-fixed").width($(".table").width());


    $(window).bind("scroll", function () {
       var offset = $(this).scrollTop();

        if (offset >= tableOffset && $fixedHeader.is(":hidden")) {

            $fixedHeader.show();
        }
        else if (offset < tableOffset) {
            $fixedHeader.hide();
        }

        $("#header-fixed th").each(function (index) {
            var index2 = index;
            $(this).width(function (index2) {
                return $(".table th").eq(index).width();
            });
        });
    });



        $("img").click(function(){	// Событие клика на маленькое изображение

            var img = $(this);	// Получаем изображение, на которое кликнули
            var src = img.attr('src'); // Достаем из этого изображения путь до картинки
            var p=$(this).position();

            $("body").append("<div class='popup'>"+ //Добавляем в тело документа разметку всплывающего окна
                "<div class='popup_bg'></div>"+ // Блок, который будет служить фоном затемненным
                "<img src='"+src+"' class='popup_img' />"+ // Са мо увеличенное фото
                "</div>");
            $(".popup").css("top", p.top );
            $(".popup").fadeIn(600); // Медленно выводим изображение
            $(".popup_bg").click(function(){	// Событие клика на затемненный фон
                $(".popup").fadeOut(600);	// Медленно убираем всплывающее окно
                setTimeout(function() {	// Выставляем таймер
                    $(".popup").remove(); // Удаляем разметку всплывающего окна
                }, 600);
            });
        });



});
</script>



<?php


use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\grid\SerialColumn;

$this->title = 'Телефонний довідник';
//debug($vip);
$flag_like = 0;
if($kol==0){
    $zag = '';

    if(isset($closest[0])){
    if($closest[0]<>''){
        $like_surname = '';
        $flag_like = 1;
        
        foreach ($closest as $v) {
            if(!empty($v))
             $like_surname .= trim($v) . ', ';
        }
            $zag = mb_substr($like_surname,0, mb_strlen($like_surname)-2,'UTF-8');
    }
    }
}
else
    $zag = 'Всього знайдено: '.$kol;
//$this->params['breadcrumbs'][] = $this->title;

?>
<?//= Html::a('Добавити', ['createtransp'], ['class' => 'btn btn-success']) ?>
<div class="site-spr">
    <?php if($flag_like==0): ?>
        <h5><?= Html::encode($zag) ?></h5>
    <?php endif; ?>    
     
    <?php if($flag_like==1): ?>
        <h5><?= Html::encode('Можливо Ви мали на увазі: ') ?> <span class="flag_like"><?= Html::encode($zag) ?></span></h5>
    <?php endif; ?> 
        
    <?php if(!isset(Yii::$app->user->identity->role)) { ?>
    <?php if($vip<>1) { ?>
    <?php if($closest[10]<>'q') { ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                'layout' => "{items}",
                'summary' => false,
                'emptyText' => 'Нічого не знайдено',
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'format' => 'raw',
                        'header' => 'Відділ',
                        'value' => function ($model) use ($vip) {
                            return \yii\helpers\Html::a('<span class="glyphicon glyphicon-list"></span>',
                                ['site/department?main_unit=' . $model->main_unit .
                                    '&unit_1=' . $model->unit_1 . '&unit_2=' . $model->unit_2. '&vip=' . $vip
                                ],
                                ['title' => Yii::t('yii', 'Відобразити всіх працівників відділу'), 'data-pjax' => '0']
                            );
                        }
                    ],
                    'fio',
                    [
                        'attribute' => 'Фото',
                        'format' => 'html',
                        'value' => function ($data) {
                            if(!empty($data['photo']))
                                return Html::img('photo/'. $data['photo'],
                                    ['width' => '75px',' -moz-border-radius' => '10px']);
                            else
                                return '';
                        },
                    ],
                    'post',
                    ['attribute' => 'tel_mob',
                        'value' => function ($model) {
                            $q = trim($model->tel_mob);
//                    $pos = strpos($q,',');
//                    if($pos>0)
//                    {
//                        $q1 = substr($q,0,$pos);
//                        $q2 = substr($q,$pos);
//                        $q1 = only_digit1($q1);
//                        $q2 = only_digit1($q2);
//                        if(strlen($q)==9) $q = '0'.$q;
//                    }
                            $tels = explode(',', $q);
                            $s = '';
                            $i = 0;
                            foreach ($tels as $t) {
                                $i++;
                                $q = only_digit($t);
                                if (strlen($q) == 9) $q = '0' . $q;
                                $q = tel_normal($q);
                                if ($i > 1)
                                    $s .= ',' . chr(13) . $q;
                                else
                                    $s = $q;

                            }
                            return $s;
                        },
                        'format' => 'raw'
                    ],
                    'tel',
                    ['attribute' => 'tel_town',
                        'value' => function ($model) {
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
                ],
            ]);
        }  ?>

    <?php if($closest[10]=='q') { ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'layout'=>"{items}",
        'summary' => false,
        'emptyText' => 'Нічого не знайдено',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'fio',
            [
                'attribute' => 'Фото',
                'format' => 'html',
                'value' => function ($data) {
                    if(!empty($data['photo']))
                        return Html::img('photo/'. $data['photo'],
                            ['width' => '75px',' -moz-border-radius' => '10px']);
                    else
                        return '';
                },
            ],
            'post',
            ['attribute' =>'tel_mob',
                'value' => function ($model){
                    $q = trim($model->tel_mob);
//                    $pos = strpos($q,',');
//                    if($pos>0)
//                    {
//                        $q1 = substr($q,0,$pos);
//                        $q2 = substr($q,$pos);
//                        $q1 = only_digit1($q1);
//                        $q2 = only_digit1($q2);
//                        if(strlen($q)==9) $q = '0'.$q;
//                    }
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
        ],
    ]);
    }}}  ?>

    <?php if(!isset(Yii::$app->user->identity->role)) { ?>
        <?php if($vip==1) { ?>
            <?php if($closest[10]<>'q') { ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    //'filterModel' => $searchModel,
                    'layout' => "{items}",
                    'summary' => false,
                    'emptyText' => 'Нічого не знайдено',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'format' => 'raw',
                            'header' => 'Відділ',
                            'value' => function ($model) use($vip) {
                                return \yii\helpers\Html::a('<span class="glyphicon glyphicon-list"></span>',
                                    ['site/department?main_unit=' . $model->main_unit .
                                        '&unit_1=' . $model->unit_1 . '&unit_2=' . $model->unit_2. '&vip=' . $vip
                                    ],
                                    ['title' => Yii::t('yii', 'Відобразити всіх працівників відділу'), 'data-pjax' => '0']
                                );
                            }
                        ],
                        'fio',
                        [
                            'attribute' => 'Фото',
                            'format' => 'html',
                            'value' => function ($data) {
                                if(!empty($data['photo']))
                                    return Html::img('photo/'. $data['photo'],
                                        ['width' => '75px',' -moz-border-radius' => '10px']);
                                else
                                    return '';
                            },
                        ],
                        'post',
                        ['attribute' => 'tel_mob',
                            'value' => function ($model) {
                                $q = trim($model->tel_mob);
//                    $pos = strpos($q,',');
//                    if($pos>0)
//                    {
//                        $q1 = substr($q,0,$pos);
//                        $q2 = substr($q,$pos);
//                        $q1 = only_digit1($q1);
//                        $q2 = only_digit1($q2);
//                        if(strlen($q)==9) $q = '0'.$q;
//                    }
                                $tels = explode(',', $q);
                                $s = '';
                                $i = 0;
                                foreach ($tels as $t) {
                                    $i++;
                                    $q = only_digit($t);
                                    if (strlen($q) == 9) $q = '0' . $q;
                                    $q = tel_normal($q);
                                    if ($i > 1)
                                        $s .= ',' . chr(13) . $q;
                                    else
                                        $s = $q;

                                }
                                return $s;
                            },
                            'format' => 'raw'
                        ],
                      'tel_private',
                      'tel',
                        ['attribute' => 'tel_town',
                            'value' => function ($model) {
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
                    ],
                ]);
            }  ?>

            <?php if($closest[10]=='q') { ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    //'filterModel' => $searchModel,
                    'layout'=>"{items}",
                    'summary' => false,
                    'emptyText' => 'Нічого не знайдено',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        'fio',
                        [
                            'attribute' => 'Фото',
                            'format' => 'html',
                            'value' => function ($data) {
                                if(!empty($data['photo']))
                                    return Html::img('photo/'. $data['photo'],
                                        ['width' => '75px',' -moz-border-radius' => '10px']);
                                else
                                    return '';
                            },
                        ],
                        'post',
                        ['attribute' =>'tel_mob',
                            'value' => function ($model){
                                $q = trim($model->tel_mob);
//                    $pos = strpos($q,',');
//                    if($pos>0)
//                    {
//                        $q1 = substr($q,0,$pos);
//                        $q2 = substr($q,$pos);
//                        $q1 = only_digit1($q1);
//                        $q2 = only_digit1($q2);
//                        if(strlen($q)==9) $q = '0'.$q;
//                    }
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
                            },
                            'format' => 'raw'
                        ],
                        'tel_private',
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
                    ],
                ]);
            }}}  ?>



<?php if(isset(Yii::$app->user->identity->role)) { ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'layout'=>"{items}",
        'summary' => false,
        'emptyText' => 'Нічого не знайдено',
        'columns' => [
            
             [
                /**
                 * Указываем класс колонки
                 */
                'class' => \yii\grid\ActionColumn::class,
                 'buttons'=>[
                  'delete'=>function ($url, $model) use ($sql) {
                        $customurl=Yii::$app->getUrlManager()->createUrl(['/site/delete',
                            'id'=>$model['id'],'mod'=>'viewphone','sql'=>$sql]); //$model->id для AR
                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-remove-circle"></span>', $customurl,
                                                ['title' => Yii::t('yii', 'Видалити'),'data' => [
                                                'confirm' => 'Ви впевнені, що хочете видалити цей запис ?',
                                                ], 'data-pjax' => '0']);
                  },

                  'update'=>function ($url, $model) use ($sql) {
                        $customurl=Yii::$app->getUrlManager()->createUrl(['/site/update',
                            'id'=>$model['id'],'mod'=>'viewphone','sql'=>$sql]); //$model->id для AR
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
            [
                'attribute' => 'Фото',
                'format' => 'html',
                'value' => function ($data) {
                 if(!empty($data['photo']))
                    return Html::img('photo/'. $data['photo'],
                        ['width' => '75px',' -moz-border-radius' => '10px']);
                  else
                    return '';
                },
            ],
            'post',
            ['attribute' =>'tel_mob',
                'value' => function ($model){
                    $q = trim($model->tel_mob);
//                    $pos = strpos($q,',');
//                    if($pos>0)
//                    {
//                        $q1 = substr($q,0,$pos);
//                        $q2 = substr($q,$pos);
//                        $q1 = only_digit1($q1);
//                        $q2 = only_digit1($q2);
//                        if(strlen($q)==9) $q = '0'.$q;
//                    }
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
                },
                'format' => 'raw'
            ],
            'nazv',
            'rate',
            'type_tel',            
            'tel',
                       
            ['attribute' =>'tel_town',
                'value' => function ($model){
                    $q = trim($model->tel_town);
                    return tel_normal($q);
                },
                'format' => 'raw'
            ],
            'line',
            'phone_type',
            'main_unit',
            'unit_1',
            'unit_2',
            'email',
            'email_group',

                        
            
       ],
]); 
 
}    ?>

    <table id="header-fixed"></table>

</div>



