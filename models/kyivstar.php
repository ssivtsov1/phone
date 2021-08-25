<?php
/**
 * Используется для редактирования мобильных телефонов
 */
namespace app\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

class Kyivstar extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'kyivstar'; 
    }
   
    public function rules()
    {
        return [

            [['id','tab_nom','fio','tel','rate','type_tel','description'],'safe']
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


