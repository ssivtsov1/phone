<?php
/**
 * Используется для просмотра сотрудников
 */
namespace app\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

class Shtrafbat extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'shtrafbat'; //Это вид
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fio' => 'П.І.Б.',
            'main_unit' => 'Підрозділ',
            'tel' => 'Мобільний телефон',
            'shtraf' => 'Борг, грн.',
            'year' => 'Рік',
            'month' => 'Місяць',
        ];
    }

    public function rules()
    {
        return [

            [['id','fio','main_unit','tel','straf','year','month'],'safe']
            ];
    }

    public function search($params)
    {
        
        $query = shtrafbat::find(); //->where(['year'=>2018,'month'=>2]);
//        $tel_mob = trim($this->tel_mob); 
//        if(substr($tel_mob,0,1)=='0') $tel_mob = substr($tel_mob,1);
//        $tel_mob = only_digit($tel_mob);
       
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder'=> ['year'=>SORT_DESC,'month'=>SORT_DESC,'fio'=>SORT_ASC]]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $tel = trim($this->tel);
       
        $query->andFilterWhere(['like', 'fio', $this->fio]);
       
        if(substr($tel,0,1)=='0' &&  strlen($tel)>1){
            $fnd = '%'.substr(only_digit($tel),1).'%';
            $query->andFilterWhere(['like', 'tel', $fnd, false]);}
        else
            $query->andFilterWhere(['like', 'tel', only_digit($this->tel)]);

        //$query->andFilterWhere(['like', 'tel', $this->tel]);
        $query->andFilterWhere(['like', 'main_unit', $this->main_unit]);
        $query->andFilterWhere(['=', 'year', $this->year]);
        $query->andFilterWhere(['=', 'month', $this->month]);
        
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


