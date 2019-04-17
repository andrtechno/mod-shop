<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;
use panix\mod\shop\models\Category;
use panix\ext\tinymce\TinyMce;
use panix\engine\bootstrap\Alert;
use panix\ext\taginput\TagInput;
?>
<div class="card bg-light">
    <div class="card-header">
        <h5><?= Html::encode($this->context->pageName) ?></h5>
    </div>
    <div class="card-body">
        <?php
        if (Yii::$app->request->get('parent_id')) {
            $parent = Category::findOne(Yii::$app->request->get('parent_id'));
            echo Alert::widget([
                'closeButton' => false,
                'options' => [
                    'class' => 'alert-info',
                ],
                'body' => "Добавление в категорию: " . $parent->name,
            ]);
        }
        ?>

        <?php
        $form = ActiveForm::begin([
                    'options' => ['class' => 'form-horizontal'],
        ]);
        ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'seo_alias')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'description')->widget(TinyMce::class, ['options' => ['rows' => 6]]); ?>
        <?= $form->field($model, 'seo_product_title')->textInput(['maxlength' => 255]); ?>
        <?= $form->field($model, 'seo_product_description')->textarea(['options' => ['rows' => 6]]); ?>
        <div class="form-group text-center">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

