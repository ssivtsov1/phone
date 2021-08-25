<?php
/**
 * Используется для общих данных
 */
namespace app\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;


class Cdata extends \yii\db\ActiveRecord
{


    public static function tableName()
    {
        return 'cdata';
    }


    public function rules()
    {
        return [

            [['gen_dir'],'safe']
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


