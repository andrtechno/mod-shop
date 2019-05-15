<?php

use panix\engine\bootstrap\ActiveForm;
use panix\engine\bootstrap\Alert;

$form = ActiveForm::begin([
    'id' => 'updateprice-form',
    'options' => [
        'class' => 'form-horizontal',
    ]
]);

echo Alert::widget([
    'options' => [
        'class' => 'alert-info',
    ],
    'body' => 'Внимание товары которые привязаны к валюте и/или используют конфигурации изменены не будут',
]);
echo $form->field($model, 'price')->textInput([
    //  'placeholder' => $model->getAttributeLabel('price'),
    'class' => 'form-control'
]);
ActiveForm::end();
?>