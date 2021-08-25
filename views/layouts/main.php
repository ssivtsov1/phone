<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\web\Request;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>

<script>
    window.addEventListener('load', function(){
    //     $('.logo_site').dblclick(function () {
    //       alert(111);
    //     });
    //
    // });
    //     localStorage.setItem('vip', '0');

        var vip = localStorage.getItem('vip');
        // alert(vip);
        $('#inputdata-vip').val(vip);
        if(vip==1) {
            $('.navbar-default').css('background-color', '#0f4c81');
            $('.hero_area').css('background-image', 'url("' + '../kn_base7.jpg' + '")');
        }
        else {
            $('.navbar-default').css('background-color', '#14837D');
            $('.hero_area').css('background-image',  'url("' + '../electric.jpg' + '")');
        }


        function makeDoubleRightClickHandler( handler ) {
            var timeout = 0, clicked = false;
            return function(e) {

                e.preventDefault();

                if( clicked ) {
                    clearTimeout(timeout);
                    clicked = false;
                    return handler.apply( this, arguments );
                }
                else {
                    clicked = true;
                    timeout = setTimeout( function() {
                        clicked = false;
                    }, 300 );
                }
            };
        }

        $('.logo_site').contextmenu( makeDoubleRightClickHandler( function(e) {
            // alert(111);
            localStorage.setItem('secret_way1', '1');
            var p2=localStorage.getItem('secret_way2');
            if(p2=='1') localStorage.setItem('secret_way2', '0');
            localStorage.setItem('vip', '0');
        }));
        $('.hero_area').contextmenu( makeDoubleRightClickHandler( function(e) {
            // alert(111);
            localStorage.setItem('secret_way2', '1');
            var p1=localStorage.getItem('secret_way1');
            var p2=localStorage.getItem('secret_way2');
            if(((p1+p2) == '11')) {

                    localStorage.setItem('vip', '1');
                    $('#inputdata-vip').val(1);
                    alert('Увага! Включено розширений режим довідника.')
                    $('.navbar-default').css('background-color', '#0f4c81');
                    // 'url(../kn_base16.jpg)'
                    $('.hero_area').css('background-image', 'url("' + '../kn_base7.jpg' + '")');
                    // $('section').removeClass('hero-area').addClass('hero-area-secret');
                    $('#btn_exit').show();
                    localStorage.setItem('secret_way1', '0');
                    localStorage.setItem('secret_way2', '0');

            }
        }));


    });

</script>


<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>
    <div class="wrap">
        <?php


        $flag = 1;
        $main = 0;

         if(!isset(Yii::$app->user->identity->id_res))
                $flag=0;
         else
             $flag = Yii::$app->user->identity->id_res;

                 

       // die;
        if(isset(Yii::$app->user->identity->role)) {
                $adm = Yii::$app->user->identity->role;
                if ($adm==3)
                {
                    $main=1;
                    $this->params['admin'][] = "Режим адміністратора: ";
                }
                else
                    $this->params['admin'][] = "Режим користувача: ";
         }

       
        if(!isset(Yii::$app->user->identity->role))
            $main=2;
      
        if($main!=1)    
        NavBar::begin([
                'brandLabel' => 'Телефонний довідник',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    //'class' => 'navbar-inverse navbar-fixed-top',
                    'class' => 'navbar-default navbar-fixed-top',
                    
                ],
            ]);
        else
          NavBar::begin([
                'brandLabel' => 'Телефонний довідник (режим адміністратора)',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    //'class' => 'navbar-inverse navbar-fixed-top',
                    'class' => 'navbar-default navbar-fixed-top',
                    
                ],
            ]);  


       
       
            switch ($main) {
           

            case 1:
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => [
                        ['label' => Html::tag('span',' Головна',['class' => 'glyphicon glyphicon-home']) ,
                            'url' => ['/site/index']],
                        
                        ['label' => 'Працівники', 'url' => ['/site/employees']],
                        ['label' => 'Книга скарг та пропозицій', 'url' => 'http://192.168.55.1/proffer'],
                        ['label' => 'Про сайт', 'url' => ['/site/about']],
                        ['label' => Html::tag('span',' Вийти',['class' => 'glyphicon glyphicon-log-out']),
                             'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']],
                        
                    ],
                    'encodeLabels' => false,
                ]);
                break;
            case 0:
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => [
                       ['label' => 'Головна', 'url' => ['/site/index']],
                        
                        ['label' => 'Працівники', 'url' => ['/site/employees']],
                        ['label' => 'Книга скарг та пропозицій', 'url' => 'http://192.168.55.1/proffer'],
                        ['label' => 'Про сайт', 'url' => ['/site/about']],
                        //['label' => 'Вийти', 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']],
                        /*
                        Yii::$app->user->isGuest ?
                            ['label' => 'Login', 'url' => ['/site/login']] :
                            ['label' => 'Logout (' . Yii::$app->user->identity->username . ')',
                                'url' => ['/site/logout'],
                                'linkOptions' => ['data-method' => 'post']],
                         *
                         */
                    ],
                ]);
                break;
            case 2:
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => [
                     
                        ['label' => Html::tag('span',' Головна',['class' => 'glyphicon glyphicon-home']) ,
                            'url' => ['/site/index']],
                        
                        ['label' => 'Працівники', 'url' => ['/site/employees']],
                        ['label' => 'Інші телефони', 'url' => ['/site/others'],
                            'options' => ['id' => 'down_menu'],
                            'items' =>
                                [
                                    ['label' => 'Вінниця', 'url' => ['/site/tel_vi']],

                                ],
                        ],
                        ['label' => 'Книга скарг та пропозицій', 'url' => 'http://192.168.55.1/proffer'],
                        ['label' => 'Про сайт', 'url' => ['/site/about']],
                        
                        //['label' => 'Вийти', 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']],
                        /*
                        Yii::$app->user->isGuest ?
                            ['label' => 'Login', 'url' => ['/site/login']] :
                            ['label' => 'Logout (' . Yii::$app->user->identity->username . ')',
                                'url' => ['/site/logout'],
                                'linkOptions' => ['data-method' => 'post']],
                         *
                         */
                    ],
                    'encodeLabels' => false,
                ]);
                break;
        }
            NavBar::end();
        ?>


        <!--Вывод логотипа-->
        <?php
        $session = Yii::$app->session;
        $session->open();
        if($session->has('view'))
            $view = $session->get('view');
        else
            $view = 0;
        if(!$view){
        ?>
        <? if(!strpos(Yii::$app->request->url,'/cek')): ?>
       
        <? if(strlen(Yii::$app->request->url)==10): ?>
        <img class="logo_site" src="web/Logo.png" alt="ЦЕК" />
        <? endif; ?>

        <? if(strlen(Yii::$app->request->url)<>10): ?>
            <img class="logo_site" src="../Logo.png" alt="ЦЕК" />
        <? endif; ?>
        <? endif; ?>

        <? if(strpos(Yii::$app->request->url,'/cek')): ?>
            <? if(strlen(Yii::$app->request->url)==10): ?>
                <img class="logo_site" src="web/Logo.png" alt="ЦЕК" />
            <? endif; ?>

            <? if(strlen(Yii::$app->request->url)<>10): ?>
                <img class="logo_site" src="../Logo.png" alt="ЦЕК" />
            <? endif; ?>
        <? endif; }?>


        <div class="container">

            <div class="page-header">
                <small class="text-info">
                    <?php
                   
                    if(isset($this->params['admin'] ))
                        if(isset($this->params['res'] ))
                        //echo $this->params['admin'][0] . ' '. $this->params['res'][0];
                           // echo $main;
                    ?>
                    </small>

            </div>

            <?= Breadcrumbs::widget([
                'homeLink' => ['label' => 'Головна', 'url' => '/phone'],
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
             
            <?= $content ?>
            
            
            
        </div>
        <section class="hero_area">
        </section>  

    </div>
 
    <footer class="footer">
        
        <div id="container_footer" class="container">
            <p class="pull-left">&copy; ЦЕК <?= date('Y') ?> &nbsp &nbsp
            <?= Html::a('Головна',["index"],['class' => 'a_main']); ?> &nbsp &nbsp
            <?= Html::a("<a class='a_main' href='http://cek.dp.ua'>сайт ПрАТ ПЕЕМ ЦЕК</a>"); ?>
            </p>
            <p class="pull-right">
            <img class='footer_img' src="../Logo.png">
            </p>
            <?php
                $day = date('j');
                $month = date('n');
                $day_week = date('w');
                switch ($day_week)  {
                    case 0: 
                        $dw = 'нед.';
                        break;
                    case 1: 
                        $dw = 'пон.';
                        break;
                    case 2: 
                        $dw = 'вівт.';
                        break;
                    case 3: 
                        $dw = 'середа';
                        break;
                    case 4: 
                        $dw = 'четв.';
                        break;
                    case 5: 
                        $dw = 'п’ятн.';
                        break;
                    case 6: 
                        $dw = 'суб.';
                        break;
                    
                }    
                $day = $day.' '.$dw;
            ?>
            
            <table width="100%" class="table table-condensed" id="calendar_footer">
            <tr>
                <th width="8.33%">
                    <?php
                    if($month==1) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                   
                </th> 
                <th width="8.33%">
                    <?php
                    if($month==2) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th> 
                <th width="8.33%">
                   <?php
                    if($month==3) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th> 
                <th width="8.33%">
                    <?php
                    if($month==4) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th>
                <th width="8.33%">
                    <?php
                    if($month==5) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th>
                <th width="8.33%">
                    <?php
                    if($month==6) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th>
                <th width="8.33%">
                    <?php
                    if($month==7) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th>
                <th width="8.33%">
                    <?php
                    if($month==8) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th>
                <th width="8.33%">
                    <?php
                    if($month==9) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th>
                <th width="8.33%">
                     <?php
                    if($month==10) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th>
                <th width="8.33%">
                     <?php
                    if($month==11) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th>
                <th width="8.33%">
                     <?php
                    if($month==12) echo '<div id="on_ceil">'.$day.'</div>';
                    ?>
                </th>
                </tr>
                <tr>
                    
                <td>   
                     <?= Html::encode("січень") ?>
                </td> 
                <td>
                     <?= Html::encode("лютий") ?>
                </td> 
                <td>
                     <?= Html::encode("березень") ?>
                </td> 
                <td>
                     <?= Html::encode("квітень") ?>
                </td>
                <td>
                     <?= Html::encode("травень") ?>
                </td>
                <td>
                     <?= Html::encode("червень") ?>
                </td>
                <td>
                     <?= Html::encode("липень") ?>
                </td>
                <td>
                     <?= Html::encode("серпень") ?>
                </td>
                <td>
                     <?= Html::encode("вересень") ?>
                </td>
                <td>
                     <?= Html::encode("жовтень") ?>
                </td>
                <td >
                     <?= Html::encode("листопад") ?>
                </td>
                <td>
                     <?= Html::encode("грудень") ?>
                </td>
               </tr>

                
            </table>  
            
        </div>
    </footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
