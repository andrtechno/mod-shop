<?php

use yii\helpers\ArrayHelper;
use yii\caching\DbDependency;
use panix\mod\shop\models\Manufacturer;
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
<?=

$form->field($model, 'manufacturer_id')->dropDownList(ArrayHelper::map(Manufacturer::find()->cache(3200, new DbDependency(['sql' => 'SELECT MAX(`updated_at`) FROM ' . Manufacturer::tableName()]))->all(), 'id', 'name'), [
    'prompt' => html_entity_decode($model::t('SELECT_MANUFACTURER_ID'))
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