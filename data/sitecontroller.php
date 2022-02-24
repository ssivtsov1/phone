<?php

namespace app\controllers;
//namespace app\models;

use app\models\A_diary;
use app\models\A_diary_search;
use app\models\Norms_search;
use app\models\Inputreport_years;
use app\models\phones_sap;
use app\models\phones_sap_search;
use app\models\Plan;
use app\models\DataReport;
use app\models\plan_forma;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\helpers\Url;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use app\models\ContactForm;
use app\models\InputData;
use app\models\cdata;
use app\models\needs_fact;
use app\models\cneeds_fact;
use app\models\vneeds_fact;
use app\models\shtrafbat;
use app\models\viewphone;
use app\models\list_workers;
use app\models\kyivstar;
use app\models\hipatch;
use app\models\tel_vi;
use app\models\requestsearch;
use app\models\tofile;
use app\models\forExcel;
use app\models\info;
use app\models\User;
use app\models\loginform;
use kartik\mpdf\Pdf;
//use mpdf\mpdf;
use yii\web\UploadedFile;
use app\models\Norms;

class SiteController extends Controller
{  /**
 * 
 * @return type
 *
 */

    public $curpage;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    //  Происходит при запуске сайта
    public function actionIndex()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->redirect(['site/more']);
        }
        if(strpos(Yii::$app->request->url,'/cek')==0) {
            return $this->redirect(['site/more']);
        }
        $model = new loginform();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['site/more']);
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    //  Происходит при формировании отчета по потреблению
    // при нажатии на кнопку "Зведений звіт"
    public function actionRep_permonth()
    {
        $model = new DataReport();

        if ($model->load(Yii::$app->request->post())) {
            $sql = $model->sql;

            if($model->year>2021)
            {


                // case when counter like '%Усього%' then coalesce(all_delta,0) else coalesce(delta_1,0) end
                $sql = "select case when nazv like '%Усього%' then 10 else 1 end as prior, voltage,year,nazv,res,
                sum(case when counter like '%Усього%' then 0 else potr_1 end)  as month_1,
                sum(coalesce(delta_1,0::dec(12,3))) as delta_1,
                sum(case when counter like '%Усього%' then 0 else potr_2 end)  as month_2,sum(delta_2) as delta_2,
                sum(case when counter like '%Усього%' then 0 else potr_3 end)   as month_3,sum(delta_3) as delta_3,
                sum(case when counter like '%Усього%' then 0 else potr_4 end)  as month_4,sum(delta_4) as delta_4,
                sum(case when counter like '%Усього%' then 0 else potr_5 end)  as month_5,sum(delta_5) as delta_5,
                sum(case when counter like '%Усього%' then 0 else potr_6 end)  as month_6,sum(delta_6) as delta_6,
                sum(case when counter like '%Усього%' then 0 else potr_7 end)  as month_7,sum(delta_7) as delta_7,
                sum(case when counter like '%Усього%' then 0 else potr_8 end)  as month_8,sum(delta_8) as delta_8,
                sum(case when counter like '%Усього%' then 0 else potr_9 end)  as month_9,sum(delta_9) as delta_9,
                sum(case when counter like '%Усього%' then 0 else potr_10 end)  as month_10,sum(delta_10) as delta_10,
                sum(case when counter like '%Усього%' then 0 else potr_11 end)  as month_11,sum(delta_11) as delta_11,
                sum(case when counter like '%Усього%' then 0 else potr_12 end)  as month_12,sum(delta_12) as delta_12
                 from (".$sql.') x'.' GROUP BY voltage,year,nazv,res ' .
                    " order by case when nazv like '%Усього%' then 10 else 1 end asc,
                    case when res='СПС' then '0'||res else res end asc,voltage desc,nazv asc,year desc";

//                $sql = "select *
//                 from (".$sql.') x';
            }


//            debug( $sql);
//            return;

            $data1 = cneeds_fact::findBySql($sql)->asarray()->all();

//            debug( $data1);
//            return;

            return $this->render('report_permonth', [
                'model' => $model, 'data1' => $data1
            ]);
        }
            $sql1  =  Yii::$app->request->post('data');
            $year  =  Yii::$app->request->post('year');
            return $this->render('data_report_permonth', [
                'model' => $model,'sql' => $sql1,'year' => $year
            ]);
    }

    //  Происходит при формировании печати отчета по потреблению
    public function actionRep_permonth_print()
    {
            $sql = Yii::$app->request->post('sql');
            $m = Yii::$app->request->post('m');
            $y = Yii::$app->request->post('y');

        if($y>2021)
        {
            $sql = "select case when nazv like '%Усього%' then 10 else 1 end as prior, voltage,year,nazv,res,
                sum(case when counter like '%Усього%' then 0 else potr_1 end)  as month_1,sum(delta_1) as delta_1,
                sum(case when counter like '%Усього%' then 0 else potr_2 end)  as month_2,sum(delta_2) as delta_2,
                sum(case when counter like '%Усього%' then 0 else potr_3 end)   as month_3,sum(delta_3) as delta_3,
                sum(case when counter like '%Усього%' then 0 else potr_4 end)  as month_4,sum(delta_4) as delta_4,
                sum(case when counter like '%Усього%' then 0 else potr_5 end)  as month_5,sum(delta_5) as delta_5,
                sum(case when counter like '%Усього%' then 0 else potr_6 end)  as month_6,sum(delta_6) as delta_6,
                sum(case when counter like '%Усього%' then 0 else potr_7 end)  as month_7,sum(delta_7) as delta_7,
                sum(case when counter like '%Усього%' then 0 else potr_8 end)  as month_8,sum(delta_8) as delta_8,
                sum(case when counter like '%Усього%' then 0 else potr_9 end)  as month_9,sum(delta_9) as delta_9,
                sum(case when counter like '%Усього%' then 0 else potr_10 end)  as month_10,sum(delta_10) as delta_10,
                sum(case when counter like '%Усього%' then 0 else potr_11 end)  as month_11,sum(delta_11) as delta_11,
                sum(case when counter like '%Усього%' then 0 else potr_12 end)  as month_12,sum(delta_12) as delta_12
                 from (".$sql.') x'.' GROUP BY voltage,year,nazv,res ' .
                " order by case when nazv like '%Усього%' then 10 else 1 end asc, voltage desc,nazv asc,year desc";
        }

            $data1 = cneeds_fact::findBySql($sql)->asarray()->all();

            $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8 , // leaner size using standard fonts
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'content' => $this->renderPartial('report_permonth_print',['m' => $m,'style_title' => 'd9','data1' => $data1]),
            'options' => [
                'title' => 'Друк звіту',
                'subject' => ''
            ],
            'methods' => [
//                'SetHeader' => ['Створено для печаті: ' . date("d.m.Y H:i:s")],
                'SetFooter' => [''],
            ]
        ]);
        return $pdf->render();
    }

    //  Происходит после ввода пароля
    public function actionMore($sql='0',$id_p=0)
    {
        $this->curpage=1;
        if($sql=='0') {

            $model = new InputData();
            $augment = 1; //  константа - нужно поменять если будут добавляться года в список (В 2022 году нужно поменять на 1 - пока 2 для отладки программы в 2021 году)
            $const_year=date('Y')+$augment;
            if ($model->load(Yii::$app->request->post())) {
                // Создание поискового sql выражения
                $where = '';
                if (!empty($model->up)) {
                    $where .= ' and (delta_1>0 or delta_2>0 or delta_3>0  or delta_4>0  or delta_5>0
                     or delta_6>0  or delta_7>0  or delta_8>0  or delta_9>0  or delta_10>0
                      or delta_11>0  or delta_12>0)'   ;
                }
                if (!empty($model->year)) {
//                    if($model->year<>1)
                        $year=$const_year-$model->year;
//                    else
//                        $year=0;
                }
                else
                    $year=$const_year-$augment;

//                debug($year);
//                return;

                if(!isset(Yii::$app->user->identity->role))
                {      $flag=0;
                        $role=0;
                }
                else{
                    $role=Yii::$app->user->identity->role;
                }

//                debug($role);
//                return;

                switch($role) {
                    case 2:
                        $where .= " and rem='-'";
                        break;
                    case 4:
                        $where .= " and rem='01'";
                        break;
                    case 5:
                        $where .= " and rem='02'";
                        break;
                    case 6:
                        $where .= " and rem='03'";
                        break;
                    case 7:
                        $where .= " and rem='04'";
                        break;
                    case 8:
                        $where .= " and rem='05'";
                        break;
                }

                if (!empty($model->rem)) {
                    switch ($model->rem){
                        case 1:
                            $where .= ' and rem=' . "'" . '-' ."'" ;
                            break;
                        case 2:
                            $where .= ' and rem=' . "'" . '01' ."'" ;
                            break;
                        case 3:
                            $where .= ' and rem=' . "'" . '03' ."'" ;
                            break;
                        case 4:
                            $where .= ' and rem=' . "'" . '04' ."'" ;
                            break;
                        case 5:
                            $where .= ' and rem=' . "'" . '02' ."'" ;
                            break;
                        case 6:
                            $where .= ' and rem=' . "'" . '05' ."'" ;
                            break;
                    }

                }
                $where = trim($where);
                if (empty($where)) $where = '';
                else {
                    $where = ' where ' . substr($where, 4) . ' or id>=10480 ' ;
                }
                // до 2022 года расход эл-энергии учитывается в целом по всей подстанции
               if($year<2022) {
                   if ($role < 4)
                       // Если вход как администратор
                       $sql = "select ROW_NUMBER() OVER(order by voltage desc,rem asc,nazv asc,year desc) AS rid,
                            id,nazv,res,all_month,all_delta,month_1,delta_1,month_2,delta_2,month_3,delta_3,month_4,delta_4,month_5,delta_5,month_6,delta_6,month_7,delta_7,month_8,delta_8,
            month_9,delta_9,month_10,delta_10,month_11,delta_11,month_12,delta_12,voltage,year from (
    select 0 as priority,a.*,
    (a.month_1+a.month_2+a.month_3+a.month_4+
    a.month_5+a.month_6+a.month_7+a.month_8+
    a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    a. month_1-b.mon_1 as delta_1,
    a. month_2-b.mon_2 as delta_2,
    a. month_3-b.mon_3 as delta_3,
    a. month_4-b.mon_4 as delta_4,
    a. month_5-b.mon_5 as delta_5,
    a. month_6-b.mon_6 as delta_6,
    a. month_7-b.mon_7 as delta_7,
    a. month_8-b.mon_8 as delta_8,
    a. month_9-b.mon_9 as delta_9,
    a. month_10-b.mon_10 as delta_10,
    a. month_11-b.mon_11 as delta_11,
    a. month_12-b.mon_12 as delta_12,
    (a. month_1-b.mon_1)+
    (a. month_2-b.mon_2)+
    (a. month_3-b.mon_3)+
    (a. month_4-b.mon_4)+
    (a. month_5-b.mon_5)+
    (a. month_6-b.mon_6)+
    (a. month_7-b.mon_7)+
    (a. month_8-b.mon_8)+
    (a. month_9-b.mon_9)+
    (a. month_10-b.mon_10)+
    (a. month_11-b.mon_11)+
    (a. month_12-b.mon_12) as all_delta,
                            c.rem as res
                            from needs_fact a
                            join needs_norm b on trim(a.nazv)=trim(b.nazv) 
                            and a.rem=b.rem
                            and a.year=b.year
                            and case when $year=0 then 1=1 else a.year=$year end 
                            left join kod_rem c on a.rem=c.kod_rem
                           union all
                           
      select 1 as priority,10480 as id,'Усього 6 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    6 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=6
     union all                      
     
    select 2 as priority,10490 as id,'Усього 10 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    10 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=10
     union all   

 select 3 as priority,10491 as id,'Усього 35 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    35 as voltage,
    '' as counter,'' as s_nom,
   sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=35
     union all   
     
     select 4 as priority,10495 as id,'Усього 150 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    150 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=150
     union all   
                                                   
    select 7 as priority,10500 as id,'Усього:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    0 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
    "
                           . apply_rem($model->rem) .
                           " and case when $year=0 then 1=1 else a.year=$year end 
    ) s"
                           . $where . ' order by priority asc,voltage desc,rem asc,nazv asc,year desc';
                   else
                       // Если вход как РЭС до 2022 года
                       $sql = "select ROW_NUMBER() OVER(order by voltage desc,rem asc,nazv asc,year desc) AS rid,
                            id,nazv,res,all_month,all_delta,month_1,delta_1,month_2,delta_2,month_3,delta_3,month_4,delta_4,month_5,delta_5,month_6,delta_6,month_7,delta_7,month_8,delta_8,
            month_9,delta_9,month_10,delta_10,month_11,delta_11,month_12,delta_12,voltage,year from (
    select 0 as priority,a.*,
    (a.month_1+a.month_2+a.month_3+a.month_4+
    a.month_5+a.month_6+a.month_7+a.month_8+
    a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    a. month_1-b.mon_1 as delta_1,
    a. month_2-b.mon_2 as delta_2,
    a. month_3-b.mon_3 as delta_3,
    a. month_4-b.mon_4 as delta_4,
    a. month_5-b.mon_5 as delta_5,
    a. month_6-b.mon_6 as delta_6,
    a. month_7-b.mon_7 as delta_7,
    a. month_8-b.mon_8 as delta_8,
    a. month_9-b.mon_9 as delta_9,
    a. month_10-b.mon_10 as delta_10,
    a. month_11-b.mon_11 as delta_11,
    a. month_12-b.mon_12 as delta_12,
    (a. month_1-b.mon_1)+
    (a. month_2-b.mon_2)+
    (a. month_3-b.mon_3)+
    (a. month_4-b.mon_4)+
    (a. month_5-b.mon_5)+
    (a. month_6-b.mon_6)+
    (a. month_7-b.mon_7)+
    (a. month_8-b.mon_8)+
    (a. month_9-b.mon_9)+
    (a. month_10-b.mon_10)+
    (a. month_11-b.mon_11)+
    (a. month_12-b.mon_12) as all_delta,
                            c.rem as res
                            from needs_fact a
                            join needs_norm b on trim(a.nazv)=trim(b.nazv) 
                            and a.rem=b.rem
                            and a.year=b.year
                            and case when $year=0 then 1=1 else a.year=$year end 
                            left join kod_rem c on a.rem=c.kod_rem
                            union all

select 1 as priority,10480 as id,'Усього 6 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    6 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=6" . apply_rem1($role) .
                           " union all                      
                                 
     select 2 as priority,10490 as id,'Усього 10 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    10 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=10" . apply_rem1($role) .
                           " union all
                            
select 3 as priority,10491 as id,'Усього 35 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    35 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=35" . apply_rem1($role) .
                           " union all 
    
      select 4 as priority,10495 as id,'Усього 150 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    150 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=150" . apply_rem1($role) .
                           " union all   
                                 
    select 7 as priority,10500 as id,'Усього:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    0 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
    "
                           . apply_rem1($role) .
                           " and case when $year=0 then 1=1 else a.year=$year end 
    ) s"
                           . $where . ' order by priority asc,voltage desc,rem asc,nazv asc,year desc';
   }

//               debug($sql);
//                echo '<br>';
//                    $y = mb_strlen($sql, 'UTF-8');
//                    debug($y);
//                    return;

// Начиная с 2022 (применяется другой принцип учета на подстанциях - расход эл-энергии учитывается по каждому счетчику)
        if($year>2021){
        if ($role == 3)
           // Если вход под админом
           $sql = "select ROW_NUMBER() OVER(order by voltage desc,rem asc,nazv asc,year desc) AS rid,
                            id,nazv,res,counter,s_nom,
                            all_month,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else all_delta end as all_delta,
                            month_b_1,
                            month_1,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             or rem=''
                             then potr_1 
                             else (potr_1/1000)::dec(14,8) end as potr_1,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього'  then null
                             else delta_1 end as delta_1,
                            month_b_2,
                            month_2,
                             case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_2 
                             else (potr_2/1000)::dec(14,8) end as potr_2,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_2 end as delta_2,
                            month_b_3,
                            month_3,
                             case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_3 
                             else (potr_3/1000)::dec(14,8) end as potr_3,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_3 end as delta_3,
                            month_b_4,
                            month_4,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_4 
                             else (potr_4/1000)::dec(14,8) end as potr_4,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_4 end as delta_4,
                            month_b_5,
                            month_5,
                             case when trim(rem)='05'
                            or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_5 
                             else (potr_5/1000)::dec(14,8) end as potr_5,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_5 end as delta_5,
                            month_b_6,
                            month_6,
                             case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_6 
                             else (potr_6/1000)::dec(14,8) end as potr_6,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_6 end as delta_6,
                            month_b_7,
                            month_7,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_7 
                             else (potr_7/1000)::dec(14,8) end as potr_7,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_7 end as delta_7,
                            month_b_8,
                            month_8,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_8 
                             else (potr_8/1000)::dec(14,8) end as potr_8,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_8 end as delta_8,
                            month_b_9,
                            month_9,
                             case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_9 
                             else (potr_9/1000)::dec(14,8) end as potr_9,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_9 end as delta_9,
                            month_b_10,
                            month_10,
                             case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_10 
                             else (potr_10/1000)::dec(14,8) end as potr_10,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_10 end as delta_10,
                            month_b_11,
                            month_11,
                             case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_11
                             else (potr_11/1000)::dec(14,8) end as potr_11,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_11 end as delta_11,
                            month_b_12,
                            month_12,
                             case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_12 
                             else (potr_12/1000)::dec(14,8) end as potr_12,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_12 end as delta_12,
                            voltage,year,koef from (
    select 0 as priority,a.*,
    
    (a.month_1-a.month_b_1)*a.koef as potr_1,
    (a.month_2-a.month_b_2)*a.koef as potr_2,
    (a.month_3-a.month_b_3)*a.koef as potr_3,
    (a.month_4-a.month_b_4)*a.koef as potr_4,
    (a.month_5-a.month_b_5)*a.koef as potr_5,
    (a.month_6-a.month_b_6)*a.koef as potr_6,
    (a.month_7-a.month_b_7)*a.koef as potr_7,
    (a.month_8-a.month_b_8)*a.koef as potr_8,
    (a.month_9-a.month_b_9)*a.koef as potr_9,
    (a.month_10-a.month_b_10)*a.koef as potr_10,
    (a.month_11-a.month_b_11)*a.koef as potr_11,
    (a.month_12-a.month_b_12)*a.koef as potr_12,
    
    ((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,
    
   (sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) over(PARTITION by a.nazv,a.rem)-b.mon_1)
                             ::dec(14,6) as delta_1,
                             
    sum((a. month_2-a.month_b_2)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_2 as delta_2,
    sum((a. month_3-a.month_b_3)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_3 as delta_3,
    sum((a. month_4-a.month_b_4)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_4 as delta_4,
    sum((a. month_5-a.month_b_5)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_5 as delta_5,
    sum((a. month_6-a.month_b_6)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_6 as delta_6,
    sum((a. month_7-a.month_b_7)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_7 as delta_7,
    sum((a. month_8-a.month_b_8)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_8 as delta_8,
    sum((a. month_9-a.month_b_9)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_9 as delta_9,
    sum((a. month_10-a.month_b_10)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_10 as delta_10,
    sum((a. month_11-a.month_b_11)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_11 as delta_11,
    sum((a. month_12-a.month_b_12)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_12 as delta_12,
    
    (sum((a. month_1-a.month_b_1)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_1)+
    (sum((a. month_2-a.month_b_2)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_2)+
    (sum((a. month_3-a.month_b_3)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_3)+
    (sum((a. month_4-a.month_b_4)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_4)+
    (sum((a. month_5-a.month_b_5)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_5)+
    (sum((a. month_6-a.month_b_6)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_6)+
    (sum((a. month_7-a.month_b_7)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_7)+
    (sum((a. month_8-a.month_b_8)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_8)+
    (sum((a. month_9-a.month_b_9)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_9)+
    (sum((a. month_10-a.month_b_10)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_10)+
    (sum((a. month_11-a.month_b_11)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_11)+
    (sum((a. month_12-a.month_b_12)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_12) as all_delta,
    
                            c.rem as res
                            from needs_fact a
                            join needs_norm b on trim(a.nazv)=trim(b.nazv) 
                            and a.rem=b.rem
                            and a.year=b.year
                            and case when $year=0 then a.year>=$year else a.year=$year end 
                            left join kod_rem c on a.rem=c.kod_rem
                            
                             union all
                             
     select 0 as priority,min(a.id)+1 as id,a.nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    a.year,a.rem,a.voltage,
     chr(8287)||'Усього:' as counter,'' as s_nom,

     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    0 as koef,
    sum((a.month_1-a.month_b_1)*a.koef) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
     
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,
        	
    (sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef))-b.mon_1)::dec(14,6) as delta_1,
                             
    sum((a.month_2-a.month_b_2)*a.koef)-b.mon_2 as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef)-b.mon_3 as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef)-b.mon_4 as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef)-b.mon_5 as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef)-b.mon_6 as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef)-b.mon_7 as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef)-b.mon_8 as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef)-b.mon_9 as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef)-b.mon_10 as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef)-b.mon_11 as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef)-b.mon_12 as delta_12,
    
    sum((a.month_1-a.month_b_1)*a.koef)-b.mon_1+
        sum((a.month_2-a.month_b_2)*a.koef)-b.mon_2+
        sum((a.month_3-a.month_b_3)*a.koef)-b.mon_3+
        sum((a.month_4-a.month_b_4)*a.koef)-b.mon_4+
        sum((a.month_5-a.month_b_5)*a.koef)-b.mon_5+
        sum((a.month_6-a.month_b_6)*a.koef)-b.mon_6+
        sum((a.month_7-a.month_b_7)*a.koef)-b.mon_7+
        sum((a.month_8-a.month_b_8)*a.koef)-b.mon_8+
        sum((a.month_9-a.month_b_9)*a.koef)-b.mon_9+
        sum((a.month_10-a.month_b_10)*a.koef)-b.mon_10+
        sum((a.month_11-a.month_b_11)*a.koef)-b.mon_11+
        sum((a.month_12-a.month_b_12)*a.koef)-b.mon_12 as all_delta,
    c.rem as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
    
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     left join kod_rem c on a.rem=c.kod_rem
     group by a.nazv,a.year,a.rem,a.voltage,c.rem,b.mon_1,
     b.mon_2,b.mon_3,b.mon_4,b.mon_5,b.mon_6,b.mon_7,b.mon_8,
     b.mon_9,b.mon_10,b.mon_11,b.mon_12
     
                           union all
                           
     select 1 as priority,10480 as id,'Усього 6 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    6 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
     0 as koef,
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
   
    case when ROW_number() over (partition by a.rem,a.nazv)=1 then
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) over (partition by a.rem,a.nazv)-b.mon_1 else 0 end  as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=6 
     
     union all                      
     
    select 2 as priority,10490 as id,'Усього 10 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    10 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
     0 as koef,
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
    case when ROW_number() over (partition by a.rem,a.nazv)=1 then
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) over (partition by a.rem,a.nazv)-b.mon_1 else 0 end  as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=10 
     
     union all   

  select 3 as priority,10491 as id,'Усього 35 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    35 as voltage,
    '' as counter,'' as s_nom,
   sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
     0 as koef,
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
    case when ROW_number() over (partition by a.rem,a.nazv)=1 then
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) over (partition by a.rem,a.nazv)-b.mon_1 else 0 end  as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
      and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=35 
     
     union all   
     
     select 4 as priority,10495 as id,'Усього 150 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    150 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    0 as koef,
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
    case when ROW_number() over (partition by a.rem,a.nazv)=1 then
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) over (partition by a.rem,a.nazv)-b.mon_1 else 0 end  as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=150 
     
     union all   
                                                   
    select 7 as priority,10500 as id,'Усього:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    0 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
     0 as koef,
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
    case when ROW_number() over (partition by a.rem,a.nazv)=1 then
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) over (partition by a.rem,a.nazv)-b.mon_1 else 0 end  as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
      and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
    "
               . apply_rem($model->rem) .
               " and case when $year=0 then 1=1 else a.year=$year end 
    ) s"
               . $where . ' order by priority asc,voltage desc,rem asc,nazv asc,year desc,ascii(counter) desc';
       else
       {
        // Если вход как РЭС
           $sql = "select ROW_NUMBER() OVER(order by voltage desc,rem asc,nazv asc,year desc) AS rid,
                            id,nazv,res,counter,s_nom,
                            all_month,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else all_delta end as all_delta,
                            month_b_1,
                            month_1,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_1 
                             else (potr_1/1000)::dec(14,8) end as potr_1,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_1 end as delta_1,
                            month_b_2,
                            month_2,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_2 
                             else (potr_2/1000)::dec(14,8) end as potr_2,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_2 end as delta_2,
                            month_b_3,
                            month_3,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_3 
                             else (potr_3/1000)::dec(14,8) end as potr_3,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_3 end as delta_3,
                            month_b_4,
                            month_4,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_4 
                             else (potr_4/1000)::dec(14,8) end as potr_4,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_4 end as delta_4,
                            month_b_5,
                            month_5,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_5 
                             else (potr_5/1000)::dec(14,8) end as potr_5,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_5 end as delta_5,
                            month_b_6,
                            month_6,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_6 
                             else (potr_6/1000)::dec(14,8) end as potr_6,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_6 end as delta_6,
                            month_b_7,
                            month_7,
                           case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_7 
                             else (potr_7/1000)::dec(14,8) end as potr_7,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_7 end as delta_7,
                            month_b_8,
                            month_8,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_8 
                             else (potr_8/1000)::dec(14,8) end as potr_8,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_8 end as delta_8,
                            month_b_9,
                            month_9,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_9 
                             else (potr_9/1000)::dec(14,8) end as potr_9,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_9 end as delta_9,
                            month_b_10,
                            month_10,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_10 
                             else (potr_10/1000)::dec(14,8) end as potr_10,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_10 end as delta_10,
                            month_b_11,
                            month_11,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_11
                             else (potr_11/1000)::dec(14,8) end as potr_11,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_11 end as delta_11,
                            month_b_12,
                            month_12,
                            case when trim(rem)='05'
                             or (trim(rem)='01' and trim(nazv)='ЦРП-5')
                             or (trim(rem)='02' and trim(nazv)='РП-1')
                             or (trim(rem)='02' and trim(nazv)='РП-2')
                             or (trim(rem)='02' and trim(nazv)='РП-3')
                             or (trim(rem)='-' and nazv like '%САЗ%')
                             or (trim(rem)='-' and nazv like '%ДШЗ-1%') 
                             then potr_12 
                             else (potr_12/1000)::dec(14,8) end as potr_12,
                            case when counter<>chr(8287)||'Усього:' and substr(nazv,1,6)<>'Усього' then null else delta_12 end as delta_12,
                            voltage,year,koef from (
    select 0 as priority,a.*,
    
    (a.month_1-a.month_b_1) as potr_1,
    (a.month_2-a.month_b_2) as potr_2,
    (a.month_3-a.month_b_3) as potr_3,
    (a.month_4-a.month_b_4) as potr_4,
    (a.month_5-a.month_b_5) as potr_5,
    (a.month_6-a.month_b_6) as potr_6,
    (a.month_7-a.month_b_7) as potr_7,
    (a.month_8-a.month_b_8) as potr_8,
    (a.month_9-a.month_b_9) as potr_9,
    (a.month_10-a.month_b_10) as potr_10,
    (a.month_11-a.month_b_11) as potr_11,
    (a.month_12-a.month_b_12) as potr_12,
    
    ((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,
    
   sum((a.month_1-a.month_b_1)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_1 as delta_1,
    sum((a. month_2-a.month_b_2)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_2 as delta_2,
    sum((a. month_3-a.month_b_3)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_3 as delta_3,
    sum((a. month_4-a.month_b_4)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_4 as delta_4,
    sum((a. month_5-a.month_b_5)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_5 as delta_5,
    sum((a. month_6-a.month_b_6)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_6 as delta_6,
    sum((a. month_7-a.month_b_7)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_7 as delta_7,
    sum((a. month_8-a.month_b_8)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_8 as delta_8,
    sum((a. month_9-a.month_b_9)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_9 as delta_9,
    sum((a. month_10-a.month_b_10)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_10 as delta_10,
    sum((a. month_11-a.month_b_11)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_11 as delta_11,
    sum((a. month_12-a.month_b_12)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_12 as delta_12,
    
    (sum((a. month_1-a.month_b_1)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_1)+
    (sum((a. month_2-a.month_b_2)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_2)+
    (sum((a. month_3-a.month_b_3)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_3)+
    (sum((a. month_4-a.month_b_4)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_4)+
    (sum((a. month_5-a.month_b_5)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_5)+
    (sum((a. month_6-a.month_b_6)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_6)+
    (sum((a. month_7-a.month_b_7)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_7)+
    (sum((a. month_8-a.month_b_8)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_8)+
    (sum((a. month_9-a.month_b_9)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_9)+
    (sum((a. month_10-a.month_b_10)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_10)+
    (sum((a. month_11-a.month_b_11)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_11)+
    (sum((a. month_12-a.month_b_12)*a.koef) over(PARTITION by a.nazv,a.rem)-b.mon_12) as all_delta,
    
                            c.rem as res
                            from needs_fact a
                            join needs_norm b on trim(a.nazv)=trim(b.nazv) 
                            and a.rem=b.rem
                            and a.year=b.year
                            and case when $year=0 then a.year>=$year else a.year=$year end "
                                 . apply_rem1($role) .
                               " left join kod_rem c on a.rem=c.kod_rem
                            
                             union all
                             
     select 0 as priority,min(a.id)+1 as id,a.nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    a.year,a.rem,a.voltage,
     chr(8287)||'Усього:' as counter,'' as s_nom,

     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    0 as koef,
    sum((a.month_1-a.month_b_1)*a.koef) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
     
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,
        	
    (sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef))-b.mon_1)::dec(14,6) as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef)-b.mon_2 as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef)-b.mon_3 as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef)-b.mon_4 as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef)-b.mon_5 as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef)-b.mon_6 as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef)-b.mon_7 as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef)-b.mon_8 as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef)-b.mon_9 as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef)-b.mon_10 as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef)-b.mon_11 as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef)-b.mon_12 as delta_12,
    
    sum((a.month_1-a.month_b_1)*a.koef)-b.mon_1+
        sum((a.month_2-a.month_b_2)*a.koef)-b.mon_2+
        sum((a.month_3-a.month_b_3)*a.koef)-b.mon_3+
        sum((a.month_4-a.month_b_4)*a.koef)-b.mon_4+
        sum((a.month_5-a.month_b_5)*a.koef)-b.mon_5+
        sum((a.month_6-a.month_b_6)*a.koef)-b.mon_6+
        sum((a.month_7-a.month_b_7)*a.koef)-b.mon_7+
        sum((a.month_8-a.month_b_8)*a.koef)-b.mon_8+
        sum((a.month_9-a.month_b_9)*a.koef)-b.mon_9+
        sum((a.month_10-a.month_b_10)*a.koef)-b.mon_10+
        sum((a.month_11-a.month_b_11)*a.koef)-b.mon_11+
        sum((a.month_12-a.month_b_12)*a.koef)-b.mon_12 as all_delta,
    c.rem as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
    
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end "
      . apply_rem1($role) .
     " left join kod_rem c on a.rem=c.kod_rem
     group by a.nazv,a.year,a.rem,a.voltage,c.rem,b.mon_1,
     b.mon_2,b.mon_3,b.mon_4,b.mon_5,b.mon_6,b.mon_7,b.mon_8,
     b.mon_9,b.mon_10,b.mon_11,b.mon_12,a.koef
     
                       union all

select 1 as priority,10480 as id,'Усього 6 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    6 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
     0 as koef,
   -- sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) as potr_1,
    sum((a.month_1-a.month_b_1)*a.koef) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
     (sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)-b.mon_1))::dec(14,6) as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=6" . apply_rem1($role) .

               " union all                      
                                 
     select 2 as priority,10490 as id,'Усього 10 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    10 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
     0 as koef,
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
     (sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)-b.mon_1))::dec(14,6) as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=10" . apply_rem1($role) .
               " union all
                            
 select 3 as priority,10491 as id,'Усього 35 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    35 as voltage,
    '' as counter,'' as s_nom,
   sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    0 as koef,
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)-b.mon_1) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
     (sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)-b.mon_1))::dec(14,6) as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
      and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=35" . apply_rem1($role) .
               " union all 
    
      select 4 as priority,10495 as id,'Усього 150 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    150 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
     0 as koef,
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
     (sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)-b.mon_1))::dec(14,6) as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=150" . apply_rem1($role) .
               " union all   
                                 
    select 7 as priority,10500 as id,'Усього:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    0 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    0 as koef,
    sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)-b.mon_1) as potr_1,
    sum((a.month_2-a.month_b_2)*a.koef) as potr_2,
    sum((a.month_3-a.month_b_3)*a.koef) as potr_3,
    sum((a.month_4-a.month_b_4)*a.koef) as potr_4,
    sum((a.month_5-a.month_b_5)*a.koef) as potr_5,
    sum((a.month_6-a.month_b_6)*a.koef) as potr_6,
    sum((a.month_7-a.month_b_7)*a.koef) as potr_7,
    sum((a.month_8-a.month_b_8)*a.koef) as potr_8,
    sum((a.month_9-a.month_b_9)*a.koef) as potr_9,
    sum((a.month_10-a.month_b_10)*a.koef) as potr_10,
    sum((a.month_11-a.month_b_11)*a.koef) as potr_11,
    sum((a.month_12-a.month_b_12)*a.koef) as potr_12,
    
    sum((a.month_1-a.month_b_1)*a.koef+(a.month_2-a.month_b_2)*a.koef+(a.month_3-a.month_b_3)*a.koef+
    (a.month_4-a.month_b_4)*a.koef+
    (a.month_5-a.month_b_5)*a.koef+(a.month_6-a.month_b_6)*a.koef+(a.month_7-a.month_b_7)*a.koef+
    (a.month_8-a.month_b_8)*a.koef+
    (a.month_9-a.month_b_9)*a.koef+(a.month_10-a.month_b_10)*a.koef+(a.month_11-a.month_b_11)*a.koef+
    (a.month_12-a.month_b_12)*a.koef) as all_month,	
     (sum(f_macro1(a.rem,a.nazv,a.month_1,a.month_b_1,a.koef)-b.mon_1))::dec(14,6) as delta_1,
    sum((a.month_2-a.month_b_2)*a.koef-b.mon_2) as delta_2,
    sum((a.month_3-a.month_b_3)*a.koef-b.mon_3) as delta_3,
    sum((a.month_4-a.month_b_4)*a.koef-b.mon_4) as delta_4,
    sum((a.month_5-a.month_b_5)*a.koef-b.mon_5) as delta_5,
    sum((a.month_6-a.month_b_6)*a.koef-b.mon_6) as delta_6,
    sum((a.month_7-a.month_b_7)*a.koef-b.mon_7) as delta_7,
    sum((a.month_8-a.month_b_8)*a.koef-b.mon_8) as delta_8,
    sum((a.month_9-a.month_b_9)*a.koef-b.mon_9) as delta_9,
    sum((a.month_10-a.month_b_10)*a.koef-b.mon_10) as delta_10,
    sum((a.month_11-a.month_b_11)*a.koef-b.mon_11) as delta_11,
    sum((a.month_12-a.month_b_12)*a.koef-b.mon_12) as delta_12,
    sum(((a. month_1-a.month_b_1)*a.koef-b.mon_1)+
        ((a. month_2-a.month_b_2)*a.koef-b.mon_2)+
        ((a. month_3-a.month_b_3)*a.koef-b.mon_3)+
        ((a. month_4-a.month_b_4)*a.koef-b.mon_4)+
        ((a. month_5-a.month_b_5)*a.koef-b.mon_5)+
        ((a. month_6-a.month_b_6)*a.koef-b.mon_6)+
        ((a. month_7-a.month_b_7)*a.koef-b.mon_7)+
        ((a. month_8-a.month_b_8)*a.koef-b.mon_8)+
        ((a. month_9-a.month_b_9)*a.koef-b.mon_9)+
        ((a. month_10-a.month_b_10)*a.koef-b.mon_10)+
        ((a. month_11-a.month_b_11)*a.koef-b.mon_11)+
        ((a. month_12-a.month_b_12)*a.koef-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
      and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
    "
               . apply_rem1($role) .
               " and case when $year=0 then 1=1 else a.year=$year end 
    ) s"
               . $where . ' order by priority asc,voltage desc,rem asc,nazv asc,year desc,ascii(counter) desc';
       }
   }

          debug($sql);
          return;

                $f=fopen('aaa','w+');
                fputs($f,$sql);
                $sql1=$sql;
                if($year>2027) {
                    // Архивация sql запроса:
                    // применяется в случае если web - сервер не пропускает большой get запрос
                    // идея в замене повторяющихся частей текста, повторяющиеся блоки текста
                    //  хранятся в таблице archsql. После того как в sql - запросе (переменная $sql)
                    //  найдется повторяющаяся часть, она заменяется на символ из поля form, но перед этим ставится символ ~ (признак упаковки)
                    $z = 'select * from archsql';
                    $src = needs_fact::findBySql($z)->asArray()->all();
//                debug($sql);
//                return;
                    foreach ($src as $v) {
                        $find = trim($v['block']);
                        $find = str_replace(chr(13), '', $find);
                       $sql = str_replace(chr(13), '', $sql);
                        $pos = mb_strpos($sql, $find, 0, 'UTF-8');
//                        $pos = find_str($sql, $find);
//                        if ($pos == -1) {
                        if ($pos === false) {
                        } else {
                            $sql = str_replace($find, '~' . $v['form'], $sql);
//                        $y=mb_strlen($find,'UTF-8');
//                        $s = mb_substr($sql,$y)
                        }
                    }
                }

                $data = needs_fact::findBySql($sql1)->all();

//                debug($data);
//                return;

                $year_find = $year;
                $year=$data[0]['year'];
                $kol = count($data);

                $searchModel = new needs_fact();
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $sql1);

                $dataProvider->pagination = false;
                if($year_find<2022)
                    return $this->render('needs_fact', [
                        'dataProvider' =>$dataProvider,
                        'searchModel' => $searchModel,
                        'kol' => $kol,'sql' => $sql,'year'=> $year,'id' => $id_p]);
                else {

                    return $this->render('needs_fact_cnt', [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                        'kol' => $kol, 'sql' => $sql, 'year' => $year, 'id' => $id_p]);
                }

            } else {

                return $this->render('inputdata', [
                    'model' => $model
                ]);
            }
            }

        else{
             // Если передается параметр $sql
            // Распаковка sql - запроса
            $sql_src = $sql;
            $result_sql = '';
            $flag=0;
            $y = mb_strlen($sql, 'UTF-8');
            for($i=0;$i<$y;$i++){
                $c=mb_substr($sql,$i,1,'UTF-8');
                if($c=='~' && $flag==0){
                    $flag=1;
                }
                if($c<>'~' && $flag==1){
                    $z="select block from archsql where form='$c'";
                    $src = needs_fact::findBySql($z)->asArray()->all();
                    $s = $src[0]['block'];
                    $result_sql = $result_sql . $s;
                    $flag=0;
                    continue;
                }
                if($c<>'~' && $flag==0){
                    $result_sql = $result_sql . $c;
                }
            }
//        debug($result_sql);
//        return;
            $sql = $result_sql;
            $data = needs_fact::findBySql($sql)->all();

            $year=$data[0]['year'];

            $searchModel = new needs_fact();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $sql);
            $dataProvider->pagination = false;
            $kol = count($data);
            $n_key=0;
            for($i=0;$i<$kol;$i++){
                if($data[$i]['id']==$id_p) {
                    $n_key=$i;
                    break;
                }
            }
            if($id_p<>0)
                $dataProvider->id=$id_p;

//            debug($sql);
//            return;

            $session = Yii::$app->session;
            $session->open();
            $session->set('view', 1);

            if($year<2022)
                return $this->render('needs_fact', ['data' => $data,
                    'dataProvider' => $dataProvider, 'searchModel' => $searchModel,
                    'kol' => $kol, 'sql' => $sql,'year'=> $year,'id' => $n_key+1]);
            else {

                return $this->render('needs_fact_cnt', ['data' => $data,
                    'dataProvider' => $dataProvider, 'searchModel' => $searchModel,
                    'kol' => $kol, 'sql' => $sql_src, 'year' => $year, 'id' => $n_key + 1]);
            }
        }
    }

   // Отчет по потреблению по выбранным годам ("Звіт по рокам" - из пункта меню "Звіти")
    public function actionReport_years()
    {
        $this->curpage=1;
        if(1==1) {
            $model = new Inputreport_years();
            $augment = 2; //  константа - нужно поменять если будут добавляться года в список (В 2022 году нужно поменять на 1 - пока 2 для отладки программы в 2021 году)
            $const_year=date('Y')+$augment;
            if ($model->load(Yii::$app->request->post())) {
                // Создание поискового sql выражения
                $where = '';
                if (!empty($model->up)) {
                    $where .= ' and (delta_1>0 or delta_2>0 or delta_3>0  or delta_4>0  or delta_5>0
                     or delta_6>0  or delta_7>0  or delta_8>0  or delta_9>0  or delta_10>0
                      or delta_11>0  or delta_12>0)'   ;
                }
                $list_years = '';
                $list = '';
                $year = 2022;
                $flag2022=-1;  // Признак что будет выборка после 2021 года,
                $old_d=0;
                $new_d=0;
                if (!empty($model->year1)) {
                    $list_years .= '2019' . ',' ;
                    $list .= '2019' . ',' ;
                    $flag2022=0;
                    $old_d=1;
               }
                if (!empty($model->year2)) {
                    $list_years .= '2020' . ',' ;
                    $list .= '2020' . ',' ;
                    $flag2022=0;
                    $old_d=1;
                }
                if (!empty($model->year3)) {
                    $list_years .= '2021' . ',' ;
                    $list .= '2021' . ',' ;
                    $flag2022=0;
                    $old_d=1;
                }
                if (!empty($model->year4)) {
                    $flag2022=1;
                    $new_d=1;
                    $list .= '2022' . ',' ;
                }

                if (!empty($model->years_all) || ($old_d==0 && $new_d==0)) {
                    // Если выбраны все годы
                    $list_years = '';
                    $list = 'Всі роки.';
                    $flag2022=2 ;
                    $year = 0;
                    $new_d=1;
                    $old_d=1;
                }

                if (!empty($list_years)) {
                    // Выборочные годы до 2022
                    $where.= ' and year in ('.$list_years.'1)';
                }

                if ($flag2022==2)  {
                    $where.= ' and (year < 2022 and year>0) ';
                }


//                debug($list_years);
//                return;

                if(!isset(Yii::$app->user->identity->role))
                {      $flag=0;
                    $role=0;
                }
                else{
                    $role=Yii::$app->user->identity->role;
                }

                $where = trim($where);
                if (empty($where)) $where = '';
                else {
                    $where = ' where ' . substr($where, 4) . ' or id>=10480 ' ;
                }

                $list_years = substr($list_years,0,strlen($list_years)-1);
                $list = substr($list,0,strlen($list)-1);
                if(empty($list_years) || strlen($list_years)==0) $list_years = '1';

//                debug($where);
//                debug($model->up);
//                return;

                // до 2022 года расход эл-энергии учитывается в целом по всей подстанции
                if($old_d==1) {
                    if ($role < 4)
                        // Если вход как администратор
                        $sql1 = "select priority,ROW_NUMBER() OVER(order by voltage desc,rem asc,nazv asc,year desc) AS rid,
                            id,nazv,res,all_month,all_delta,month_1,delta_1,month_2,delta_2,month_3,delta_3,month_4,delta_4,month_5,delta_5,month_6,delta_6,month_7,delta_7,month_8,delta_8,
            month_9,delta_9,month_10,delta_10,month_11,delta_11,month_12,delta_12,voltage,year from (
    select 0 as priority,a.*,
    (a.month_1+a.month_2+a.month_3+a.month_4+
    a.month_5+a.month_6+a.month_7+a.month_8+
    a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    a. month_1-b.mon_1 as delta_1,
    a. month_2-b.mon_2 as delta_2,
    a. month_3-b.mon_3 as delta_3,
    a. month_4-b.mon_4 as delta_4,
    a. month_5-b.mon_5 as delta_5,
    a. month_6-b.mon_6 as delta_6,
    a. month_7-b.mon_7 as delta_7,
    a. month_8-b.mon_8 as delta_8,
    a. month_9-b.mon_9 as delta_9,
    a. month_10-b.mon_10 as delta_10,
    a. month_11-b.mon_11 as delta_11,
    a. month_12-b.mon_12 as delta_12,
    (a. month_1-b.mon_1)+
    (a. month_2-b.mon_2)+
    (a. month_3-b.mon_3)+
    (a. month_4-b.mon_4)+
    (a. month_5-b.mon_5)+
    (a. month_6-b.mon_6)+
    (a. month_7-b.mon_7)+
    (a. month_8-b.mon_8)+
    (a. month_9-b.mon_9)+
    (a. month_10-b.mon_10)+
    (a. month_11-b.mon_11)+
    (a. month_12-b.mon_12) as all_delta,
                            c.rem as res
                            from needs_fact a
                            join needs_norm b on trim(a.nazv)=trim(b.nazv) 
                            and a.rem=b.rem
                            and a.year=b.year
                            and case when $year=0 then 1=1 else a.year in($list_years) end 
                            left join kod_rem c on a.rem=c.kod_rem
                           union all
                           
    select 1 as priority,10480 as id,'Усього 6 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    6 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then 1=1 else a.year in($list_years) end 
     where a.voltage=6
     union all                      
     
    select 2 as priority,10490 as id,'Усього 10 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    10 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then 1=1 else a.year in($list_years) end 
     where a.voltage=10
     union all   

 select 3 as priority,10491 as id,'Усього 35 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    35 as voltage,
    '' as counter,'' as s_nom,
   sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then 1=1 else a.year in($list_years) end 
     where a.voltage=35
     union all   
     
     select 4 as priority,10495 as id,'Усього 150 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    150 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then 1=1 else a.year in($list_years) end 
     where a.voltage=150
     union all   
                                                   
    select 7 as priority,10500 as id,'Усього:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    0 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
    "
                            .
                            " and case when $year=0 then 1=1 else a.year in ($list_years) end and a.year<2022
    ) s"
                            . $where . ' order by priority asc,voltage desc,rem asc,nazv asc,year desc';
                    else
                        // Если вход как РЭС до 2022 года
                        $sql1 = "select ROW_NUMBER() OVER(order by voltage desc,rem asc,nazv asc,year desc) AS rid,
                            id,nazv,res,all_month,all_delta,month_1,delta_1,month_2,delta_2,month_3,delta_3,month_4,delta_4,month_5,delta_5,month_6,delta_6,month_7,delta_7,month_8,delta_8,
            month_9,delta_9,month_10,delta_10,month_11,delta_11,month_12,delta_12,voltage,year from (
    select 0 as priority,a.*,
    (a.month_1+a.month_2+a.month_3+a.month_4+
    a.month_5+a.month_6+a.month_7+a.month_8+
    a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    a. month_1-b.mon_1 as delta_1,
    a. month_2-b.mon_2 as delta_2,
    a. month_3-b.mon_3 as delta_3,
    a. month_4-b.mon_4 as delta_4,
    a. month_5-b.mon_5 as delta_5,
    a. month_6-b.mon_6 as delta_6,
    a. month_7-b.mon_7 as delta_7,
    a. month_8-b.mon_8 as delta_8,
    a. month_9-b.mon_9 as delta_9,
    a. month_10-b.mon_10 as delta_10,
    a. month_11-b.mon_11 as delta_11,
    a. month_12-b.mon_12 as delta_12,
    (a. month_1-b.mon_1)+
    (a. month_2-b.mon_2)+
    (a. month_3-b.mon_3)+
    (a. month_4-b.mon_4)+
    (a. month_5-b.mon_5)+
    (a. month_6-b.mon_6)+
    (a. month_7-b.mon_7)+
    (a. month_8-b.mon_8)+
    (a. month_9-b.mon_9)+
    (a. month_10-b.mon_10)+
    (a. month_11-b.mon_11)+
    (a. month_12-b.mon_12) as all_delta,
                            c.rem as res
                            from needs_fact a
                            join needs_norm b on trim(a.nazv)=trim(b.nazv) 
                            and a.rem=b.rem
                            and a.year=b.year
                            and case when $year=0 then 1=1 else a.year=$year end 
                            left join kod_rem c on a.rem=c.kod_rem
                            union all

select 1 as priority,10480 as id,'Усього 6 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    6 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=6" . apply_rem1($role) .
                            " union all                      
                                 
     select 2 as priority,10490 as id,'Усього 10 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    10 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=10" . apply_rem1($role) .
                            " union all
                            
select 3 as priority,10491 as id,'Усього 35 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    35 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=35" . apply_rem1($role) .
                            " union all 
    
      select 4 as priority,10495 as id,'Усього 150 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    150 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when 2021=0 then 1=1 else a.year=2021 end 
     where a.voltage=150" . apply_rem1($role) .
                            " union all   
                                 
    select 7 as priority,10500 as id,'Усього:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    0 as voltage,
    '' as counter,'' as s_nom,
     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    sum(a.month_1+a.month_2+a.month_3+a.month_4+
        a.month_5+a.month_6+a.month_7+a.month_8+
        a.month_9+a.month_10+a.month_11+a.month_12) as all_month,	
    sum(a. month_1-b.mon_1) as delta_1,
    sum(a. month_2-b.mon_2) as delta_2,
    sum(a. month_3-b.mon_3) as delta_3,
    sum(a. month_4-b.mon_4) as delta_4,
    sum(a. month_5-b.mon_5) as delta_5,
    sum(a. month_6-b.mon_6) as delta_6,
    sum(a. month_7-b.mon_7) as delta_7,
    sum(a. month_8-b.mon_8) as delta_8,
    sum(a. month_9-b.mon_9) as delta_9,
    sum(a. month_10-b.mon_10) as delta_10,
    sum(a. month_11-b.mon_11) as delta_11,
    sum(a. month_12-b.mon_12) as delta_12,
    sum((a. month_1-b.mon_1)+
        (a. month_2-b.mon_2)+
        (a. month_3-b.mon_3)+
        (a. month_4-b.mon_4)+
        (a. month_5-b.mon_5)+
        (a. month_6-b.mon_6)+
        (a. month_7-b.mon_7)+
        (a. month_8-b.mon_8)+
        (a. month_9-b.mon_9)+
        (a. month_10-b.mon_10)+
        (a. month_11-b.mon_11)+
        (a. month_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
    "
                            . apply_rem1($role) .
                            " and case when $year=0 then 1=1 else a.year=$year end 
    ) s"
                            . $where . ' order by priority asc,voltage desc,rem asc,nazv asc,year desc';


                    if(!empty($model->sum))
                        $sql1 = 'select priority,nazv,res,voltage,SUM(all_month) AS all_month,SUM(all_delta) as all_delta,SUM(month_1) as month_1,
                        SUM(delta_1)  as delta_1,SUM(month_2) as month_2,SUM(delta_2)  as delta_2,SUM(month_3) as month_3,
                        SUM(delta_3) as delta_3,SUM(month_4) as month_4,SUM(delta_4)  as delta_4,SUM(month_5) as month_5,
                        SUM(delta_5)  as delta_5,SUM(month_6) as month_6,SUM(delta_6)  as delta_6,
                        SUM(month_7) as month_7,SUM(delta_7)  as delta_7,SUM(month_8) as month_8,SUM(delta_8)  as delta_8,
                        SUM(month_9) as month_9,SUM(delta_9)  as delta_9,SUM(month_10) as month_10,SUM(delta_10)  as delta_10,
                        SUM(month_11) as month_11,SUM(delta_11)  as delta_11,SUM(month_12) as month_12,SUM(delta_12)  as delta_12
                        from (' . $sql1 . ') qqq GROUP BY nazv,res,voltage,priority order by priority asc,voltage desc,res asc,nazv asc';
                    else
                        $sql1 = 'select priority,year,nazv,res,voltage,all_month,all_delta,month_1,
                        delta_1,month_2,delta_2,month_3,
                        delta_3,month_4,delta_4,month_5,
                        delta_5,month_6,delta_6,
                        month_7,delta_7,month_8,delta_8,
                        month_9,delta_9,month_10,delta_10,
                        month_11,delta_11,month_12,delta_12
                        from (' . $sql1 . ') qqq  order by priority asc,voltage desc,res asc,nazv asc';
                }

// Начиная с 2022 (применяется другой принцип учета на подстанциях - расход эл-энергии учитывается по каждому счетчику)
                if($new_d==1){
                    if ($role < 4)
                        // Если вход под админом
                        $sql2 = "select priority,ROW_NUMBER() OVER(order by voltage desc,rem asc,nazv asc,year desc) AS rid,
                            id,nazv,res,counter,s_nom,
                            all_month,
                            case when counter<>chr(8287)||'Усього:' then null else all_delta end as all_delta,
                            month_b_1,
                            month_1,
                            potr_1,
                            case when counter<>chr(8287)||'Усього:' then null else delta_1 end as delta_1,
                            month_b_2,
                            month_2,
                            potr_2,
                            case when counter<>chr(8287)||'Усього:' then null else delta_2 end as delta_2,
                            month_b_3,
                            month_3,
                            potr_3,
                            case when counter<>chr(8287)||'Усього:' then null else delta_3 end as delta_3,
                            month_b_4,
                            month_4,
                            potr_4,
                            case when counter<>chr(8287)||'Усього:' then null else delta_4 end as delta_4,
                            month_b_5,
                            month_5,
                            potr_5,
                            case when counter<>chr(8287)||'Усього:' then null else delta_5 end as delta_5,
                            month_b_6,
                            month_6,
                            potr_6,
                            case when counter<>chr(8287)||'Усього:' then null else delta_6 end as delta_6,
                            month_b_7,
                            month_7,
                            potr_7,
                            case when counter<>chr(8287)||'Усього:' then null else delta_7 end as delta_7,
                            month_b_8,
                            month_8,
                            potr_8,
                            case when counter<>chr(8287)||'Усього:' then null else delta_8 end as delta_8,
                            month_b_9,
                            month_9,
                            potr_9,
                            case when counter<>chr(8287)||'Усього:' then null else delta_9 end as delta_9,
                            month_b_10,
                            month_10,
                            potr_10,
                            case when counter<>chr(8287)||'Усього:' then null else delta_10 end as delta_10,
                            month_b_11,
                            month_11,
                            potr_11,
                            case when counter<>chr(8287)||'Усього:' then null else delta_11 end as delta_11,
                            month_b_12,
                            month_12,
                            potr_12,
                            case when counter<>chr(8287)||'Усього:' then null else delta_12 end as delta_12,
                            voltage,year from (
    select 0 as priority,a.*,
    
    (a.month_1-a.month_b_1) as potr_1,
    (a.month_2-a.month_b_2) as potr_2,
    (a.month_3-a.month_b_3) as potr_3,
    (a.month_4-a.month_b_4) as potr_4,
    (a.month_5-a.month_b_5) as potr_5,
    (a.month_6-a.month_b_6) as potr_6,
    (a.month_7-a.month_b_7) as potr_7,
    (a.month_8-a.month_b_8) as potr_8,
    (a.month_9-a.month_b_9) as potr_9,
    (a.month_10-a.month_b_10) as potr_10,
    (a.month_11-a.month_b_11) as potr_11,
    (a.month_12-a.month_b_12) as potr_12,
    
    ((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,
    
   sum(a.month_1-a.month_b_1) over(PARTITION by a.nazv,a.rem)-b.mon_1 as delta_1,
    sum(a. month_2-a.month_b_2) over(PARTITION by a.nazv,a.rem)-b.mon_2 as delta_2,
    sum(a. month_3-a.month_b_3) over(PARTITION by a.nazv,a.rem)-b.mon_3 as delta_3,
    sum(a. month_4-a.month_b_4) over(PARTITION by a.nazv,a.rem)-b.mon_4 as delta_4,
    sum(a. month_5-a.month_b_5) over(PARTITION by a.nazv,a.rem)-b.mon_5 as delta_5,
    sum(a. month_6-a.month_b_6) over(PARTITION by a.nazv,a.rem)-b.mon_6 as delta_6,
    sum(a. month_7-a.month_b_7) over(PARTITION by a.nazv,a.rem)-b.mon_7 as delta_7,
    sum(a. month_8-a.month_b_8) over(PARTITION by a.nazv,a.rem)-b.mon_8 as delta_8,
    sum(a. month_9-a.month_b_9) over(PARTITION by a.nazv,a.rem)-b.mon_9 as delta_9,
    sum(a. month_10-a.month_b_10) over(PARTITION by a.nazv,a.rem)-b.mon_10 as delta_10,
    sum(a. month_11-a.month_b_11) over(PARTITION by a.nazv,a.rem)-b.mon_11 as delta_11,
    sum(a. month_12-a.month_b_12) over(PARTITION by a.nazv,a.rem)-b.mon_12 as delta_12,
    
    (sum(a. month_1-a.month_b_1) over(PARTITION by a.nazv,a.rem)-b.mon_1)+
    (sum(a. month_2-a.month_b_2) over(PARTITION by a.nazv,a.rem)-b.mon_2)+
    (sum(a. month_3-a.month_b_3) over(PARTITION by a.nazv,a.rem)-b.mon_3)+
    (sum(a. month_4-a.month_b_4) over(PARTITION by a.nazv,a.rem)-b.mon_4)+
    (sum(a. month_5-a.month_b_5) over(PARTITION by a.nazv,a.rem)-b.mon_5)+
    (sum(a. month_6-a.month_b_6) over(PARTITION by a.nazv,a.rem)-b.mon_6)+
    (sum(a. month_7-a.month_b_7) over(PARTITION by a.nazv,a.rem)-b.mon_7)+
    (sum(a. month_8-a.month_b_8) over(PARTITION by a.nazv,a.rem)-b.mon_8)+
    (sum(a. month_9-a.month_b_9) over(PARTITION by a.nazv,a.rem)-b.mon_9)+
    (sum(a. month_10-a.month_b_10) over(PARTITION by a.nazv,a.rem)-b.mon_10)+
    (sum(a. month_11-a.month_b_11) over(PARTITION by a.nazv,a.rem)-b.mon_11)+
    (sum(a. month_12-a.month_b_12) over(PARTITION by a.nazv,a.rem)-b.mon_12) as all_delta,
    
                            c.rem as res
                            from needs_fact a
                            join needs_norm b on trim(a.nazv)=trim(b.nazv) 
                            and a.rem=b.rem
                            and a.year=b.year
                            and case when $year=0 then a.year>=$year else a.year=$year end 
                            left join kod_rem c on a.rem=c.kod_rem
                            
                             union all
                             
     select 0 as priority,min(a.id)+1 as id,a.nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    a.year,a.rem,a.voltage,
     chr(8287)||'Усього:' as counter,'' as s_nom,

     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
     
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,
        	
    sum(a.month_1-a.month_b_1)-b.mon_1 as delta_1,
    sum(a.month_2-a.month_b_2)-b.mon_2 as delta_2,
    sum(a.month_3-a.month_b_3)-b.mon_3 as delta_3,
    sum(a.month_4-a.month_b_4)-b.mon_4 as delta_4,
    sum(a.month_5-a.month_b_5)-b.mon_5 as delta_5,
    sum(a.month_6-a.month_b_6)-b.mon_6 as delta_6,
    sum(a.month_7-a.month_b_7)-b.mon_7 as delta_7,
    sum(a.month_8-a.month_b_8)-b.mon_8 as delta_8,
    sum(a.month_9-a.month_b_9)-b.mon_9 as delta_9,
    sum(a.month_10-a.month_b_10)-b.mon_10 as delta_10,
    sum(a.month_11-a.month_b_11)-b.mon_11 as delta_11,
    sum(a.month_12-a.month_b_12)-b.mon_12 as delta_12,
    
    sum(a.month_1-a.month_b_1)-b.mon_1+
        sum(a.month_2-a.month_b_2)-b.mon_2+
        sum(a.month_3-a.month_b_3)-b.mon_3+
        sum(a.month_4-a.month_b_4)-b.mon_4+
        sum(a.month_5-a.month_b_5)-b.mon_5+
        sum(a.month_6-a.month_b_6)-b.mon_6+
        sum(a.month_7-a.month_b_7)-b.mon_7+
        sum(a.month_8-a.month_b_8)-b.mon_8+
        sum(a.month_9-a.month_b_9)-b.mon_9+
        sum(a.month_10-a.month_b_10)-b.mon_10+
        sum(a.month_11-a.month_b_11)-b.mon_11+
        sum(a.month_12-a.month_b_12)-b.mon_12 as all_delta,
    c.rem as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
    
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     left join kod_rem c on a.rem=c.kod_rem
     group by a.nazv,a.year,a.rem,a.voltage,c.rem,b.mon_1,
     b.mon_2,b.mon_3,b.mon_4,b.mon_5,b.mon_6,b.mon_7,b.mon_8,
     b.mon_9,b.mon_10,b.mon_11,b.mon_12
     
                           union all
                           
     select 1 as priority,10480 as id,'Усього 6 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    6 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,

    sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=6
     
     union all                      
     
    select 2 as priority,10490 as id,'Усього 10 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    10 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=10
     
     union all   

  select 3 as priority,10491 as id,'Усього 35 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    35 as voltage,
    '' as counter,'' as s_nom,
   sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
      and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=35
     
     union all   
     
     select 4 as priority,10495 as id,'Усього 150 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    150 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=150
     
     union all   
                                                   
    select 7 as priority,10500 as id,'Усього:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    0 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,
    	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
      and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
    "
                            .
                            " and case when $year=0 then 1=1 else a.year=$year end and a.year<>0
    ) s"
                             . ' order by priority asc,voltage desc,rem asc,nazv asc,year desc,ascii(counter) desc';
                    else
                    {
                        // Если вход как РЭС
                        $sql = "select ROW_NUMBER() OVER(order by voltage desc,rem asc,nazv asc,year desc) AS rid,
                            id,nazv,res,counter,s_nom,
                            all_month,
                            case when counter<>chr(8287)||'Усього:' then null else all_delta end as all_delta,
                            month_b_1,
                            month_1,
                            potr_1,
                            case when counter<>chr(8287)||'Усього:' then null else delta_1 end as delta_1,
                            month_b_2,
                            month_2,
                            potr_2,
                            case when counter<>chr(8287)||'Усього:' then null else delta_2 end as delta_2,
                            month_b_3,
                            month_3,
                            potr_3,
                            case when counter<>chr(8287)||'Усього:' then null else delta_3 end as delta_3,
                            month_b_4,
                            month_4,
                            potr_4,
                            case when counter<>chr(8287)||'Усього:' then null else delta_4 end as delta_4,
                            month_b_5,
                            month_5,
                            potr_5,
                            case when counter<>chr(8287)||'Усього:' then null else delta_5 end as delta_5,
                            month_b_6,
                            month_6,
                            potr_6,
                            case when counter<>chr(8287)||'Усього:' then null else delta_6 end as delta_6,
                            month_b_7,
                            month_7,
                            potr_7,
                            case when counter<>chr(8287)||'Усього:' then null else delta_7 end as delta_7,
                            month_b_8,
                            month_8,
                            potr_8,
                            case when counter<>chr(8287)||'Усього:' then null else delta_8 end as delta_8,
                            month_b_9,
                            month_9,
                            potr_9,
                            case when counter<>chr(8287)||'Усього:' then null else delta_9 end as delta_9,
                            month_b_10,
                            month_10,
                            potr_10,
                            case when counter<>chr(8287)||'Усього:' then null else delta_10 end as delta_10,
                            month_b_11,
                            month_11,
                            potr_11,
                            case when counter<>chr(8287)||'Усього:' then null else delta_11 end as delta_11,
                            month_b_12,
                            month_12,
                            potr_12,
                            case when counter<>chr(8287)||'Усього:' then null else delta_12 end as delta_12,
                            voltage,year from (
    select 0 as priority,a.*,
    
    (a.month_1-a.month_b_1) as potr_1,
    (a.month_2-a.month_b_2) as potr_2,
    (a.month_3-a.month_b_3) as potr_3,
    (a.month_4-a.month_b_4) as potr_4,
    (a.month_5-a.month_b_5) as potr_5,
    (a.month_6-a.month_b_6) as potr_6,
    (a.month_7-a.month_b_7) as potr_7,
    (a.month_8-a.month_b_8) as potr_8,
    (a.month_9-a.month_b_9) as potr_9,
    (a.month_10-a.month_b_10) as potr_10,
    (a.month_11-a.month_b_11) as potr_11,
    (a.month_12-a.month_b_12) as potr_12,
    
    ((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,
    
   sum(a.month_1-a.month_b_1) over(PARTITION by a.nazv,a.rem)-b.mon_1 as delta_1,
    sum(a. month_2-a.month_b_2) over(PARTITION by a.nazv,a.rem)-b.mon_2 as delta_2,
    sum(a. month_3-a.month_b_3) over(PARTITION by a.nazv,a.rem)-b.mon_3 as delta_3,
    sum(a. month_4-a.month_b_4) over(PARTITION by a.nazv,a.rem)-b.mon_4 as delta_4,
    sum(a. month_5-a.month_b_5) over(PARTITION by a.nazv,a.rem)-b.mon_5 as delta_5,
    sum(a. month_6-a.month_b_6) over(PARTITION by a.nazv,a.rem)-b.mon_6 as delta_6,
    sum(a. month_7-a.month_b_7) over(PARTITION by a.nazv,a.rem)-b.mon_7 as delta_7,
    sum(a. month_8-a.month_b_8) over(PARTITION by a.nazv,a.rem)-b.mon_8 as delta_8,
    sum(a. month_9-a.month_b_9) over(PARTITION by a.nazv,a.rem)-b.mon_9 as delta_9,
    sum(a. month_10-a.month_b_10) over(PARTITION by a.nazv,a.rem)-b.mon_10 as delta_10,
    sum(a. month_11-a.month_b_11) over(PARTITION by a.nazv,a.rem)-b.mon_11 as delta_11,
    sum(a. month_12-a.month_b_12) over(PARTITION by a.nazv,a.rem)-b.mon_12 as delta_12,
    
    (sum(a. month_1-a.month_b_1) over(PARTITION by a.nazv,a.rem)-b.mon_1)+
    (sum(a. month_2-a.month_b_2) over(PARTITION by a.nazv,a.rem)-b.mon_2)+
    (sum(a. month_3-a.month_b_3) over(PARTITION by a.nazv,a.rem)-b.mon_3)+
    (sum(a. month_4-a.month_b_4) over(PARTITION by a.nazv,a.rem)-b.mon_4)+
    (sum(a. month_5-a.month_b_5) over(PARTITION by a.nazv,a.rem)-b.mon_5)+
    (sum(a. month_6-a.month_b_6) over(PARTITION by a.nazv,a.rem)-b.mon_6)+
    (sum(a. month_7-a.month_b_7) over(PARTITION by a.nazv,a.rem)-b.mon_7)+
    (sum(a. month_8-a.month_b_8) over(PARTITION by a.nazv,a.rem)-b.mon_8)+
    (sum(a. month_9-a.month_b_9) over(PARTITION by a.nazv,a.rem)-b.mon_9)+
    (sum(a. month_10-a.month_b_10) over(PARTITION by a.nazv,a.rem)-b.mon_10)+
    (sum(a. month_11-a.month_b_11) over(PARTITION by a.nazv,a.rem)-b.mon_11)+
    (sum(a. month_12-a.month_b_12) over(PARTITION by a.nazv,a.rem)-b.mon_12) as all_delta,
    
                            c.rem as res
                            from needs_fact a
                            join needs_norm b on trim(a.nazv)=trim(b.nazv) 
                            and a.rem=b.rem
                            and a.year=b.year
                            and case when $year=0 then a.year>=$year else a.year=$year end 
                            left join kod_rem c on a.rem=c.kod_rem
                            
                             union all
                             
     select 0 as priority,min(a.id)+1 as id,a.nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    a.year,a.rem,a.voltage,
     chr(8287)||'Усього:' as counter,'' as s_nom,

     sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
     
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,
        	
    sum(a.month_1-a.month_b_1)-b.mon_1 as delta_1,
    sum(a.month_2-a.month_b_2)-b.mon_2 as delta_2,
    sum(a.month_3-a.month_b_3)-b.mon_3 as delta_3,
    sum(a.month_4-a.month_b_4)-b.mon_4 as delta_4,
    sum(a.month_5-a.month_b_5)-b.mon_5 as delta_5,
    sum(a.month_6-a.month_b_6)-b.mon_6 as delta_6,
    sum(a.month_7-a.month_b_7)-b.mon_7 as delta_7,
    sum(a.month_8-a.month_b_8)-b.mon_8 as delta_8,
    sum(a.month_9-a.month_b_9)-b.mon_9 as delta_9,
    sum(a.month_10-a.month_b_10)-b.mon_10 as delta_10,
    sum(a.month_11-a.month_b_11)-b.mon_11 as delta_11,
    sum(a.month_12-a.month_b_12)-b.mon_12 as delta_12,
    
    sum(a.month_1-a.month_b_1)-b.mon_1+
        sum(a.month_2-a.month_b_2)-b.mon_2+
        sum(a.month_3-a.month_b_3)-b.mon_3+
        sum(a.month_4-a.month_b_4)-b.mon_4+
        sum(a.month_5-a.month_b_5)-b.mon_5+
        sum(a.month_6-a.month_b_6)-b.mon_6+
        sum(a.month_7-a.month_b_7)-b.mon_7+
        sum(a.month_8-a.month_b_8)-b.mon_8+
        sum(a.month_9-a.month_b_9)-b.mon_9+
        sum(a.month_10-a.month_b_10)-b.mon_10+
        sum(a.month_11-a.month_b_11)-b.mon_11+
        sum(a.month_12-a.month_b_12)-b.mon_12 as all_delta,
    c.rem as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
    
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     left join kod_rem c on a.rem=c.kod_rem
     group by a.nazv,a.year,a.rem,a.voltage,c.rem,b.mon_1,
     b.mon_2,b.mon_3,b.mon_4,b.mon_5,b.mon_6,b.mon_7,b.mon_8,
     b.mon_9,b.mon_10,b.mon_11,b.mon_12
     
                       union all

select 1 as priority,10480 as id,'Усього 6 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    6 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,

    sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=6" . apply_rem1($role) .

                            " union all                      
                                 
     select 2 as priority,10490 as id,'Усього 10 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    10 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=10" . apply_rem1($role) .
                            " union all
                            
 select 3 as priority,10491 as id,'Усього 35 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    35 as voltage,
    '' as counter,'' as s_nom,
   sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
      and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=35" . apply_rem1($role) .
                            " union all 
    
      select 4 as priority,10495 as id,'Усього 150 кВ:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    150 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
     and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
     where a.voltage=150" . apply_rem1($role) .
                            " union all   
                                 
    select 7 as priority,10500 as id,'Усього:' as nazv,
    sum(a.month_1) as month_1,
    sum(a.month_2) as month_2,
    sum(a.month_3) as month_3,
    sum(a.month_4) as month_4,
    sum(a.month_5) as month_5,
    sum(a.month_6) as month_6,
    sum(a.month_7) as month_7,
    sum(a.month_8) as month_8,
    sum(a.month_9) as month_9,
    sum(a.month_10) as month_10,
    sum(a.month_11) as month_11,
    sum(a.month_12) as month_12,
    0 as year,
    '' as rem,
    0 as voltage,
    '' as counter,'' as s_nom,
    sum(a.month_b_1) as month_b_1,
    sum(a.month_b_2) as month_b_2,
    sum(a.month_b_3) as month_b_3,
    sum(a.month_b_4) as month_b_4,
    sum(a.month_b_5) as month_b_5,
    sum(a.month_b_6) as month_b_6,
    sum(a.month_b_7) as month_b_7,
    sum(a.month_b_8) as month_b_8,
    sum(a.month_b_9) as month_b_9,
    sum(a.month_b_10) as month_b_10,
    sum(a.month_b_11) as month_b_11,
    sum(a.month_b_12) as month_b_12,
    
     sum(a.month_1-a.month_b_1) as potr_1,
    sum(a.month_2-a.month_b_2) as potr_2,
    sum(a.month_3-a.month_b_3) as potr_3,
    sum(a.month_4-a.month_b_4) as potr_4,
    sum(a.month_5-a.month_b_5) as potr_5,
    sum(a.month_6-a.month_b_6) as potr_6,
    sum(a.month_7-a.month_b_7) as potr_7,
    sum(a.month_8-a.month_b_8) as potr_8,
    sum(a.month_9-a.month_b_9) as potr_9,
    sum(a.month_10-a.month_b_10) as potr_10,
    sum(a.month_11-a.month_b_11) as potr_11,
    sum(a.month_12-a.month_b_12) as potr_12,
    
    sum((a.month_1-a.month_b_1)+(a.month_2-a.month_b_2)+(a.month_3-a.month_b_3)+(a.month_4-a.month_b_4)+
    (a.month_5-a.month_b_5)+(a.month_6-a.month_b_6)+(a.month_7-a.month_b_7)+(a.month_8-a.month_b_8)+
    (a.month_9-a.month_b_9)+(a.month_10-a.month_b_10)+(a.month_11-a.month_b_11)+(a.month_12-a.month_b_12)) as all_month,
    	
    sum(a.month_1-a.month_b_1-b.mon_1) as delta_1,
    sum(a.month_2-a.month_b_2-b.mon_2) as delta_2,
    sum(a.month_3-a.month_b_3-b.mon_3) as delta_3,
    sum(a.month_4-a.month_b_4-b.mon_4) as delta_4,
    sum(a.month_5-a.month_b_5-b.mon_5) as delta_5,
    sum(a.month_6-a.month_b_6-b.mon_6) as delta_6,
    sum(a.month_7-a.month_b_7-b.mon_7) as delta_7,
    sum(a.month_8-a.month_b_8-b.mon_8) as delta_8,
    sum(a.month_9-a.month_b_9-b.mon_9) as delta_9,
    sum(a.month_10-a.month_b_10-b.mon_10) as delta_10,
    sum(a.month_11-a.month_b_11-b.mon_11) as delta_11,
    sum(a.month_12-a.month_b_12-b.mon_12) as delta_12,
    sum((a. month_1-a.month_b_1-b.mon_1)+
        (a. month_2-a.month_b_2-b.mon_2)+
        (a. month_3-a.month_b_3-b.mon_3)+
        (a. month_4-a.month_b_4-b.mon_4)+
        (a. month_5-a.month_b_5-b.mon_5)+
        (a. month_6-a.month_b_6-b.mon_6)+
        (a. month_7-a.month_b_7-b.mon_7)+
        (a. month_8-a.month_b_8-b.mon_8)+
        (a. month_9-a.month_b_9-b.mon_9)+
        (a. month_10-a.month_b_10-b.mon_10)+
        (a. month_11-a.month_b_11-b.mon_11)+
        (a. month_12-a.month_b_12-b.mon_12)) as all_delta,
    '' as res
    from needs_fact a
    join needs_norm b on trim(a.nazv)=trim(b.nazv) and a.year=b.year 
    and a.rem=b.rem
      and 1=1 and case when $year=0 then a.year>=$year else a.year=$year end 
    "
                            . apply_rem1($role) .
                            " and case when $year=0 then 1=1 else a.year=$year end 
    ) s"
                            . $where . ' order by priority asc,voltage desc,rem asc,nazv asc,year desc,ascii(counter) desc';
                    }

                    if(!empty($model->sum))
                        $sql2 = 'select priority,nazv,res,voltage,SUM(all_month) AS all_month,SUM(all_delta) as all_delta,SUM(month_1) as month_1,
                        SUM(delta_1)  as delta_1,SUM(month_2) as month_2,SUM(delta_2)  as delta_2,SUM(month_3) as month_3,
                        SUM(delta_3) as delta_3,SUM(month_4) as month_4,SUM(delta_4)  as delta_4,SUM(month_5) as month_5,
                        SUM(delta_5)  as delta_5,SUM(month_6) as month_6,SUM(delta_6)  as delta_6,
                        SUM(month_7) as month_7,SUM(delta_7)  as delta_7,SUM(month_8) as month_8,SUM(delta_8)  as delta_8,
                        SUM(month_9) as month_9,SUM(delta_9)  as delta_9,SUM(month_10) as month_10,SUM(delta_10)  as delta_10,
                        SUM(month_11) as month_11,SUM(delta_11)  as delta_11,SUM(month_12) as month_12,SUM(delta_12)  as delta_12
                        from (' . $sql2 . ') qqq GROUP BY nazv,res,voltage,priority order by priority asc,voltage desc,res asc,nazv asc';
                    else
                        $sql2 = 'select priority,year,nazv,res,voltage,all_month,all_delta,month_1,
                        delta_1,month_2,delta_2,month_3,
                        delta_3,month_4,delta_4,month_5,
                        delta_5,month_6,delta_6,
                        month_7,delta_7,month_8,delta_8,
                        month_9,delta_9,month_10,delta_10,
                        month_11,delta_11,month_12,delta_12
                        from (' . $sql2 . ') qqq  order by priority asc,voltage desc,res asc,nazv asc';
                }

if($old_d==1 && $new_d==0)  // Данные до 2022 года
    $sql=$sql1;
if($old_d==0 && $new_d==1 && $year<>0)  // Данные после 2021 года
            $sql=$sql2;
if(($old_d==1 && $new_d==1) || $year==0)  // Данные до 2022 года и после 2021 года
{  $sql1 = substr($sql1,0,-51);
    $sql = $sql1 . ' UNION ALL ' . $sql2;
    if(!empty($model->sum))
    $sql = 'select priority,nazv,res,voltage,SUM(all_month) AS all_month,SUM(all_delta) as all_delta,SUM(month_1) as month_1,
                    SUM(delta_1)  as delta_1,SUM(month_2) as month_2,SUM(delta_2)  as delta_2,SUM(month_3) as month_3,
                    SUM(delta_3) as delta_3,SUM(month_4) as month_4,SUM(delta_4)  as delta_4,SUM(month_5) as month_5,
                    SUM(delta_5)  as delta_5,SUM(month_6) as month_6,SUM(delta_6)  as delta_6,
                    SUM(month_7) as month_7,SUM(delta_7)  as delta_7,SUM(month_8) as month_8,SUM(delta_8)  as delta_8,
                    SUM(month_9) as month_9,SUM(delta_9)  as delta_9,SUM(month_10) as month_10,SUM(delta_10)  as delta_10,
                    SUM(month_11) as month_11,SUM(delta_11)  as delta_11,SUM(month_12) as month_12,SUM(delta_12)  as delta_12
                    from (' . $sql . ') qqq GROUP BY nazv,res,voltage,priority order by priority asc,voltage desc,res asc,nazv asc';
    else
        $sql = ' select priority,year,nazv,res,voltage,SUM(all_month) AS all_month,SUM(all_delta) as all_delta,SUM(month_1) as month_1,
                    SUM(delta_1)  as delta_1,SUM(month_2) as month_2,SUM(delta_2)  as delta_2,SUM(month_3) as month_3,
                    SUM(delta_3) as delta_3,SUM(month_4) as month_4,SUM(delta_4)  as delta_4,SUM(month_5) as month_5,
                    SUM(delta_5)  as delta_5,SUM(month_6) as month_6,SUM(delta_6)  as delta_6,
                    SUM(month_7) as month_7,SUM(delta_7)  as delta_7,SUM(month_8) as month_8,SUM(delta_8)  as delta_8,
                    SUM(month_9) as month_9,SUM(delta_9)  as delta_9,SUM(month_10) as month_10,SUM(delta_10)  as delta_10,
                    SUM(month_11) as month_11,SUM(delta_11)  as delta_11,SUM(month_12) as month_12,SUM(delta_12)  as delta_12
                    from (' . $sql . ') qqq GROUP BY nazv,year,res,voltage,priority order by priority asc,voltage desc,res asc,nazv asc,year asc';
//        $sql = 'select priority,year,nazv,res,voltage,
//                    case when year<>0 then all_month else sum(all_month) end AS all_month,
//                    case when year<>0 then all_delta else sum(all_delta) end as all_delta,
//                    case when year<>0 then month_1 else sum(month_1) end  as month_1,
//                    case when year<>0 then month_2 else sum(month_2) end  as month_2,
//                    case when year<>0 then month_3 else sum(month_3) end  as month_3,
//                    case when year<>0 then month_4 else sum(month_4) end  as month_4,
//                    case when year<>0 then month_5 else sum(month_5) end  as month_5,
//                    case when year<>0 then month_6 else sum(month_6) end  as month_6,
//                    case when year<>0 then month_7 else sum(month_7) end  as month_7,
//                    case when year<>0 then month_8 else sum(month_8) end  as month_8,
//                    case when year<>0 then month_9 else sum(month_9) end  as month_9,
//                    case when year<>0 then month_10 else sum(month_10) end  as month_10,
//                    case when year<>0 then month_11 else sum(month_11) end  as month_11,
//                    case when year<>0 then month_12 else sum(month_12) end  as month_12,
//                    case when year<>0 then delta_1 else sum(delta_1) end  as delta_1,
//                    case when year<>0 then delta_2 else sum(delta_2) end  as delta_2,
//                    case when year<>0 then delta_3 else sum(delta_3) end  as delta_3,
//                    case when year<>0 then delta_4 else sum(delta_4) end  as delta_4,
//                    case when year<>0 then delta_5 else sum(delta_5) end  as delta_5,
//                    case when year<>0 then delta_6 else sum(delta_6) end  as delta_6,
//                    case when year<>0 then delta_7 else sum(delta_7) end  as delta_7,
//                    case when year<>0 then delta_8 else sum(delta_8) end  as delta_8,
//                    case when year<>0 then delta_9 else sum(delta_9) end  as delta_9,
//                    case when year<>0 then delta_10 else sum(delta_10) end  as delta_10,
//                    case when year<>0 then delta_11 else sum(delta_11) end  as delta_11,
//                    case when year<>0 then delta_12 else sum(delta_12) end  as delta_12
//                    from (' . $sql . ') qqq GROUP BY all_month,all_delta,
//                    month_1,month_2,month_3,month_4,month_5,month_6,month_7,month_8,
//                    month_9,month_10,month_11,month_12,
//                    delta_1,delta_2,delta_3,delta_4,delta_5,delta_6,delta_7,delta_8,delta_9,
//                    delta_10,delta_11,delta_12,
//                    year,nazv,res,voltage,priority order by priority asc,voltage desc,res asc,nazv asc';

}

//          debug($sql);
//          return;

               // Сброс sql запроса в файл
                $f=fopen('aaa','w+');
                fputs($f,$sql);
                $sql1=$sql;
                if($year>2027) {
                    // Архивация sql запроса:
                    // применяется в случае если web - сервер не пропускает большой get запрос
                    // идея в замене повторяющихся частей текста, повторяющиеся блоки текста
                    //  хранятся в таблице archsql. После того как в sql - запросе (переменная $sql)
                    //  найдется повторяющаяся часть, она заменяется на символ из поля form, но перед этим ставится символ ~ (признак упаковки)
                    $z = 'select * from archsql';
                    $src = needs_fact::findBySql($z)->asArray()->all();
//                debug($sql);
//                return;
                    foreach ($src as $v) {
                        $find = trim($v['block']);
                        $find = str_replace(chr(13), '', $find);
                        $sql = str_replace(chr(13), '', $sql);
                        $pos = mb_strpos($sql, $find, 0, 'UTF-8');
//                        $pos = find_str($sql, $find);
//                        if ($pos == -1) {
                        if ($pos === false) {
                        } else {
                            $sql = str_replace($find, '~' . $v['form'], $sql);
//                        $y=mb_strlen($find,'UTF-8');
//                        $s = mb_substr($sql,$y)
                        }
                    }
                }

                $data = needs_fact::findBySql($sql1)->all();
                $year_find = $year;
                $year=$data[0]['year'];
                $kol = count($data);

                $searchModel = new needs_fact();
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $sql1);

                $dataProvider->pagination = false;

                if(!empty($model->sum))
                    return $this->render('needs_fact_report', [
                        'dataProvider' =>$dataProvider,
                        'searchModel' => $searchModel,
                        'kol' => $kol,'sql' => $sql,'year'=> $year,'id' => 1,'list'=> $list]);
                else
                    return $this->render('needs_fact_report_years', [
                        'dataProvider' =>$dataProvider,
                        'searchModel' => $searchModel,
                        'kol' => $kol,'sql' => $sql,'year'=> $year,'id' => 1]);


            } else {

                return $this->render('inputreport_years', [
                    'model' => $model
                ]);
            }
        }

        else{
            // Если передается параметр $sql
            // Распаковка sql - запроса
            $sql_src = $sql;
            $result_sql = '';
            $flag=0;
            $y = mb_strlen($sql, 'UTF-8');
            for($i=0;$i<$y;$i++){
                $c=mb_substr($sql,$i,1,'UTF-8');
                if($c=='~' && $flag==0){
                    $flag=1;
                }
                if($c<>'~' && $flag==1){
                    $z="select block from archsql where form='$c'";
                    $src = needs_fact::findBySql($z)->asArray()->all();
                    $s = $src[0]['block'];
                    $result_sql = $result_sql . $s;
                    $flag=0;
                    continue;
                }
                if($c<>'~' && $flag==0){
                    $result_sql = $result_sql . $c;
                }
            }
//        debug($result_sql);
//        return;
            $sql = $result_sql;
            $data = needs_fact::findBySql($sql)->all();

            $year=$data[0]['year'];

            $searchModel = new needs_fact();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $sql);
            $dataProvider->pagination = false;
            $kol = count($data);
            $n_key=0;
            for($i=0;$i<$kol;$i++){
                if($data[$i]['id']==$id_p) {
                    $n_key=$i;
                    break;
                }
            }
            if($id_p<>0)
                $dataProvider->id=$id_p;

//            debug($sql);
//            return;

            $session = Yii::$app->session;
            $session->open();
            $session->set('view', 1);

            if($year<2022)
                return $this->render('needs_fact', ['data' => $data,
                    'dataProvider' => $dataProvider, 'searchModel' => $searchModel,
                    'kol' => $kol, 'sql' => $sql,'year'=> $year,'id' => $n_key+1]);
            else {

                return $this->render('needs_fact_cnt', ['data' => $data,
                    'dataProvider' => $dataProvider, 'searchModel' => $searchModel,
                    'kol' => $kol, 'sql' => $sql_src, 'year' => $year, 'id' => $n_key + 1]);
            }
        }
    }

    //    ~ Обновление записи
    public function actionUpdate_fact($id,$mod,$sql,$res='')
    {
        // $id  id записи
        // $mod - название модели
        $sql_src = $sql;
        // Распаковка sql - запроса
        // Принцип:
        //  Если встретился символ ~ тогда первый за ним символ является ссылкой на поле form таблицы archsql и
        // вместо этого символа подставляется блок текста из поля block
        $result_sql = '';   // Сдесь будет собираться распакованный запрос
        $flag=0;
        $y = mb_strlen($sql, 'UTF-8');
        for($i=0;$i<$y;$i++){
            $c=mb_substr($sql,$i,1,'UTF-8');
            if($c=='~' && $flag==0){
                $flag=1;
            }
            if($c<>'~' && $flag==1){
                $z="select block from archsql where form='$c'";
                $src = needs_fact::findBySql($z)->asArray()->all();
                $s = $src[0]['block'];
                $result_sql = $result_sql . $s;
                $flag=0;
                continue;
            }
            if($c<>'~' && $flag==0){
                $result_sql = $result_sql . $c;
            }
        }
//        debug($result_sql);
//        return;
        $sql = $result_sql;

            $sql1='select * from ('.$sql.') src '. ' where id='.$id;
            $model = needs_fact::findBySql($sql1)->one();

//            debug($id);
//            return;

        $session = Yii::$app->session;
        $session->open();
        if($session->has('user'))
            $user = $session->get('user');
        else
            $user = '';

        if ($model->load(Yii::$app->request->post()))
        {
//            debug($model);
//            return;

            if(!isset($model->month_b_1)) $model->month_b_1 = 0;
            if(!isset($model->month_b_2)) $model->month_b_2 = 0;
            if(!isset($model->month_b_3)) $model->month_b_3 = 0;
            if(!isset($model->month_b_4)) $model->month_b_4 = 0;
            if(!isset($model->month_b_5)) $model->month_b_5 = 0;
            if(!isset($model->month_b_6)) $model->month_b_6 = 0;
            if(!isset($model->month_b_7)) $model->month_b_7 = 0;
            if(!isset($model->month_b_8)) $model->month_b_8 = 0;
            if(!isset($model->month_b_9)) $model->month_b_9 = 0;
            if(!isset($model->month_b_10)) $model->month_b_10 = 0;
            if(!isset($model->month_b_11)) $model->month_b_11 = 0;
            if(!isset($model->month_b_12)) $model->month_b_12 = 0;
            // Обновление фактических показателей
            // Запись конечных и начальных показаний и начальных показаний следующего месяца
            $z = "UPDATE needs_fact 
                  SET "."month_1"."=".$model->month_1.
                ',month_2='.$model->month_2.
                ',month_3='.$model->month_3.
                ',month_4='.$model->month_4.
                ',month_5='.$model->month_5.
                ',month_6='.$model->month_6.
                ',month_7='.$model->month_7.
                ',month_8='.$model->month_8.
                ',month_9='.$model->month_9.
                ',month_10='.$model->month_10.
                ',month_11='.$model->month_11.
                ',month_12='.$model->month_12.
                ',month_b_1='.$model->month_b_1.
                ',month_b_2='.$model->month_b_2.
                ',month_b_3='.$model->month_b_3.
                ',month_b_4='.$model->month_b_4.
                ',month_b_5='.$model->month_b_5.
                ',month_b_6='.$model->month_b_6.
                ',month_b_7='.$model->month_b_7.
                ',month_b_8='.$model->month_b_8.
                ',month_b_9='.$model->month_b_9.
                ',month_b_10='.$model->month_b_10.
                ',month_b_11='.$model->month_b_11.
                ',month_b_12='.$model->month_b_12.
                ',koef='.$model->koef.
                ',year='.$model->year.
                " WHERE id = ".$model->id;

            $model->pointer='*';

//            debug($z);
//            return;

            Yii::$app->db->createCommand($z)->execute();

            // Запись начальных показаний следующего месяца
            $z = "UPDATE needs_fact 
                  SET ".
                'month_b_2='.$model->month_1.
                ',month_b_3='.$model->month_2.
                ',month_b_4='.$model->month_3.
                ',month_b_5='.$model->month_4.
                ',month_b_6='.$model->month_5.
                ',month_b_7='.$model->month_6.
                ',month_b_8='.$model->month_7.
                ',month_b_9='.$model->month_8.
                ',month_b_10='.$model->month_9.
                ',month_b_11='.$model->month_10.
                ',month_b_12='.$model->month_11.
                ',year='.$model->year.
                " WHERE id = ".$model->id .
//                " and rem<>'05'" .
//                " and rem<>'01'" . " and trim(nazv)<>'ЦРП-5'" .
//                " and rem<>'02'" . " and trim(nazv)<>'РП-1'" .
//                " and rem<>'02'" . " and trim(nazv)<>'РП-2'" .
//                " and rem<>'-'" . " and nazv like '%САЗ%'" .
//                " and rem<>'-'" . " and nazv like '%ДШЗ-1%'" ;

            " and not(rem='05'" .
            " or (rem='01'" . " and trim(nazv)='ЦРП-5')" .
            " or (rem='02'" . " and trim(nazv)='РП-1')" .
            " or (rem='02'" . " and trim(nazv)='РП-2')" .
            " or (rem='-'" . " and nazv like '%САЗ%')" .
            " or (rem<>'-'" . " and nazv like '%ДШЗ-1%'))" ;
//            debug($z);
//            return;

            Yii::$app->db->createCommand($z)->execute();

            if($mod=='norm_facts')
                $this->redirect(['site/more','sql' => $sql_src,'id_p' =>$model->id]);

        } else {
            if($mod=='norm_facts')
                // Форма редактирования показаний
                return $this->render('update_fact', [
                    'model' => $model
                ]);
        }
    }

    public function actionNorms_forms()
    {
        $model = new Norms();
        $searchModel = new Norms_search();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $sql = "SELECT  nazv,c.rem,voltage,
mon_1,mon_2,mon_3,mon_4,mon_5,mon_6,mon_7,mon_8,mon_9,mon_10,mon_11,mon_12,year,
(mon_1+mon_2+mon_3+mon_4+mon_5+mon_6+mon_7+mon_8+mon_9+mon_10+mon_11+mon_12) as sum_potr 
FROM needs_norm 
left join kod_rem c on needs_norm.rem=c.kod_rem
 where 1=1 ";

                if (!empty($model->year)) {
                    if ($model->year == '1')
                        $model->year = '2022';
                    if ($model->year == '2')
                        $model->year = '2021';
                    if ($model->year == '3')
                        $model->year = '2020';
                    if ($model->year == '4')
                        $model->year = '2019';

                         $sql = $sql . ' and year = ' . $model->year ;
            }
                $sql=$sql. ' ORDER BY needs_norm.voltage desc,needs_norm.rem asc,needs_norm.nazv,needs_norm.year desc';

                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $sql);
                    $dataProvider->pagination = false;
                    return $this->render('norms', [
                        'model' => $searchModel, 'dataProvider' => $dataProvider, 'searchModel' => $searchModel, 'sql' => $sql
                    ]);
        } else {
            return $this->render('norms_forms', compact('model'));
        }
    }

    // Сброс в Excel
    public function actionNorms2excel()
    {
        $sql=Yii::$app->request->post('data');
        $model = Norms_search::findBySql($sql)->asarray()->all();
        $dataProvider = new ActiveDataProvider([
            'query' => Norms_search::findBySql($sql),
            'pagination' => [
                'pageSize' => 500,
            ],
        ]);
        $session = Yii::$app->session;
        if($session->has('sql_analytics'))
            $sql = $session->get('sql_analytics');
        else
            $sql='';


        $cols = [
            'nazv'=> 'Назва',
            'rem'=> 'РЕМ',
            'voltage'=> 'Рівень напруги',
            'mon_1'=> 'Січень',
            'mon_2'=> 'Лютий',
            'mon_3'=> 'Березень',
            'mon_4'=> 'Квітень',
            'mon_5'=> 'Травень',
            'mon_6'=> 'Червень',
            'mon_7'=> 'Липень',
            'mon_8'=> 'Серпень',
            'mon_9'=> 'Вересень',
            'mon_10'=> 'Жовтень',
            'mon_11'=> 'Листопад',
            'mon_12'=> 'Грудень',
            'year' => 'Рік'
        ];

        // Формирование массива названий колонок
        $list='';  // Список полей для сброса в Excel
        $h=[];
        $i=0;
//        debug($model);
//        return;
        $j=0;
        $col_e=[];
        foreach($model[0] as $k=>$v){
            $col="'".$k."'";
            $col_e[$j]=$k;
            $j++;
            if(in_array(trim($k), array_keys($cols), true)){
                $h[$i]['col']=$col;
                $i++;
            }
        }

        $k1='Довідник норм';

        $newQuery = clone $dataProvider->query;
        $models = $newQuery->all();

        \moonland\phpexcel\Excel::widget([
            'models' => $models,

            'mode' => 'export', //default value as 'export'
            'format' => 'Excel2007',
            'hap' => $k1,    //cтрока шапки таблицы
            'data_model' => 1,
            //'columns' => $h,
            'columns' => $col_e,
            'headers' => $cols
        ]);
        return;
    }

    // Сброс в Excel
    public function actionFacts2excel()
    {
        $sql=Yii::$app->request->post('data');
        $version=Yii::$app->request->post('version');
        $model = needs_fact::findBySql($sql)->asarray()->all();
        $dataProvider = new ActiveDataProvider([
            'query' => needs_fact::findBySql($sql),
            'pagination' => [
                'pageSize' => 500,
            ],
        ]);
        $session = Yii::$app->session;
        if($session->has('sql_analytics'))
            $sql = $session->get('sql_analytics');
        else
            $sql='';

        $cols = [
            'id' => 'ID',
            'nazv' => 'Назва',
            'voltage' => 'Рівень напруги',
            'res' => 'РЕС',
            'year' => 'Рік',
//            'rem' => '',
            'all_month' => 'Усього',
            'all_delta' => '^',
            'month_1' => 'січень',
            'delta_1' => '^1',
            'month_2' => 'лютий',
            'delta_2' => '^2',
            'month_3' => 'березень',
            'delta_3' => '^3',
            'month_4' => 'квітень',
            'delta_4' => '^4',
            'month_5' => 'травень',
            'delta_5' => '^5',
            'month_6' => 'червень',
            'delta_6' => '^6',
            'month_7' => 'липень',
            'delta_7' => '^7',
            'month_8' => 'серпень',
            'delta_8' => '^8',
            'month_9' => 'вересень',
            'delta_9' => '^9',
            'month_10' => 'жовтень',
            'delta_10' => '^10',
            'month_11' => 'листопад',
            'delta_11' => '^11',
            'month_12' => 'грудень',
            'delta_12' => '^12',
        ];
        // Формирование массива названий колонок
        $list='';  // Список полей для сброса в Excel
        $h=[];
        $i=0;
        $j=0;
        $col_e=[];
        foreach($model[0] as $k=>$v){
            $col="'".$k."'";
            $col_e[$j]=$k;
            $j++;
            if(in_array(trim($k), array_keys($cols), true)){
                $h[$i]['col']=$col;
                $i++;
            }
        }

        $k1='Фактичні показання';

        $newQuery = clone $dataProvider->query;
        $models = $newQuery->all();
        // Версия до 2022 года
        if($version==1)
        \moonland\phpexcel\Excel::widget([
            'models' => $models,

            'mode' => 'export', //default value as 'export'
            'format' => 'Excel2007',
            'hap' => $k1,    //cтрока шапки таблицы
            'data_model' => 1,
            //'columns' => $h,
//            'columns' => $col_e,
            'columns' => [  'nazv',
                'voltage',
                'res',
                'year',
                'all_month',
                'all_delta',
                'month_1',
                'delta_1',
                'month_2',
                'delta_2',
                'month_3',
                'delta_3',
                'month_4',
                'delta_4',
                'month_5',
                'delta_5',
                'month_6',
                'delta_6',
                'month_7',
                'delta_7',
                'month_8',
                'delta_8',
                'month_9',
                'delta_9',
                'month_10',
                'delta_10',
                'month_11',
                'delta_11',
                'month_12',
                'delta_12',

            ],
//            'headers' => $cols
            'headers' => [  'nazv' => 'Назва',
                'voltage' => 'Рівень напруги',
                'res' => 'РЕМ','year' => 'Рік','all_month' => 'Усього',
                'all_delta' => '^',
                'month_1' => 'січень',
                'delta_1' => '^1',
                'month_2' => 'лютий',
                'delta_2' => '^2',
                'month_3' => 'березень',
                'delta_3' => '^3',
                'month_4' => 'квітень',
                'delta_4' => '^4',
                'month_5' => 'травень',
                'delta_5' => '^5',
                'month_6' => 'червень',
                'delta_6' => '^6',
                'month_7' => 'липень',
                'delta_7' => '^7',
                'month_8' => 'серпень',
                'delta_8' => '^8',
                'month_9' => 'вересень',
                'delta_9' => '^9',
                'month_10' => 'жовтень',
                'delta_10' => '^10',
                'month_11' => 'листопад',
                'delta_11' => '^11',
                'month_12' => 'грудень',
                'delta_12' => '^12',
                ],
        ]);
        // Версия после 2021 года
        if($version==2)
            \moonland\phpexcel\Excel::widget([
                'models' => $models,

                'mode' => 'export', //default value as 'export'
                'format' => 'Excel2007',
                'hap' => $k1,    //cтрока шапки таблицы
                'data_model' => 1,
                //'columns' => $h,
//            'columns' => $col_e,
                'columns' => [  'nazv',
                    'voltage',
                    'res',
                    'year',
                    'all_month',
                    'counter',
                    's_nom',
                    'all_delta',
                    'month_b_1',
                    'month_1',
                    'potr_1',
                    'delta_1',
                    'month_b_2',
                    'month_2',
                    'potr_2',
                    'delta_2',
                    'month_b_3',
                    'month_3',
                    'potr_3',
                    'delta_3',
                    'month_b_4',
                    'month_4',
                    'potr_4',
                    'delta_4',
                    'month_b_5',
                    'month_5',
                    'potr_5',
                    'delta_5',
                    'month_b_6',
                    'month_6',
                    'potr_6',
                    'delta_6',
                    'month_b_7',
                    'month_7',
                    'potr_7',
                    'delta_7',
                    'month_b_8',
                    'month_8',
                    'potr_8',
                    'delta_8',
                    'month_b_9',
                    'month_9',
                    'potr_9',
                    'delta_9',
                    'month_b_10',
                    'month_10',
                    'potr_10',
                    'delta_10',
                    'month_b_11',
                    'month_11',
                    'potr_11',
                    'delta_11',
                    'month_b_12',
                    'month_12',
                    'potr_12',
                    'delta_12',

                ],
//            'headers' => $cols
                'headers' => [  'nazv' => 'Назва',
                    'voltage' => 'Рівень напруги',
                    'res' => 'РЕМ','year' => 'Рік','all_month' => 'Усього',
                    'all_delta' => '^',
                    'counter' => 'Тип ліч.',
                    's_nom' => '№ ліч.',
                    'month_b_1' => 'січень поч.',
                    'month_1' => 'січень',
                    'potr_1'=> 'січень спож.',
                    'delta_1' => '^1',
                    'month_b_2' => 'лютий поч.',
                    'month_2' => 'лютий',
                    'potr_2'=> 'лютий спож.',
                    'delta_2' => '^2',
                    'month_b_3' => 'березень поч.',
                    'month_3' => 'березень',
                    'potr_3'=> 'березень спож.',
                    'delta_3' => '^3',
                    'month_b_4' => 'квітень поч.',
                    'month_4' => 'квітень',
                    'potr_4'=> 'квітень спож.',
                    'delta_4' => '^4',
                    'month_b_5' => 'травень поч.',
                    'month_5' => 'травень',
                    'potr_5'=> 'травень спож.',
                    'delta_5' => '^5',
                    'month_b_6' => 'червень поч.',
                    'month_6' => 'червень',
                    'potr_6'=> 'червень спож.',
                    'delta_6' => '^6',
                    'month_b_7' => 'липень поч.',
                    'month_7' => 'липень',
                    'potr_7'=> 'липень спож.',
                    'delta_7' => '^7',
                    'month_b_8' => 'серпень поч.',
                    'month_8' => 'серпень',
                    'potr_8'=> 'серпень спож.',
                    'delta_8' => '^8',
                    'month_b_9' => 'вересень поч.',
                    'month_9' => 'вересень',
                    'potr_9'=> 'вересень спож.',
                    'delta_9' => '^9',
                    'month_b_10' => 'жовтень поч.',
                    'month_10' => 'жовтень',
                    'potr_10'=> 'жовтень спож.',
                    'delta_10' => '^10',
                    'month_b_11' => 'листопад поч.',
                    'month_11' => 'листопад',
                    'potr_11'=> 'листопад спож.',
                    'delta_11' => '^11',
                    'month_b_12' => 'грудень поч.',
                    'month_12' => 'грудень',
                    'potr_12'=> 'грудень спож.',
                    'delta_12' => '^12',
                ],

            ]);
        return;
    }

    // Сброс в Excel
    public function actionFacts2excel_report()
    {
        $sql=Yii::$app->request->post('data');
        $version=Yii::$app->request->post('version');
        $years=Yii::$app->request->post('years');
        $model = needs_fact::findBySql($sql)->asarray()->all();
        $dataProvider = new ActiveDataProvider([
            'query' => needs_fact::findBySql($sql),
            'pagination' => [
                'pageSize' => 500,
            ],
        ]);
        $session = Yii::$app->session;
        if($session->has('sql_analytics'))
            $sql = $session->get('sql_analytics');
        else
            $sql='';

        $cols = [
            'id' => 'ID',
            'nazv' => 'Назва',
            'voltage' => 'Рівень напруги',
            'res' => 'РЕС',
            'year' => 'Рік',
//            'rem' => '',
            'all_month' => 'Усього',
            'all_delta' => '^',
            'month_1' => 'січень',
            'delta_1' => '^1',
            'month_2' => 'лютий',
            'delta_2' => '^2',
            'month_3' => 'березень',
            'delta_3' => '^3',
            'month_4' => 'квітень',
            'delta_4' => '^4',
            'month_5' => 'травень',
            'delta_5' => '^5',
            'month_6' => 'червень',
            'delta_6' => '^6',
            'month_7' => 'липень',
            'delta_7' => '^7',
            'month_8' => 'серпень',
            'delta_8' => '^8',
            'month_9' => 'вересень',
            'delta_9' => '^9',
            'month_10' => 'жовтень',
            'delta_10' => '^10',
            'month_11' => 'листопад',
            'delta_11' => '^11',
            'month_12' => 'грудень',
            'delta_12' => '^12',
        ];
        // Формирование массива названий колонок
        $list='';  // Список полей для сброса в Excel
        $h=[];
        $i=0;
        $j=0;
        $col_e=[];
        foreach($model[0] as $k=>$v){
            $col="'".$k."'";
            $col_e[$j]=$k;
            $j++;
            if(in_array(trim($k), array_keys($cols), true)){
                $h[$i]['col']=$col;
                $i++;
            }
        }

        $k1='Фактичні показання. Роки: '.$years;

        $newQuery = clone $dataProvider->query;
        $models = $newQuery->all();
        // Версия до 2022 года
        if($version==1)
            \moonland\phpexcel\Excel::widget([
                'models' => $models,

                'mode' => 'export', //default value as 'export'
                'format' => 'Excel2007',
                'hap' => $k1,    //cтрока шапки таблицы
                'data_model' => 1,
                //'columns' => $h,
//            'columns' => $col_e,
                'columns' => [  'nazv',
                    'voltage',
                    'res',
                    'all_month',
                    'all_delta',
                    'month_1',
                    'delta_1',
                    'month_2',
                    'delta_2',
                    'month_3',
                    'delta_3',
                    'month_4',
                    'delta_4',
                    'month_5',
                    'delta_5',
                    'month_6',
                    'delta_6',
                    'month_7',
                    'delta_7',
                    'month_8',
                    'delta_8',
                    'month_9',
                    'delta_9',
                    'month_10',
                    'delta_10',
                    'month_11',
                    'delta_11',
                    'month_12',
                    'delta_12',

                ],
//            'headers' => $cols
                'headers' => [  'nazv' => 'Назва',
                    'voltage' => 'Рівень напруги',
                    'res' => 'РЕМ','all_month' => 'Усього',
                    'all_delta' => '^',
                    'month_1' => 'січень',
                    'delta_1' => '^1',
                    'month_2' => 'лютий',
                    'delta_2' => '^2',
                    'month_3' => 'березень',
                    'delta_3' => '^3',
                    'month_4' => 'квітень',
                    'delta_4' => '^4',
                    'month_5' => 'травень',
                    'delta_5' => '^5',
                    'month_6' => 'червень',
                    'delta_6' => '^6',
                    'month_7' => 'липень',
                    'delta_7' => '^7',
                    'month_8' => 'серпень',
                    'delta_8' => '^8',
                    'month_9' => 'вересень',
                    'delta_9' => '^9',
                    'month_10' => 'жовтень',
                    'delta_10' => '^10',
                    'month_11' => 'листопад',
                    'delta_11' => '^11',
                    'month_12' => 'грудень',
                    'delta_12' => '^12',
                ],
            ]);
        // Версия после 2021 года
        if($version==2)
            \moonland\phpexcel\Excel::widget([
                'models' => $models,

                'mode' => 'export', //default value as 'export'
                'format' => 'Excel2007',
                'hap' => $k1,    //cтрока шапки таблицы
                'data_model' => 1,
                //'columns' => $h,
//            'columns' => $col_e,
                'columns' => [  'nazv',
                    'voltage',
                    'res',
                    'year',
                    'all_month',
                    'counter',
                    's_nom',
                    'all_delta',
                    'month_b_1',
                    'month_1',
                    'potr_1',
                    'delta_1',
                    'month_b_2',
                    'month_2',
                    'potr_2',
                    'delta_2',
                    'month_b_3',
                    'month_3',
                    'potr_3',
                    'delta_3',
                    'month_b_4',
                    'month_4',
                    'potr_4',
                    'delta_4',
                    'month_b_5',
                    'month_5',
                    'potr_5',
                    'delta_5',
                    'month_b_6',
                    'month_6',
                    'potr_6',
                    'delta_6',
                    'month_b_7',
                    'month_7',
                    'potr_7',
                    'delta_7',
                    'month_b_8',
                    'month_8',
                    'potr_8',
                    'delta_8',
                    'month_b_9',
                    'month_9',
                    'potr_9',
                    'delta_9',
                    'month_b_10',
                    'month_10',
                    'potr_10',
                    'delta_10',
                    'month_b_11',
                    'month_11',
                    'potr_11',
                    'delta_11',
                    'month_b_12',
                    'month_12',
                    'potr_12',
                    'delta_12',

                ],
//            'headers' => $cols
                'headers' => [  'nazv' => 'Назва',
                    'voltage' => 'Рівень напруги',
                    'res' => 'РЕМ','year' => 'Рік','all_month' => 'Усього',
                    'all_delta' => '^',
                    'counter' => 'Тип ліч.',
                    's_nom' => '№ ліч.',
                    'month_b_1' => 'січень поч.',
                    'month_1' => 'січень',
                    'potr_1'=> 'січень спож.',
                    'delta_1' => '^1',
                    'month_b_2' => 'лютий поч.',
                    'month_2' => 'лютий',
                    'potr_2'=> 'лютий спож.',
                    'delta_2' => '^2',
                    'month_b_3' => 'березень поч.',
                    'month_3' => 'березень',
                    'potr_3'=> 'березень спож.',
                    'delta_3' => '^3',
                    'month_b_4' => 'квітень поч.',
                    'month_4' => 'квітень',
                    'potr_4'=> 'квітень спож.',
                    'delta_4' => '^4',
                    'month_b_5' => 'травень поч.',
                    'month_5' => 'травень',
                    'potr_5'=> 'травень спож.',
                    'delta_5' => '^5',
                    'month_b_6' => 'червень поч.',
                    'month_6' => 'червень',
                    'potr_6'=> 'червень спож.',
                    'delta_6' => '^6',
                    'month_b_7' => 'липень поч.',
                    'month_7' => 'липень',
                    'potr_7'=> 'липень спож.',
                    'delta_7' => '^7',
                    'month_b_8' => 'серпень поч.',
                    'month_8' => 'серпень',
                    'potr_8'=> 'серпень спож.',
                    'delta_8' => '^8',
                    'month_b_9' => 'вересень поч.',
                    'month_9' => 'вересень',
                    'potr_9'=> 'вересень спож.',
                    'delta_9' => '^9',
                    'month_b_10' => 'жовтень поч.',
                    'month_10' => 'жовтень',
                    'potr_10'=> 'жовтень спож.',
                    'delta_10' => '^10',
                    'month_b_11' => 'листопад поч.',
                    'month_11' => 'листопад',
                    'potr_11'=> 'листопад спож.',
                    'delta_11' => '^11',
                    'month_b_12' => 'грудень поч.',
                    'month_12' => 'грудень',
                    'potr_12'=> 'грудень спож.',
                    'delta_12' => '^12',
                ],

            ]);
        return;
    }


// Добавление новых пользователей
    public function actionAddAdmin() {
        $model = User::find()->where(['username' => 'sbit'])->one();

        if (empty($model) || is_null($model)) {
            $user = new User();
            $user->username = 'sbit';
            $user->email = 'sbit@ukr.net';
            $user->id = 8;
            $user->role = 3;
            $user->id_res = 5000;
            $user->setPassword('sbit_cek');
            $user->generateAuthKey();
            if ($user->save()) {
                echo 'good';
            }
            else{
                $user->validate();
                debug($user->getErrors());
            }
        }
    }

// Выход пользователя
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(str_replace('/web','',Url::toRoute('site/cek')));
    }

    //    Страница о программе
    public function actionAbout()
    {
        $model = new info();
        $model->title = 'Про програму';
        $model->info1 = "Ця програма здійснює введення данних по фактичному споживанню электоенергії
         на підстанціях для власних потреб, а також формування звітів для порівняння споживання з нормативним споживанням.";
        $model->style1 = "d15";
        $model->style2 = "info-text";
        $model->style_title = "d9";
        return $this->render('about', [
            'model' => $model]);
    }
}

