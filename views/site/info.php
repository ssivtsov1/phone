<?php
use yii\helpers\Html;
$this->title = $model->title;
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="site-about">-->
<div class=<?= $model->style_title ?> >
    <h3><?= Html::encode($this->title) ?></h3>
    </div>

    <div class=<?= $model->style1 ?> >
        <h3><?= Html::encode($model->info1) ?></h3>

    </div>

    <div class=<?= $model->style2 ?> >

        <h3><?= Html::encode($model->info2) ?></h3>
    </div>
    <code><?//= __FILE__ ?></code>
<!--</div>-->
