<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;

echo \panix\engine\bootstrap\Alert::widget([
    'options' => [
        'class' => 'alert-info',
    ],
    'closeButton'=>false,
    'body' => Yii::t('shop/Attribute', 'INFO', ['productType' => Html::a('типу товара', '/admin/shop/productType')]),
]);

?>


<div class="card bg-light">
    <div class="card-header">
        <h5><?= Html::encode($this->context->pageName) ?></h5>
    </div>
    <div class="card-body">
        <?php
        $form = ActiveForm::begin([
            'id' => basename(get_class($model)),
            'options' => [
                'class' => 'form-horizontal',
            ]
        ]);
        ?>
        <?php
        echo panix\engine\bootstrap\Tabs::widget([
            'options' => ['id' => 'attributes-tabs'],
            'items' => [
                [
                    'label' => 'Основные',
                    'content' => $this->render('tabs/_main', ['form' => $form, 'model' => $model]),
                    'active' => true,

                ],
                [
                    'label' => 'Опции',
                    'content' => $this->render('tabs/_options', ['form' => $form, 'model' => $model]),
                    'headerOptions' => [],

                ],
            ],
        ]);
        ?>
        <div class="form-group text-center">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>