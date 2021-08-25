<?php
/**
 * Используется для редактирования мобильных телефонов
 */
namespace app\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

class Tel_vi extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'tel_vi';
    }
   
    public function rules()
    {
        return [

            [['id','post','fio','tel','tel_town'],'safe']
            ];
    }

    public function search($params)
    {

        $query = tel_vi::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
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


