<?php

use panix\mod\shop\models\Attribute;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\AttributeGroup;
?>
<?= $form->field($model, 'title')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'name')->textInput(['maxlength' => 255])->hint($model::t('HINT_NAME')); ?>
<?= $form->field($model, 'abbreviation')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'required')->checkbox(); ?>
<?=
$form->field($model, 'group_id')->dropDownList(ArrayHelper::map(AttributeGroup::find()->all(), 'id', 'name'), [
    'prompt' => 'Выберите группу'
]);
?>
<?= $form->field($model, 'display_on_front')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>
<?= $form->field($model, 'use_in_filter')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>
<?= $form->field($model, 'use_in_variants')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>
<?= $form->field($model, 'select_many')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>
<?= $form->field($model, 'use_in_compare')->dropDownList([1 => Yii::t('app', 'YES'), 0 => Yii::t('app', 'NO')]); ?>

<?= $form->field($model, 'type')->dropDownList(Attribute::typesList()); ?>
<?= $form->field($model, 'sort')->dropDownList(Attribute::sortList(),['prompt'=>$model::t('SORT_DEFAULT')]); ?>




