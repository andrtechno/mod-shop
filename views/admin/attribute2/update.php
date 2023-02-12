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


$types[1] = 'Стандартный';
if (YII_DEBUG) {
    $types[$model::TYPE_SLIDER] = 'Слайдер (test)';
    $types[$model::TYPE_COLOR] = 'Цвет (test)';
}
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
                    <?= Html::activeDropDownList($model, 'type', $types, ['class' => 'form-control']); ?>
                </div>
            </div>


        </div>
        <div class="card-footer text-center">
            <?= Html::submitButton(Yii::t('app/default', 'CREATE', 0), ['name' => false, 'class' => 'btn btn-success']); ?>
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
                    'label' => (isset($model->tab_errors['color'])) ? Html::icon('warning', ['class' => 'text-danger', 'title' => $model->tab_errors['color']]) . ' ' . $model::t('TAB_COLOR') : $model::t('TAB_COLOR'),
                    'encode' => false,
                    'options' => ['id' => 'tab-color'],
                    'content' => $this->render('tabs/_color', ['form' => $form, 'model' => $model]),
                    'headerOptions' => [],

                ];
            }elseif ($model->type == $model::TYPE_SLIDER) {
                $tabs[] = [
                    'label' => (isset($model->tab_errors['slider'])) ? Html::icon('warning', ['class' => 'text-danger', 'title' => $model->tab_errors['slider']]) . ' ' . $model::t('TAB_SLIDER') : $model::t('TAB_SLIDER'),
                    'encode' => false,
                    'options' => ['id' => 'tab-slider'],
                    'content' => $this->render('tabs/_slider', ['form' => $form, 'model' => $model]),
                    'headerOptions' => [],

                ];

            } else {
                $tabs[] = [
                    'label' => (isset($model->tab_errors['options'])) ? Html::icon('warning', ['class' => 'text-danger', 'title' => $model->tab_errors['options']]) . ' ' . $model::t('TAB_OPTIONS') : $model::t('TAB_OPTIONS'),
                    'encode' => false,
                    'options' => ['id' => 'tab-options'],
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


<!-- Modal -->
<div class="modal fade" id="optionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить опцию</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php $optionForm = ActiveForm::begin(['id'=>'option-form','action'=>['/admin/shop/attribute2/add-option','id'=>$model->id]]); ?>
            <div class="modal-body">
                <?php


                $optionModel = new \panix\mod\shop\models\AttributeOption();
                $optionModel->attribute_id = $model->id;

                ?>
                <?= $optionForm->field($optionModel, 'value')->textInput(['maxlength' => 255]); ?>
                <?= $optionForm->field($optionModel, 'value_uk')->textInput(['maxlength' => 255]); ?>
                <?= $optionForm->field($optionModel, 'value_en')->textInput(['maxlength' => 255]); ?>


            </div>
            <div class="modal-footer">
                <?= $optionModel->submitButton(); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>