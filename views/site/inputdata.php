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
       $('#inputdata-id_t').each(function () {
        var txt = $(this).text()
        $(this).html(
            "<span style='color:#111111" + ";'></span>" + txt)
})
    }
    
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

            <?=$form->field($model, 'main_unit')->dropDownList(
                    ArrayHelper::map(app\models\employees::findbysql(
                            "select 630 as id_name,0 as id,null as nazv,'Всі підрозділи' as main_unit
                                union
                                select min(a.id_name) as id_name,b.id,b.nazv,a.main_unit 
                                from vw_phone a 
                                left join spr_res b on a.main_unit = b.nazv
                                group by b.id,b.nazv,a.main_unit")->all(), 'id_name', 'main_unit'),
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
                         var q1 = q.substr(3);
                         var n = q.substr(0,3);
                         $("#inputdata-unit_1").append("<option value="+n+
                         " style="+String.fromCharCode(34)+"font-size: 14px;"+
                         String.fromCharCode(34)+">"+q1+"</option>");
                        } 
                         $("#inputdata-unit_1").change();
                  });',]); ?>

            <?=$form->field($model, 'unit_1')->
            dropDownList(ArrayHelper::map(
                app\models\employees::findbysql('
                select 630 as id," Всі підрозділи" as unit_1
                union
                Select min(id) as id,unit_1 
                from vw_phone 
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
                         var q1 = q.substr(3);
                         var n = q.substr(0,3);
                         $("#inputdata-unit_2").append("<option value="+n+
                         " style="+String.fromCharCode(34)+"font-size: 14px;"+
                         String.fromCharCode(34)+">"+q1+"</option>");
                        } 
                         
                  });',]); ?>

            <?=$form->field($model, 'unit_2')->
            dropDownList(ArrayHelper::map(
                app\models\employees::findbysql('
                select 630 as id," Всі підрозділи" as unit_2
                union
                Select min(id) as id,unit_2 
                from vw_phone 
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
                         //alert(data.cur.length);
                         var j=data.cur.length;
                         if(j<6){
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
                        if(j>5 || data.success==false) {$("#inputdata-id_t").hide();
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
            <?= $form->field($model, 'post')->textInput(['onDblClick' => 'rmenu($(this).val(),"#inputdata-post")']) ?>

            <div class='rmenu' id='rmenu-inputdata-post'></div>

<!--            --><?//= $form->field($model, 'email') ?>

            <div class="form-group">
                <?= Html::submitButton('OK', ['class' => 'btn btn-primary','id' => 'btn_find','onclick'=>'dsave()']); ?>
<!--                --><?//= Html::a('OK', ['/CalcWork/web'], ['class' => 'btn btn-success']) ?>
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
            <br>
            <p class="tel_news_r">1. Увага! З'явилась нова послуга <a href="http://192.168.55.1/proffer">«Книга скарг та пропозицій»</a></p>
            <p class="tel_news_n"> <?= Html::encode('2.[Нове!!!] ');?> </p>
                <div class="tel_news_block">
                    <?php echo Html::a("Список працівників, які перевисили ліміт по мобільному зв'язку за квітень 2018 р.", ['/shtrafbat']); ?>
                </div>    
            </p>
            
        </div>  
            
            <!-- weather widget start -->
<!--            <a class="weather" target="_blank" href="http://nochi.com/weather/dnipro-33401">
                <img src="https://w.bookcdn.com/weather/picture/2_33401_1_20_137AE9_160_ffffff_333333_08488D_1_ffffff_333333_0_6.png?scode=124&domid=604&anc_id=26065"  alt="booked.net"/>
            </a> weather widget end -->
            
           <div id="MeteoInformerWrap">
            <script type="text/javascript" src="http://meteo.ua/var/informers.js"></script>
            <script type="text/javascript">
            makeMeteoInformer("http://meteo.ua/informer/get.php?cities=164&w=280&lang=ua&rnd=1&or=vert&clr=4&dt=today&style=classic",276,525);
            </script>
            </div>
        <?php endif; ?>  
    </div>
</div>


<script>
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





