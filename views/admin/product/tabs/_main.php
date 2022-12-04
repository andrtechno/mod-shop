<?php

use yii\helpers\ArrayHelper;
use yii\caching\DbDependency;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Category;
use panix\ext\tinymce\TinyMce;

/**
 * @var panix\engine\bootstrap\ActiveForm $form
 */

?>


<?php if (!$model->auto) { ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?php //echo $form->field($model, 'slug')->textInput(['maxlength' => 255]) ?>
<?php } ?>
<?= $form->field($model, 'sku')->textInput(['maxlength' => 255]) ?>

<?php
echo $this->render('_prices', ['model' => $model, 'form' => $form]);
?>
<?php
/*echo $form->field($model, 'label')->dropDownList($model::labelsList(), [
    'prompt' => html_entity_decode($model::t('SELECT_LABEL'))
]);*/
?>

<?php
/*
echo $form->field($model, 'brand_id')->dropDownList(ArrayHelper::map(Brand::find()->cache(3200, new DbDependency(['sql' => 'SELECT MAX(`updated_at`) FROM ' . Brand::tableName()]))->orderBy(['name_' . Yii::$app->language => SORT_ASC])->all(), 'id', 'name'), [
    'prompt' => html_entity_decode($model::t('SELECT_BRAND_ID'))
]);*/


echo $form->field($model, 'brand_id')->widget(\panix\ext\select2\Select2::class,[
    'items' => ArrayHelper::map(Brand::find()->cache(3200, new DbDependency(['sql' => 'SELECT MAX(`updated_at`) FROM ' . Brand::tableName()]))->orderBy(['name_' . Yii::$app->language => SORT_ASC])->all(), 'id', 'name'),
    'options' => [
        'prompt' => html_entity_decode($model::t('SELECT_BRAND_ID'))
    ],
    'clientOptions' => [
        /// 'placeholder'=>Yii::t('app/default', 'EMPTY_LIST'),
        'width' => '100%'
    ]
]);





$model->label = $model->getLabel();
?>

<?= $form->field($model, 'label')->checkboxList($model::getLabelList()) ?>
<?=

$form->field($model, 'main_category_id')->dropDownList(Category::flatTree(), [
    'prompt' => html_entity_decode($model::t('SELECT_MAIN_CATEGORY_ID'))
]);
?>
<?=

$form->field($model, 'full_description')->widget(TinyMce::class, [
    'options' => ['rows' => 6],
]);

?>
<?php echo $form->field($model, 'tagValues')->widget(\panix\ext\taginput\TagInput::class); ?>
