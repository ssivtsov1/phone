<?php
/**
 * Используется для просмотра сотрудников
 */
namespace app\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

class Employees extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'vw_phone'; //Это вид
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tab_nom' => 'Таб. №',
            'fio' => 'П.І.Б.',
            'post' => 'Посада',
            'main_unit' => 'Гол. підрозділ',
            'unit_1' => 'Підпор. підрозділ',
            'unit_2' => 'Група',
            'tel_mob' => 'Моб. тел.',
            'email' => 'Особова пошта',
            'email_group' => 'Пошта відділу',
            'rate' => 'Тарифний план',
            'tel' => 'Внутр. тел.',
            'tel_town' => 'Міський тел.',
            'line' => 'Лінія',
            'nazv' => 'Назва',
            'type_tel' => 'Тип моб. телефону',
            'phone_type' => 'Тип телефону',
            
        ];
    }

    public function rules()
    {
        return [

            [['id','tab_nom','fio','post','main_unit','unit_1','unit_2','tel_mob','remark',
                'email','rate','tel_town','tel','line','nazv','type_tel',
                'phone_type','email_group'],'safe']
            ];
    }

    public function search($params)
    {
        
        $query = employees::find();
//        $tel_mob = trim($this->tel_mob); 
//        if(substr($tel_mob,0,1)=='0') $tel_mob = substr($tel_mob,1);
//        $tel_mob = only_digit($tel_mob);
       
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder'=> ['fio'=>SORT_ASC]]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $tel = trim($this->tel_mob);
        $query->andFilterWhere(['=', 'tab_nom', $this->tab_nom]);
        $query->andFilterWhere(['like', 'fio', $this->fio]);
        $query->andFilterWhere(['like', 'post', $this->post]);
        if(substr($tel,0,1)=='0' &&  strlen($tel)>1){
            $fnd = '%'.substr($tel,1).'%';
            $query->andFilterWhere(['like', 'tel_mob', $fnd, false]);}
        else
            $query->andFilterWhere(['like', 'tel_mob', only_digit($this->tel_mob)]);

        $query->andFilterWhere(['like', 'tel', $this->tel]);
        $query->andFilterWhere(['like', 'tel_town', only_digit($this->tel_town)]);
        $query->andFilterWhere(['like', 'main_unit', $this->main_unit]);
        $query->andFilterWhere(['like', 'unit_1', $this->unit_1]);
        $query->andFilterWhere(['like', 'unit_2', $this->unit_2]);
        $query->andFilterWhere(['like', 'email', $this->email]);
        $query->andFilterWhere(['like', 'email_group', $this->email_group]);

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


