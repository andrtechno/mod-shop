<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;

?>

<div class="card">
    <div class="card-header">
        <h5><?= Html::encode($this->context->pageName) ?></h5>
    </div>
    <div class="card-body">
        <?php
        $form = ActiveForm::begin();
        ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'address')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'phone')->widget(\panix\ext\telinput\PhoneInput::class) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>
        <div class="form-group text-center">
            <?= $model->submitButton(); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
