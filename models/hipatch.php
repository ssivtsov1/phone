<?php
/**
 * Используется для редактирования мобильных телефонов
 */
namespace app\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

class Hipatch extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'hipatch'; 
    }
   
    public function rules()
    {
        return [

            [['id','tab_nom','fio','tel','tel_town','nazv','line','phone_type'],'safe']
            ];
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


