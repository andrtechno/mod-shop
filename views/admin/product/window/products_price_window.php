<?php

use panix\engine\bootstrap\ActiveForm;
use panix\engine\bootstrap\Alert;
\panix\engine\widgets\PjaxAsset::register($this);
$form = ActiveForm::begin();

echo Alert::widget([
    'options' => [
        'class' => 'alert-info',
    ],
    'body' => 'Внимание товары которые привязаны к валюте и/или используют конфигурации изменены не будут',
]);
echo $form->field($model, 'price')->textInput([
    //  'placeholder' => $model->getAttributeLabel('price'),
]);
ActiveForm::end();
