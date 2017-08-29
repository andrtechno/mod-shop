<?php

//use Yii;
use yii\helpers\Html;
?>
<h1><?= $model->name ?></h1>
<p><?= $model->full_description ?></p>

<?php
if ($prev = $model->getNextOrPrev('prev')) {
    echo Html::a('prev '.$prev->name, $prev->getUrl());
}
echo '<br>';
if ($next = $model->getNextOrPrev('next')) {
    echo Html::a($next->name.' next', $next->getUrl());
}
?>
<?php ?>

<div class="price">
    <span><?= Yii::$app->currency->convert($model->price); ?></span>
    <sub><?= Yii::$app->currency->active->symbol; ?></sub>
</div>
