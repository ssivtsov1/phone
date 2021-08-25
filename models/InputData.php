<?php
/* Ввод основных данных для поиска телефонов */

namespace app\models;

use Yii;
use yii\base\Model;

class InputData extends Model
{
   
    public $id;
    public $main_unit;        // Главное подразделение
    public $fio;              // П.І.Б.
    public $unit_1;           // Підрозділ, підпорядкований головному
    public $unit_2;           // Підрозділ нижчого рівня
    public $post;             // Должность
    public $tel_mob;          // Моб. телефон
    public $tel;              // Телефон внутр.
    public $tel_town;         // Телефон городской.
    public $email;            //  E-Mail 
    public $id_t;
    public $id_p;
    public $sex;              // Пол
    public $photo;            // Фото сотрудника
    public $gpost;          // Група должностей
    public $vip;              // Расширенный режим

    private $_user;

    public function attributeLabels()
    {
        return [
            'main_unit' => 'Головний підрозділ:',
            'id_t' => '',
            'id_p' => '',
            'unit_1' => 'Підрозділ, підпорядкований головному:',
            'unit_2' => 'Група:',
            'fio' => 'П.І.Б.:',
            'post' => 'Посада:',
            'tel_mob' => 'Мобільний телефон:',
            'tel' => 'Телефон внутрішній:',
            'tel_town' => 'Телефон міський:',
            'email' => 'Адрес пошти:',
            'sex' => 'Стать:',
            'photo' => 'Фото:',
            'gpost' => 'Група посад:',
        ];
    }

    public function rules()
    {
        return [
            
            ['main_unit', 'safe'],
            ['unit_1', 'safe'],
            ['unit_2', 'safe'],
            ['fio', 'safe'],
            ['post', 'safe'],
            ['tel', 'safe'],
            //['tel_mob', 'safe'],
            ['tel_mob', 'match', 'pattern' => '/^[0-9-\s]*$/i'],
            ['tel_town', 'safe'],
            ['email', 'safe'],
            ['id_t', 'safe'],
            ['id_p', 'safe'],
            ['sex', 'safe'],
            ['gpost', 'safe'],
            ['photo', 'file'],
            ['vip', 'safe'],
        ];
    }

}
