<?php
/**
 * Модель для вывода разных информационных страниц
 * с небольшим содержанием информации.
 */

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;


class Info extends Model
{
    public $title = '';     // Заголовок
    public $info1 =  '';    // Информационная строка 1
    public $info2 =  '';    // Информационная строка 2
    public $info3 =  '';    // Информационная строка 3
    public $tag;            // Вспомогательное поле
    public $style1;            // Стиль оформления 1
    public $style2;            // Стиль оформления 2
    public $style_title;            // Стиль оформления заголовка


    public function rules()
    {
        return [
            [['title', 'info1', 'info2','style1','style2',
            'info3','tag','style','style_title'], 'safe'],
        ];
    }
}
