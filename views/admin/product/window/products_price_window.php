<?php

use panix\engine\bootstrap\ActiveForm;

$form = ActiveForm::begin([
            'id' => 'updateprice-form',
            'options' => [
                'class' => 'form-horizontal',
            ]
        ]);
$this->theme->alert('warning', 'Внимание товары которые привязаны к валюте и/или используют конфигурации изменены не будут', false);

echo $form->field($model, 'price')->textInput([
    //  'placeholder' => $model->getAttributeLabel('price'),
    'class' => 'form-control'
]);
 ActiveForm::end(); ?>