<?php
/**
 * Используется для редактирования списка сотрудников
 */
namespace app\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

class List_workers extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '1c'; 
    }
   
    public function rules()
    {
        return [

            [['id','tab_nom','fio','post','main_unit','unit_1','unit_2','remark','photo'],'safe']
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


