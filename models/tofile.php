<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;


class tofile extends Model
{
public $file;

public function rules()
{
    return [
        [['file'], 'file'],
    ];
}
}
?>