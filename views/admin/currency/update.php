<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;


?>



<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::encode($this->context->pageName) ?></h3>
    </div>
    <div class="panel-body">


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
        <?= $form->field($model, 'iso')->textInput(['maxlength' => 10]) ?>
        <?= $form->field($model, 'symbol')->textInput(['maxlength' => 10]) ?>
        <?= $form->field($model, 'rate')->textInput() ?>
        <?= $form->field($model, 'is_main')->checkbox() ?>
         <?= $form->field($model, 'is_default')->checkbox() ?>




        <div class="form-group text-center">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>



    </div>
</div>
