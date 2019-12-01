<?php
use panix\engine\Html;
use yii\helpers\ArrayHelper;

$this->registerJs('$.configureBoxes({useFilters: false, useCounters: false});');
?>

<div class="form-group row required">
    <div class="col-sm-4"><?= Html::activeLabel($model, 'name', ['class' => 'col-form-label']); ?></div>
    <div class="col-sm-8"><?= Html::activeTextInput($model, 'name', ['class' => 'form-control']); ?></div>
</div>

<div class="form-group row">
    <div class="leftBox col-lg-5">

        <?= Html::label(Yii::t('shop/admin', 'Атрибуты продукта'), 'box2View',['class' => 'col-form-label']) ?>
        <br/>
        <?php
        echo Html::dropDownList('attributes[]', null, ArrayHelper::map($model->shopAttributes, 'id', 'title'), array('id' => 'box2View', 'multiple' => true, 'class' => 'form-control multiple attributesList', 'style' => 'height:300px;width:100%;'));
        ?>

        <br/>
        <span id="box2Counter" class="countLabel"></span>
    </div>

    <div class="dualControl col-lg-2 text-center2 d-flex align-items-center">

        <div class="btn-group mx-auto">
            <button id="to2" type="button" class="dualBtn btn btn-sm btn-secondary">
                <?= Html::icon('arrow-left'); ?>
            </button>
            <button id="to1" type="button" class="dualBtn btn btn-sm btn-secondary">
                <?= Html::icon('arrow-right'); ?>
            </button>
        </div>
        <div class="btn-group mx-auto">
            <button id="allTo2" type="button" class="dualBtn btn btn-sm btn-secondary">
                <?= Html::icon('double-arrow-left'); ?>
            </button>
            <button id="allTo1" type="button" class="dualBtn btn btn-sm btn-secondary">
                <?= Html::icon('double-arrow-right'); ?>
            </button>
        </div>
    </div>

    <div class="rightBox col-lg-5">

        <?= Html::label(Yii::t('shop/admin', 'Доступные атрибуты'), 'box1View',['class' => 'col-form-label']) ?><br/>
        <?php

        echo Html::dropDownList('allAttributes', null, ArrayHelper::map($attributes, 'id', 'title'), array('id' => 'box1View', 'multiple' => true, 'class' => 'form-control multiple attributesList', 'style' => 'height:300px;width:100%;'));
        ?>

        <br/>
        <span id="box1Counter" class="countLabel"></span>


    </div>
    <div class="clear"></div>
</div>



