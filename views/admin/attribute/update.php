<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;

/**
 * @var $this \yii\web\View
 * @var $form \panix\engine\bootstrap\ActiveForm
 * @var $model \panix\mod\shop\models\Attribute
 */

echo \panix\engine\bootstrap\Alert::widget([
    'options' => [
        'class' => 'alert-info',
    ],
    'closeButton' => false,
    'body' => Yii::t('shop/Attribute', 'INFO', ['productType' => Html::a('типу товара', '/admin/shop/productType')]),
]);


if ($model->isNewRecord && !$model->type) {

    echo Html::beginForm('', 'GET');

    ?>
    <div class="card">
        <div class="card-header">
            <h5><?= Html::encode($this->context->pageName) ?></h5>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-4"><?= Html::activeLabel($model, 'type', ['class' => 'control-label']); ?></div>
                <div class="col-sm-8">
                    <?php echo Html::activeDropDownList($model, 'type', [1 => 'Стандартный', 8 => 'Слайдер', 9 => 'Цвет'], ['class' => 'form-control']); ?>
                </div>
            </div>


        </div>
        <div class="card-footer text-center">
            <?= Html::submitButton(Yii::t('app', 'CREATE', 0), ['name' => false, 'class' => 'btn btn-success']); ?>
        </div>
    </div>
    <?php
    echo Html::endForm();

} else {

    ?>

    <?php

    $form = ActiveForm::begin();
    ?>
    <div class="card">
        <div class="card-header">
            <h5><?= Html::encode($this->context->pageName) ?></h5>
        </div>
        <div class="card-body">

            <?php


            $tabs[] = [
                'label' => 'Основные',
                'encode' => false,
                'content' => $this->render('tabs/_main', ['form' => $form, 'model' => $model]),
                'active' => true,

            ];

            if ($model->type == $model::TYPE_COLOR) {
                $tabs[] = [
                    'label' => $model::t('TAB_COLOR'),
                    'encode' => false,
                    'options'=>['id'=>'tab-color'],
                    'content' => $this->render('tabs/_color', ['form' => $form, 'model' => $model]),
                    'headerOptions' => [],

                ];
            } else {
                $tabs[] = [
                    'label' => (isset($model->tab_errors['options'])) ? Html::icon('warning', ['class' => 'text-danger', 'title' => $model->tab_errors['options']]) . ' '.$model::t('TAB_OPTIONS') : $model::t('TAB_OPTIONS'),
                    'encode' => false,
                    'options'=>['id'=>'tab-options'],
                    'content' => $this->render('tabs/_options', ['form' => $form, 'model' => $model]),
                    'headerOptions' => [],

                ];
            }


            echo panix\engine\bootstrap\Tabs::widget([
                'options' => ['id' => 'attributes-tabs'],
                'items' => $tabs,
            ]);
            ?>
        </div>
        <div class="card-footer text-center">
            <?= $model->submitButton(); ?>
        </div>
    </div>
    <?php ActiveForm::end();

} ?>
