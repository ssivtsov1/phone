<?php
/**
 * Используется для просмотра сотрудников
 */
namespace app\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;


class Viewphone extends \yii\db\ActiveRecord
{
    public $sort1;
    public $sql;
    //public $photo;
    

    public static function tableName()
    {
        return 'vw_phone'; //Это вид
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            
            'tab_nom' => 'Таб. № ',
            'fio' => 'П.І.Б.',
            'fio_ru' => 'П.І.Б.[ru]',
            'post' => 'Посада',
            'main_unit' => 'Гол. підрозділ',
            'unit_1' => 'Підпор. підрозділ',
            'unit_2' => 'Група',
            'tel_mob' => 'Моб.тел.',
            'email' => 'Особова пошта',
            'email_group' => 'Пошта відділу',
            'rate' => 'Тарифний план',
            'tel' => 'Внутр. тел.',
            'tel_private' => 'Особовий тел.',
            'tel_town' => 'Міський тел.',
            'line' => 'Лінія',
            'nazv' => 'Назва',
            'type_tel' => 'Тип моб. телефону',
            'phone_type' => 'Тип телефону',
            'photo' => '   Фото',
            
        ];
    }

    public function rules()
    {
        return

            [
                [['id','tab_nom','fio','post','main_unit','unit_1','unit_2','tel_mob','remark',
                'email','rate','tel_town','tel','line','nazv','type_tel',
                'phone_type','sort1','email_group','sql','fio_ru'
                ],'safe'],
               // [['photo'],'file','extensions'=>'png,jpg,jpeg'],
                [['photo'], 'file'],
            ]
            ;
    }

    public function search($params,$sql)
    {
        
        $query = viewphone::findBySql($sql);
        $query->sql = $sql;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            //'sort' => ['defaultOrder'=> ['sort1'=>SORT_ASC,'unit_2'=>SORT_ASC]]
        ]);

//        $dataProvider->setSort([
//            'attributes' => [
//                'id',
//                'sort1' => [
//                    'asc' => ['sort1' => SORT_ASC, 'unit_2' => SORT_ASC],
//                    'desc' => ['sort1' => SORT_DESC, 'unit_2' => SORT_DESC],
//
//                    'default' => SORT_ASC
//                ],
//
//            ]
//        ]);


        
        
//        $dataProvider = new SqlDataProvider([
//    'sql' => $sql,
//    
//    'totalCount' => (int) $kol,
//    //'sort' =>false, to remove the table header sorting
//    'sort' => [
//        'attributes' => [
//            'fio' => [
//                'asc' => ['fio' => SORT_ASC],
//                'desc' => ['fio' => SORT_DESC],
//                'default' => SORT_ASC,
//               
//            ],
//            'post' => [
//                'asc' => ['post' => SORT_ASC],
//                'desc' => ['post' => SORT_DESC],
//                'default' => SORT_ASC,
//                
//            ],
//			
//        ],
//    ],
//    'pagination' => [
//        'pageSize' => 20,
//    ],
//]);
        
        
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

//        $query->andFilterWhere(['like', 'fio', $this->fio]);
//        $query->andFilterWhere(['like', 'post', $this->post]);
//        $query->andFilterWhere(['like', 'tel_mob', only_digit($this->tel_mob)]);
//       
//        $query->andFilterWhere(['like', 'tel', $this->tel]);
//        $query->andFilterWhere(['like', 'tel_town', only_digit($this->tel_town)]);
        return $dataProvider;
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public static function getDb()
    {
            return Yii::$app->get('db');
    }

}


