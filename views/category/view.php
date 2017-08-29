<?php
use yii\helpers\Url;


echo Url::previous(); // получаем ранее сохранённый URL
?>
<h1><?= $this->context->model->name ?></h1>
<?php


echo \yii\widgets\ListView::widget([
    'dataProvider' => $provider,
    'itemView' => $itemView,
]);
