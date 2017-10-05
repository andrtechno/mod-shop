<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;
use panix\mod\shop\models\Category;
use panix\ext\tinymce\TinyMce;
use yii\bootstrap\Alert;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::encode($this->context->pageName) ?></h3>
    </div>
    <div class="panel-body">
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


        <?=
        $form->field($model, 'description')->widget(TinyMce::className(), [
            'options' => ['rows' => 6],
        ]);
        ?>

        <div class="form-group text-center">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

