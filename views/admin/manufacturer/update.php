<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;


?>



<div class="card bg-light">
    <div class="card-header">
        <h5><?= Html::encode($this->context->pageName) ?></h5>
    </div>
    <div class="card-body">


        <?php
        $form = ActiveForm::begin([
                    'layout' => 'horizontal',
                    'options' => ['enctype' => 'multipart/form-data']
        ]);
        ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'seo_alias')->textInput(['maxlength' => 255]) ?>

        <?= $form->field($model, 'image')->fileInput() ?>




        <div class="form-group text-center">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

        <?php

        $s = $model->getImageUrl('image','100x100');
        echo $s;
        ?>

    </div>
</div>
