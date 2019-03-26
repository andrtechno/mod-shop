<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\Category;
use panix\ext\tinymce\TinyMce;
?>
<?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'seo_alias')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'sku')->textInput(['maxlength' => 255]) ?>


<?php
echo $this->render('_prices',['model'=>$model,'form'=>$form]);
?>
<?=

$form->field($model, 'manufacturer_id')->dropDownList(ArrayHelper::map(Manufacturer::find()->all(), 'id', 'name'), [
    'prompt' => 'Укажите производителя'
]);
?>


<?=

$form->field($model, 'main_category_id')->dropDownList(Category::flatTree(), [
    'prompt' => 'Укажите категорию'
]);
?>
<?=

$form->field($model, 'full_description')->widget(TinyMce::class, [
    'options' => ['rows' => 6],
]);

?>