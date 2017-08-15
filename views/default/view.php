<?php
//use Yii;
?>
<h1><?= $model->name ?></h1>
<p><?= $model->full_description ?></p>

<p><?= $model->category->name ?></p>

<div class="price">
    <span><?= Yii::$app->currency->convert($model->price); ?></span>
    <sub><?= Yii::$app->currency->active->symbol; ?></sub>
</div>
