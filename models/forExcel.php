<?php
/**
 Для сброса основных показателей по рассчету стоимости работ в Excel
 */

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;

class ForExcel extends Model
{
    public $nazv;
    public $rabota;
    public $delivery;
    public $transp;
    public $all;
    public $nds;
    public $all_nds;
    public $adr_work;
    public $comment;

    public function attributeLabels()
    {
        return [
            'delivery' => 'Доставка бригади, грн',
            'rabota' => 'Вартість роботи, грн',
            'usluga' => 'Напрямок роботи (послуги):',
            'transp' => 'Транспортні послуги, грн',
            'all' => 'Всього',
            'all_nds' => 'Всього з ПДВ',
            'nazv' => 'Вид послуги ',
            'nds' => 'ПДВ'
        ];
    }

    public function rules()
    {
        return [
            [['nazv','rabota','delivery','transp',
                'all','nds','all_nds','adr_work','comment'], 'safe']
        ];
    }

}
