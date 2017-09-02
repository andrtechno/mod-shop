<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::a(Html::encode($model->name), $model->getUrl()) ?></h3>
    </div>
    <div class="panel-body">

        <?= HtmlPurifier::process($model->name) ?>    
    </div>
</div>
