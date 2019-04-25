<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;


?>



<div class="card bg-light">
    <div class="card-header">
        <h5><?= Html::encode($this->context->pageName) ?></h5>
    </div>
    <div class="card-body">


        <?php
        $form = ActiveForm::begin([
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-4',
                            'offset' => 'col-sm-offset-4',
                            'wrapper' => 'col-sm-8',
                            'error' => '',
                            'hint' => '',
                        ],
                    ],
                    'options' => ['class' => 'form-horizontal']
        ]);
        ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'address')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'phone')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>




        <div class="form-group text-center">
            <?= $model->submitButton(); ?>
        </div>

        <?php ActiveForm::end(); ?>



    </div>
</div>
