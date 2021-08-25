<?php
use yii\helpers\Html;

$music = "http://localhost/CalcWork/web/zvukovye-effekty-korotkie-fanfary.mp3";

$audio = "<embed src='".$music."'>";
?>
 <div class="d15" >
        <h3><?= Html::encode("Увага з’явилась нова заявка") ?></h3>

 </div>
<?php
    echo $audio;
?>

    <code><?//= __FILE__ ?></code>


