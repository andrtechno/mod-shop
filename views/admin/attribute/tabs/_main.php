<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\Attribute;


?>
<?= $form->field($model, 'title')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'abbreviation')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'required')->checkbox(); ?>
<?= $form->field($model, 'display_on_front')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>

<?= $form->field($model, 'use_in_filter')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>
<?= $form->field($model, 'use_in_variants')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>
<?= $form->field($model, 'select_many')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>
<?= $form->field($model, 'use_in_compare')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>

<?= $form->field($model, 'type')->dropDownList(Attribute::getTypesList(), [
    'prompt' => 'Укажите производителя'
]); ?>





