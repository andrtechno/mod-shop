<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\ShopManufacturer;
use panix\mod\shop\models\ShopCategory;
use panix\ext\tinymce\TinyMce;


?>
<?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'seo_alias')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'sku')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'price')->textInput(['maxlength' => 10]) ?>
<?= $form->field($model, 'manufacturer_id')->dropDownList(ArrayHelper::map(ShopManufacturer::find()->all(), 'id', 'name'), [
    'prompt' => 'Укажите производителя'
]); ?>


<?= $form->field($model, 'main_category_id')->dropDownList(ShopCategory::flatTree(), [
    'prompt' => 'Укажите категорию'
]); ?>
<?= $form->field($model, 'full_description')->widget(TinyMce::className(), [
    'options' => ['rows' => 6],
    'clientOptions' => [
        'plugins' => [
            "advlist autolink lists link charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste"
        ],
        'toolbar' => "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
    ]
]);
?>