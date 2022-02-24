<?php
// Ввод основных данных для поиска телефонов

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
$this->title = 'Телефонний довідник (ЦЄК)';
?>

<script>
     window.addEventListener('load', function(){


         var vip = localStorage.getItem('vip');
         $('#inputdata-vip').val(vip);
         if(vip==1) {
             $('.navbar-default').css('background-color', '#0f4c81');
             $('.hero_area').css('background-image',  'url("' + '../kn_base7.jpg' + '")');
             $('#btn_exit').show();
         }
         else {
             $('.navbar-default').css('background-color', '#14837D');
             $('.hero_area').css('background-image',  'url("' + '../electric.jpg' + '")');
             $('#btn_exit').hide();
         }


       $('#inputdata-id_t').each(function () {
        var txt = $(this).text()
        $(this).html(
            "<span style='color:#111111" + ";'></span>" + txt)
});
         $('#inputdata-id_p').each(function () {
             var txt1 = $(this).text()
             $(this).html(
                 "<span style='color:#111111" + ";'></span>" + txt1)
         })
    });

     // $('#MeteoInformerWrap').removeClass('constructor__metlink');

     // window.onload=function(){
     //     var element = document.getElementById("MeteoInformerWrap").classList;
     //     // alert(element);
     //     element.remove('constructor__metlink');
     //
     // };

</script>



<div class="site-login" <?php if(isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role==3) echo 'id="main_block"'; ?>>
    <h2><?= Html::encode('') ?></h2>
      <div class="row">
         
          <?php //debug(Yii::$app->user->identity); ?> 
          
        <div <?php if(isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role==3) echo 'class="col-lg-8"'; else echo 'class="col-lg-6 tel_left_side"'; ?>>
            <?php $form = ActiveForm::begin(['id' => 'inputdata',
                'options' => [
                    'class' => 'form-horizontal col-lg-25',
                    'enctype' => 'multipart/form-data'
                    
                ]]); ?>

<!--            a.main_unit<>'Павлоградські РЕМ' and-->
            <?=$form->field($model, 'main_unit')->dropDownList(
                    ArrayHelper::map(app\models\employees::findbysql(
                            "select 1630 as id_name,0 as id,null as nazv,'Всі підрозділи' as main_unit,-1 as ssort
                                union
                                select min(a.id_name) as id_name,b.id,b.nazv,a.main_unit,
                                case when b.id is null and LOCATE('РЕМ', a.main_unit)=0 then 0
                                when LOCATE('РЕМ', a.main_unit)>0 then 1 end as ssort 
                                from 1c a 
                                left join spr_res b on a.main_unit = b.nazv
                                where  id_name is not null 
                                group by b.id,b.nazv,a.main_unit
                                order by ssort,id_name")->all(), 'id_name', 'main_unit'),
            [
            'prompt' => 'Виберіть головний підрозділ','onchange' => '$.get("' . Url::to('/phone/web/site/getunit_1?id_name=') .
                '"+$(this).val(),
                    function(data) {
                         $("#inputdata-unit_1").empty();
                         $("#inputdata-unit_2").empty();
   
                         
                         localStorage.setItem("main_unit", data.main_unit);
                         for(var i = 0; i<data.unit.length; i++) {
                         var q = data.unit[i].unit_1;
                         if(q==null) continue;
                         var q1 = q.substr(4);
                        
                         var n = q.substr(0,4);
//                         alert(n); 
                         $("#inputdata-unit_1").append("<option value="+n+
                         " style="+String.fromCharCode(34)+"font-size: 14px;"+
                         String.fromCharCode(34)+">"+q1+"</option>");
                        } 
                         $("#inputdata-unit_1").change();
                  });',]); ?>

            <?=$form->field($model, 'unit_1')->
            dropDownList(ArrayHelper::map(
                app\models\employees::findbysql('
                select 1630 as id," Всі підрозділи" as unit_1
                union
                Select min(id) as id,unit_1 
                from 1c 
                where LENGTH(ltrim(rtrim(unit_1)))<>0
                 group by unit_1 
                 order by unit_1')
                    ->all(), 'id', 'unit_1'),
                ['prompt' => 'Виберіть підрозділ підпорядкований головному',
                    'onchange' => '$.get("' . Url::to('/phone/web/site/getunit?id=') .
                        '"+$(this).val()+"&main_unit="+localStorage.getItem("main_unit"),
                    function(data) {
                         $("#inputdata-unit_2").empty();
                         for(var i = 0; i<data.data.length; i++) {
                         var q = data.data[i].unit_2;
                         if(q==null) continue;
                         var q1 = q.substr(4);
                         var n = q.substr(0,4);
                         $("#inputdata-unit_2").append("<option value="+n+
                         " style="+String.fromCharCode(34)+"font-size: 14px;"+
                         String.fromCharCode(34)+">"+q1+"</option>");
                        } 
                         
                  });',]); ?>

            <?=$form->field($model, 'unit_2')->
            dropDownList(ArrayHelper::map(
                app\models\employees::findbysql('
                select 1630 as id," Всі підрозділи" as unit_2
                union
                Select min(id) as id,unit_2 
                from 1c 
                where LENGTH(ltrim(rtrim(unit_2)))<>0
                 group by unit_2
                  order by unit_2')
                    ->all(), 'id', 'unit_2'),
                ['prompt' => 'Виберіть підрозділ'
                    ]); ?>

          <!-- <?= $form->field($model, 'fio',['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-user"></span></span>{input}</div>'])
                ->textInput(['onDblClick' => 'rmenu($(this).val(),"#inputdata-fio")'])?> -->
            
            
             <?= $form->field($model, 'fio',['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-user"></span></span>{input}</div>'])
                ->textInput(
                ['autocomplete' => 'off','maxlength' => true,'onkeyup' => '$.get("' . Url::to('/phone/web/site/get_fio?fio=') .
                    '"+$(this).val(),
                   function(data) {
                         $("#inputdata-id_t").empty();
                        // alert(data.cur.length);
                         var j=data.cur.length;
                         if(j<9){
                         // Находим самую длинную строку
                         var lmax=0,sfio,lfio;
                         for(var ii = 0; ii<j; ii++) {
                             sfio = data.cur[ii].fio;
                             lfio = sfio.length;
                            if(lfio>lmax)
                                lmax=lfio;
                         }
                         //alert(lmax);
                         for(var ii = -1; ii<j; ii++) {
                         if(ii==-1) { var q1=" ";var q2=" ";var n = 20000;
                            $("#inputdata-id_t").append("<option onClick="+String.fromCharCode(34)+"sel_fio($(this).text(),"+n+");"
                            +String.fromCharCode(34)+" value="+n+">"+q1+"  "+q2+
                            "</option>");
                         }
                         else {
                         var w=data.cur[ii].tel_mob;
                         sfio = data.cur[ii].fio;
                         lfio = sfio.length;
                         //alert(lmax-lfio);
                         //&#8195;
//                         if((lmax-lfio)>0)
//                             sfio = sfio+stringFill("&#8195;",(lmax-lfio)*2);
//                         else    
//                             sfio = sfio+"&nbsp;";
                         
                         //alert("1"+stringFill(" ",(lmax-lfio))+"1");
                         if(w==null || w=="" || normtel(w)=="") {var q1 = sfio;
                            }
                         else {var q1 = sfio +"  <span>"+", "+normtel(w)+"</span>";}
                         
                         var w1=data.cur[ii].tel;
                         
                         if(!(w1==null || w1=="")) var q1 = q1+"  <span>"+", "+w1+"</span>";
                        
                         var n = data.cur[ii].id;
                        // if(q1==null && ii<>0) continue;
//                         var q1 = q.substr(6);
//                         var n = q.substr(0,6);
                        
                         $("#inputdata-id_t").append("<option onClick="+String.fromCharCode(34)+"sel_fio($(this).text(),"+n+");"
                         +String.fromCharCode(34)+" value="+n+">"+q1+
                         "</option>");
                         //$("#inputdata-id_t").append("<option value="+n+">"+"<span>"+q1+"</span></option>");
                         $("#inputdata-id_t").attr("size", ii+2);
                         //$("#klient-id_t").focus();
                         $("#inputdata-id_t").show();
                         $(".field-inputdata-id_t").show();
                        }}} 
                        if(j>8 || data.success==false) {$("#inputdata-id_t").hide();
                            $(".field-inputdata-id_t").hide();}
                            
                  });',
                    'onDblClick' => 'rmenu($(this).val(),"#inputdata-fio")'
                ]) ?>
            
            
            <?=$form->field($model, 'id_t')->
            dropDownList(['maxlength' => true,"onchange"=>"sel_fio1(this,event)",['rows' => 3, 'cols' => 55]]) ?>

            <div class='rmenu' id='rmenu-inputdata-fio'></div>

            <?= $form->field($model, 'tel_mob',['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
            . '<span class="glyphicon glyphicon-phone"></span></span>{input}</div>']) ?>
            <?= $form->field($model, 'tel_town',['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
                . '<span class="glyphicon glyphicon-phone-alt"></span></span>{input}</div>']) ?>
            <?= $form->field($model, 'tel',['inputTemplate' => '<div class="input-group"><span class="input-group-addon">'
                . '<span class="glyphicon glyphicon-earphone"></span></span>{input}</div>']) ?>
<!--            --><?//= $form->field($model, 'post')->textInput(['onDblClick' => 'rmenu($(this).val(),"#inputdata-post")']) ?>
            <?= $form->field($model, 'post')->textInput(['autocomplete' => 'off','maxlength' => true,
                'onkeyup' => '$.get("' . Url::to('/phone/web/site/get_post?post=') .
                    '"+$(this).val(),
                   function(data) {
                         $("#inputdata-id_p").empty();
                        // alert(data.cur.length);
                         var j=data.cur.length;
                         if(j<39){
                         // Находим самую длинную строку
                         var lmax=0,sfio,lfio;
                         for(var ii = 0; ii<j; ii++) {
                             sfio = data.cur[ii].post;
                             lfio = sfio.length;
                            if(lfio>lmax)
                                lmax=lfio;
                         }
//                         alert(lmax);
                         for(var ii = -1; ii<j; ii++) {
                         if(ii==-1) { var q1=" ";var q2=" ";var n = 20000;
                            $("#inputdata-id_p").append("<option onClick="+String.fromCharCode(34)+"sel_post($(this).text(),"+n+");"
                            +String.fromCharCode(34)+" value="+n+">"+q1+"  "+q2+
                            "</option>");
                         }
                         else {
                         var w="";
                         sfio = data.cur[ii].post;
//                         alert(sfio);
                         lfio = sfio.length;
                         //alert(lmax-lfio);
                         //&#8195;

                         if(w==null || w=="" ) {var q1 = sfio;
                            }
                         else {var q1 = sfio;}
                        
                         var n = data.cur[ii].id;
//                        alert(n);
                         $("#inputdata-id_p").append("<option onClick="+String.fromCharCode(34)+"sel_post($(this).text(),"+n+");"
                         +String.fromCharCode(34)+" value="+n+">"+q1+
                         "</option>");
                         $("#inputdata-id_p").attr("size", ii+2);
                         $("#inputdata-id_p").show();
                         $(".field-inputdata-id_p").show();
                        }}} 
                        if(j>37 || data.success==false) {$("#inputdata-id_p").hide();
                            $(".field-inputdata-id_p").hide();}
                            
                  });',
                    'onDblClick' => 'rmenu($(this).val(),"#inputdata-post")'
                ]) ?>

            <?=$form->field($model, 'id_p')->
            dropDownList(['maxlength' => true,"onchange"=>"sel_post1(this,event)",['rows' => 3, 'cols' => 55]]) ?>

            <div class='rmenu' id='rmenu-inputdata-post'></div>

            <?= $form->field($model, 'gpost')->label('Група посад') -> textInput() -> dropDownList (ArrayHelper::map(
                app\models\employees::findbysql('
                 select min(id) as id,gpost from group_post group by 2 order by 2')
                    ->all(), 'id', 'gpost'),
                ['prompt' => 'Виберіть групу посад',]) ?>

            <?php if(isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role==3)
                echo $form->field($model, 'sex')->
                dropDownList([1 => 'Чоловіча',2 => 'Жіноча'],['prompt'=>'Виберіть стать']);
            ?>
            <? echo $form->field($model, 'vip') ?>

<!--            --><?//= $form->field($model, 'email') ?>

            <div class="form-group">
                <?= Html::submitButton('OK', ['class' => 'btn btn-primary','id' => 'btn_find','onclick'=>'dsave()']); ?>
<!--                --><?//= Html::a('OK', ['/CalcWork/web'], ['class' => 'btn btn-success']) ?>
<!--                --><?//= Html::submitButton('Вийти з розширеного режиму', ['class' => 'btn btn-primary','id' => 'btn_exit','onclick'=>'mode_ex()']); ?>
                <button id=btn_exit onclick="mode_ex();return false;" >Вийти з розширеного режиму</button>
            </div>

            <?php
            
            $session = Yii::$app->session;
            $session->open();
            $session->set('view', 0);
            
            ActiveForm::end(); ?>
        </div>
          
        <?php if(!(isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role==3)): ?>
        <div class="tel_right_side"> 
            <p class="tel_news">Новини сайту</p>
            <?php if(!($gendir == 'Корса Микола Вікторович')): ?>
            <div class="kigdsaxtun"> Виконуючий обов'язки генерального директора:
                <p ><?php echo $gendir;?></p>
                 </div>
            <?php endif; ?>
            <!--            <br>-->
            <p class="tel_news_r">1. З <span> 21.09.2021 </span> довідник обновився актуальними записами з новими посадами.</p>
            <p class="tel_news_r">2. Увага! З'явились нові фото співробітників.</p>
            <p class="tel_news_sr">3. З'явився пошук по групі посад.</p>
<!--            <p class="tel_news_r">2. Увага! З'явилась нова послуга <a href="http://192.168.55.1/proffer">«Книга скарг та пропозицій»</a></p>-->

<!--            <p class="tel_news_n"> --><?//= Html::encode('3. ');?>
<!--                <div class="tel_news_block">-->
<!--                   --><?php ////echo Html::a("Список працівників, які перевисили ліміт по мобільному зв'язку за червень 2019 р.", ['/shtrafbat']); ?>
<!--                   --><?php //echo Html::a("Доїзд працівників на період короновірусу.", ['/job_health.xlsx']); ?>
<!--               </div>    -->
<!--            </p>-->
            
        </div>  
            
            <!-- weather widget start -->
<!--            <a class="weather" target="_blank" href="http://nochi.com/weather/dnipro-33401">
                <img src="https://w.bookcdn.com/weather/picture/2_33401_1_20_137AE9_160_ffffff_333333_08488D_1_ffffff_333333_0_6.png?scode=124&domid=604&anc_id=26065"  alt="booked.net"/>
            </a> weather widget end -->
            
<!--           <div id="MeteoInformerWrap">-->
<!--            <script type="text/javascript" src="http://meteo.ua/var/informers.js"></script>-->
<!--            <script type="text/javascript">-->
<!--            makeMeteoInformer("http://meteo.ua/informer/get.php?cities=164&w=280&lang=ua&rnd=1&or=vert&clr=4&dt=today&style=classic",276,525);-->
<!--            </script>-->
<!--            </div>-->

            <?php
//            $apiKey = "feb5088590e2d51f105fddf4c1b9435d";
//            $cityId = "709930";
//            $apiUrl = "http://api.openweathermap.org/data/2.5/weather?id=" . $cityId . "&lang=ru&units=metric&APPID=" . $apiKey;
//
//            $crequest = curl_init();
//
//            curl_setopt($crequest, CURLOPT_HEADER, 0);
//            curl_setopt($crequest, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($crequest, CURLOPT_URL, $apiUrl);
//            curl_setopt($crequest, CURLOPT_FOLLOWLOCATION, 1);
//            curl_setopt($crequest, CURLOPT_VERBOSE, 0);
//            curl_setopt($crequest, CURLOPT_SSL_VERIFYPEER, false);
//            $response = curl_exec($crequest);
//
//            curl_close($crequest);
//            $data = json_decode($response);
//            $currentTime = time();
//
//            $feels_like=round($data->main->feels_like,0);
//            $clouds = $data->clouds->all;
//            debug($feels_like);
//            debug($clouds);
//            debug($data);
            ?>

<!--          <div id="MeteoInformerWrap">-->
<!--            <link type="text/css" rel="stylesheet" href="https://www.meteoprog.ua/css/winformer.min.css?id=100">-->
<!---->
<!--            <div class="meteoprog-informer" style="width: 260px" data-params='{"city_ids":"2392","domain":"https://www.meteoprog.ua/ua","id":"61497bf22bac9279538b46f0","lang":"ua"}'>-->
<!---->
<!--                <a title="Погода в місті Дніпро (Дніпропетровськ)" target="_blank" href="https://www.meteoprog.ua/ua/weather/Dnipropetrovsk">-->
<!--                    <img style="margin: 0 auto; display: block" src="https://www.meteoprog.ua/images/preloader.gif" alt="Loading...">-->
<!--                </a>-->
<!--                <a target="_blank" class="constructor__met2wlink" href="https://www.meteoprog.ua/ua/review/Dnipropetrovsk/">Погода на 2 тижні</a>-->
<!---->
<!--                <a class="constructor__metlink" target="_blank" href="https://www.meteoprog.ua/ua">-->
<!--                    <img  style="display: block; margin: 0 auto;" alt="" src="https://www.meteoprog.ua/images/meteoprog-inf.png">-->
<!--                </a>-->
<!--            </div>-->
<!--                 <script type="text/javascript" src="https://www.meteoprog.ua/js/winformer.min.js?id=100"></script>-->
<!--                <script>-->
<!--                 // Баннер погоды-->
<!--                !function()-->
<!--                {var DomReady=window.DomReady={},userAgent=navigator.userAgent.toLowerCase(),-->
<!--                    browser_safari=(userAgent.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/),/webkit/.test(userAgent)),-->
<!--                    browser_opera=/opera/.test(userAgent),browser_msie=/msie/.test(userAgent)&&!/opera/.test(userAgent),-->
<!--                    readyBound=(/mozilla/.test(userAgent)&&/(compatible|webkit)/.test(userAgent),!1),isReady=!1,readyList=[];-->
<!--                    function domReady(){if(!isReady&&(isReady=!0,readyList))-->
<!--                    {for(var fn=0;fn<readyList.length;fn++)readyList[fn].call(window,[]);readyList=[]}}-->
<!--                     function bindReady() {var numStyles,func,oldonload;readyBound||(readyBound=!0,-->
<!--                     document.addEventListener&&!browser_opera&&document.addEventListener("DOMContentLoaded",domReady,!1),-->
<!--                     browser_msie&&window==top&&function(){if(!isReady){-->
<!--                         try{document.documentElement.doScroll("left")}catch(error){return setTimeout(arguments.callee,0)}-->
<!--                         domReady()}}(),browser_opera&&document.addEventListener("DOMContentLoaded",function()-->
<!--                            {if(!isReady){for(var i=0;i<document.styleSheets.length;i++)-->
<!--                                if(document.styleSheets[i].disabled)return void setTimeout(arguments.callee,0);-->
<!--                                domReady()}},!1),-->
<!--                                browser_safari&&function(){if(!isReady)-->
<!--                                    if("loaded"===document.readyState||"complete"===document.readyState){-->
<!--                                        if(void 0===numStyles){-->
<!--                                            for(var links=document.getElementsByTagName("link"),i=0;i<links.length;i++)"stylesheet"===links[i].getAttribute("rel")&&numStyles++;-->
<!--                                            var styles=document.getElementsByTagName("style");numStyles+=styles.length}-->
<!--                                            document.styleSheets.length==numStyles?domReady():setTimeout(arguments.callee,0)}-->
<!--                                            else setTimeout(arguments.callee,0)}(),func=domReady,-->
<!--                                             oldonload=window.onload,-->
<!--                                            "function"!=typeof window.onload?window.onload=func:window.onload=function()-->
<!--                                            {oldonload&&oldonload(),func()})}DomReady.ready=function(fn,args)-->
<!--                                            {bindReady(),isReady?fn.call(window,[]):readyList.push(function()-->
<!--                                            {return fn.call(window,[])})},bindReady()}(),-->
<!--                                            function(){var our_informers;function getDomainFromUrl(url)-->
<!--                                            {var url_element=document.createElement("a");return url_element.href=url,-->
<!--                                            "https://"+url_element.hostname}function initInformers(i)-->
<!--                                            {var params,onReady,http,obj_params,url,dataParams=our_informers[i].getAttribute("data-params"),-->
<!--                                                jsonDataParams=JSON.parse(dataParams),-->
<!---->
<!--                                                citiesCount=jsonDataParams.city_ids.split(",").length;our_informers[i].querySelectorAll('a[href*="meteoprog."]').length>=2*citiesCount?(params=dataParams,-->
<!--                                                onReady=function(data) {var outerElem=document.createElement("div",{class:"outerelem"});-->
<!---->
<!--                                                // Взлом-->
<!--                                                // Добавляем Температура відчувається и Хмарність с другого сайта-->
<!--                                                    var pos = data.indexOf('constructor__city-wrp');-->
<!--                                                    var data1 =   data.substring(0, pos+22) ;-->
<!--                                                    var data2 =   data.substring(pos+23) ;-->
<!--                                                    data = data1 + '<div class="constructor__custom1"> Температура відчувається на ' +-->
<!--                                                        --><?php //echo $feels_like; ?><!--//  + '&deg;</div>';-->
<!--//                                                    data = data + '<div class="constructor__custom2"> Хмарність ' +-->
<!--//                                                        --><?php ////echo $clouds; ?><!--// +  '% </div>' + data2 ;-->
<!--//                                                // Убираем ссылку внизу-->
<!--//                                                // alert(data);-->
<!--//                                                var pos = data.indexOf('constructor__metlink');-->
<!--//                                                data =   data.substring(0, pos-10) ;-->
<!--//                                                // Убираем ссылку с температуры-->
<!--//                                                data =   data.replace('href="https://meteoprog.ua/weather/Dnipropetrovsk"','');-->
<!--//                                                //-->
<!--//-->
<!--//                                                outerElem.innerHTML=data;-->
<!--//                                                for(var links=outerElem.querySelectorAll("link"),-->
<!--//                                                                                 // links.length-->
<!--//                                                 curr_domain=(outerElem.querySelectorAll("img"),getDomainFromUrl(jsonDataParams.domain)),j=0;j<links.length;j++)-->
<!--//                                                    links[j].getAttribute("href").startsWith("http://")||links[j].getAttribute("href").startsWith("https://")||(links[j].href=curr_domain+links[j].getAttribute("href"));-->
<!--//                                                document.querySelectorAll(".meteoprog-informer")[i].innerHTML=outerElem.innerHTML,++i<our_informers.length&&initInformers(i)},-->
<!--//                                                http=new XMLHttpRequest,obj_params=JSON.parse(params),-->
<!--//                                                url=getDomainFromUrl(obj_params.domain)+"/widget_v2/show/json/"+obj_params.id+"/?nocache=1",-->
<!--//                                                http.open("POST",url,!0),http.setRequestHeader("Content-type","application/x-www-form-urlencoded"),-->
<!--//                                                http.onreadystatechange=function(){4===http.readyState&&200===http.status&&onReady(JSON.parse(http.responseText).data)},-->
<!--//                                                http.send("params="+params)):++i<our_informers.length&&initInformers(i)}DomReady.ready(function(){-->
<!--//                                                our_informers=document.querySelectorAll(".meteoprog-informer"),initInformers(0)-->
<!--//-->
<!--//                                                })-->
<!--//-->
<!--//                                                var element = document.getElementById("MeteoInformerWrap").classList;-->
<!--//                                                // alert(element);-->
<!--//                                                element.remove('constructor__metlink');-->
<!--//-->
<!--//                }();-->
<!--//-->
<!--//-->
<!--//                </script>-->


<!--            </div>-->



<!--            <div class="weather">-->
<!--                <h2 class="weather__title">Погода в городе --><?php //echo $data->name; ?><!--</h2>-->
<!--                <div class="weather__time">-->
<!--                    <p class="weather__time">--><?php //echo date("l g:i a", $currentTime); ?><!--</p>-->
<!--                    <p class="weather__date">--><?php //echo date("jS F, Y",$currentTime); ?><!--</p>-->
<!--                    <p class="weather__status">--><?php //echo ucwords($data->weather[0]->description); ?><!--</p>-->
<!--                </div>-->
<!--                <div class="weather__forecast">-->
<!--                    <span class="weather__min">--><?php //echo $data->main->temp_min; ?><!--°C</span>-->
<!--                    <span class="weather__max">--><?php //echo $data->main->temp_max; ?><!--°C</span>-->
<!--                </div>-->
<!--                <p class="weather__humidity">Влажность: --><?php //echo $data->main->humidity; ?><!-- %</p>-->
<!--                <p class="weather__wind">Ветер: --><?php //echo $data->wind->speed; ?><!-- км/ч</p>-->
<!--            </div>-->




        <?php endif; ?>  
    </div>
</div>


<script>

    function mode_ex()
    {
        var vip
        vip=localStorage.getItem("vip");
        if(vip==1) localStorage.setItem("vip",0);
        $('#inputdata-vip').val(0);
        $('.navbar-default').css('background-color', '#14837D');
        alert('Увага! Виключено розширений режим довідника.');
        $('#btn_exit').hide();
        $('.hero_area').css('background-image',  'url("' + '../electric.jpg' + '")');
        return 0;
    }

    function dsave()
    {

        localStorage.setItem("fio",$('#inputdata-fio').val());
    }
    function sel_fio(elem,id) {
        //localStorage.setItem("id_fio", id);
        var p,r;
        elem=$.trim(elem);
        //alert(elem+'1');
        p=elem.indexOf('  ')+1;
        r=elem.substr(0,p);
        r=$.trim(r);
       
        if(p>0)
            $("#inputdata-fio").val(r);
        else
            $("#inputdata-fio").val(elem);
        
        $(".field-inputdata-id_t").hide();
        $("#inputdata-id_t").hide();
        //$("#klient-search_street").val('');
        
    }
     function sel_fio1(elem,event) {
        //alert(event.keyCode);
        if(event.keyCode==13) {
            $("#inputdata-fio").val(elem);
            $("#inputdata-id_t").hide();
        }
    }

    function sel_post1(elem,event) {
        //alert(event.keyCode);
        if(event.keyCode==13) {
            $("#inputdata-post").val(elem);
            $("#inputdata-id_p").hide();
        }
    }
    function sel_post(elem,id) {
        //localStorage.setItem("id_fio", id);
        var p,r;
        elem=$.trim(elem);
        //alert(elem+'1');
        p=elem.indexOf('  ')+1;
        r=elem.substr(0,p);
        r=$.trim(r);

        if(p>0)
            $("#inputdata-post").val(r);
        else
            $("#inputdata-post").val(elem);

        $(".field-inputdata-id_p").hide();
        $("#inputdata-id_p").hide();
        //$("#klient-search_street").val('');

    }
    
     function normtel(p){
        if(p==null) return '';
        //if(!(p.indexOf(',')==-1)) return '';
        var pos = p.indexOf(',');
        var qt,jt,frez='',origin;
        origin=p;
        if (pos==-1)
            qt=1;
        else
            qt=2;
        if(qt==2)
            $("#inputdata-id_t").css("font-size", 13);
        else
            $("#inputdata-id_t").css("font-size", 14);
        for(jt=1;jt<=qt;jt++) {
        if (pos>-1 && jt==1)
            p=origin.substr(0,pos); 
        if (pos>-1 && jt==2)
            p=origin.substr(pos+1);
        //alert(p);
        if(!(p.substr(0,1)=='0'))
            p='0'+p; 
        var y,i,c,tel = '',kod,op,flag=0,rez='';
        y = p.length;

        for(i=0;i<y;i++)
        {
            c = p.substr(i,1);
            kod=p.charCodeAt(i);
            if(kod>47 && kod<58) tel+=c;
        }
        op = tel.substr(0,3);
        y = tel.length;
        if(y<10) {
            return '';
        }
            switch(op) {
                case '050':  flag = 1;
                    break;
                case '096':  flag = 1;
                    break;
                case '097':  flag = 1;
                    break;
                case '098':  flag = 1;
                    break;
                case '099':  flag = 1;
                    break;

                case '091':  flag = 1;
                    break;
                case '063':  flag = 1;
                    break;
                case '073':  flag = 1;
                    break;
                case '067':  flag = 1;
                    break;
                case '066':  flag = 1;
                    break;

                case '093':  flag = 1;
                    break;
                case '095':  flag = 1;
                    break;
                case '039':  flag = 1;
                    break;
                case '068':  flag = 1;
                    break;
                case '092':  flag = 1;
                    break;
                case '094':  flag = 1;
                    break;
            }

            var add = tel.substr(3,3);
            rez+=add+'-';
            add = tel.substr(6,2);
            rez+=add+'-';
            add = tel.substr(8);
            rez+=add;

        if(flag) {
            rez = op+' '+rez;
        }
        else{
            rez = '('+op+')'+' '+rez;
        }
            
            if(qt==2 && jt==1)
                frez=rez+', ';
            if(qt==2 && jt==2)
                frez+=rez;
             if(qt==1)
                frez=rez;
        }
        return frez;
    }

function stringFill(x, n) { 
    var s = ''; 
    while (s.length < n) s += x; 
    return s; 
} 


    //window.onload=function(){


   
</script>





