<?php

use panix\engine\Html;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Length;
use panix\mod\shop\models\Weight;
use yii\helpers\ArrayHelper;

$attributes = (isset($model->type->shopAttributes)) ? $model->type->shopAttributes : [];

/*
echo \panix\engine\barcode\BarcodeGenerator::widget([
    'elementId' => 'showBarcode',
    'value' => '4797111018719',
    'type' => 'ean13'
]);*/
?>


<div class="form-group row">
    <div class="col-sm-4 col-lg-2"></div>
    <div class="col-sm-8 col-lg-10">
        <div class="row">
            <div class="col-sm-4">
                <?= Html::activeLabel($model, 'length', ['class' => 'col-form-label']); ?>
                <?= Html::activeTextInput($model, 'length', ['class' => 'form-control']); ?>
            </div>
            <div class="col-sm-4">
                <?= Html::activeLabel($model, 'width', ['class' => 'col-form-label']); ?>
                <?= Html::activeTextInput($model, 'width', ['class' => 'form-control']); ?>
            </div>
            <div class="col-sm-4">
                <?= Html::activeLabel($model, 'height', ['class' => 'col-form-label']); ?>
                <div class="input-group">
                    <?= Html::activeTextInput($model, 'height', ['class' => 'form-control']); ?>
                    <div class="input-group-append">
                        <?= Html::activeDropDownList($model, 'length_class_id', ArrayHelper::map(Length::find()->all(), 'id', 'title'), ['class' => 'custom-select']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-group row">
    <div class="col-sm-4 col-lg-2"><?= Html::activeLabel($model, 'weight', ['class' => 'col-form-label']); ?></div>
    <div class="col-sm-8 col-lg-10">

        <div class="input-group">
            <?= Html::activeTextInput($model, 'weight', ['class' => 'form-control']); ?>
            <div class="input-group-append">
                <?= Html::activeDropDownList($model, 'weight_class_id', ArrayHelper::map(Weight::find()->all(), 'id', 'title'), ['class' => 'custom-select']); ?>
            </div>
        </div>
    </div>
</div>

<div class="row ml-0z mr-0z">
    <?php
    if (empty($attributes)) {
        echo \panix\engine\bootstrap\Alert::widget([
            'options' => ['class' => 'alert-info'],
            'body' => Yii::t('shop/admin', 'EMPTY_ATTRIBUTES_LIST')
        ]);

    } else {


        foreach ($eavList as $group_name => $attributes) {
            echo '<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6"><h5 class="text-center mt-3">' . $group_name . '</h5>';

            foreach ($attributes as $a) {
                //   \panix\engine\CMS::dump($a['attribute']);die;
                if ($a['attribute']->type == Attribute::TYPE_DROPDOWN) {
                    $addOptionLink = Html::a(Html::icon('add'), '#', [
                        'rel' => $a['attribute']->id,
                        'data-name' => $a['attribute']->getIdByName(), //$a->getIdByName()
                        //'data-name' => Html::getInputName($a, $a->name),
                        'onclick' => 'js: return addNewOption($(this));',
                        'class' => 'btn btn-sm btn-success', // btn-sm mt-2 float-right
                        'title' => Yii::t('shop/admin', 'ADD_OPTION')
                    ]);

                    // . ' ' . Yii::t('shop/admin', 'ADD_OPTION')
                } else
                    $addOptionLink = null;


                $error = '';
                $inputClass = '';

                if ($a['attribute']->required && array_key_exists($a['attribute']->name, $model->getErrors())) {
                    $inputClass = 'is-invalid';
                    $error = Html::error($a, $a['attribute']->name);
                }

                ?>
                <div class="form-group row <?= ($a['attribute']->required ? 'required' : ''); ?>">
                    <?= Html::label($a['attribute']->title, $a['attribute']->name, ['class' => 'col-sm-4 col-form-label']); ?>
                    <div class="col-sm-8 rowInput eavInput">
                        <div class="input-group2 <?= ($a['attribute']->type == Attribute::TYPE_CHECKBOX_LIST) ? '1' : ''; ?> row no-gutters">
                            <div class="col-10">
                                <?= $a['attribute']->renderField($a['value'], $inputClass); ?>

                                <?php if ($a['attribute']->abbreviation) { ?>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><?= $a['attribute']->abbreviation; ?></span>
                                    </div>
                                <?php } ?>
                            </div>

                            <?php if ($addOptionLink) { ?>
                                <div class="col-2 text-right">
                                    <?= $addOptionLink; ?>
                                </div>
                            <?php } ?>

                        </div>
                        <?= $error; ?>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
        }
    }
    ?>
</div>

