<?php

use yii\helpers\ArrayHelper;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\Category;
use panix\ext\tinymce\TinyMce;
use panix\mod\shop\models\Supplier;
use panix\mod\shop\models\Attribute;

/**
 * @var panix\engine\bootstrap\ActiveForm $form
 */

?>


<?php if (!$model->auto) { ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'slug')->textInput(['maxlength' => 255]) ?>
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

$form->field($model, 'manufacturer_id')->dropDownList(ArrayHelper::map(Manufacturer::find()->all(), 'id', 'name'), [
    'prompt' => html_entity_decode($model::t('SELECT_MANUFACTURER_ID'))
]);
?>

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