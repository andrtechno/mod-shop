<?php

use panix\engine\bootstrap\ActiveForm;
use panix\engine\Html;

$form = ActiveForm::begin([
    'action' => ['/shop/print/termo'],
    'id' => 'form',

]) ?>
<div class="card">
    <div class="card-body">

        <?= $form->field($model, 'type')->dropdownList(['termo' => 'Термо', 'cut' => 'A4 cut']) ?>
        <?= $form->field($model, 'size')->dropdownList($model->getSizes()) ?>
        <?= $form->field($model, 'items') ?>


    </div>
    <div class="card-footer">
        <?= Html::submitButton('Генерировать', ['class' => 'btn btn-primary']) ?>
    </div>

</div>
<?php ActiveForm::end() ?>


