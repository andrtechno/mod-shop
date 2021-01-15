<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \panix\mod\shop\models\search\ProductRelatedSearch */
/* @var $form yii\widgets\ActiveForm */
?>


    <div class="form-group row field-product-id">
        <div class="col-sm-4 col-md-4 col-lg-3 col-xl-2">
            <?= Html::activeLabel($model, 'id', ['class' => 'col-form-label']) ?>
        </div>
        <div class="col-sm-8 col-md-8 col-lg-9 col-xl-10">
            <?= Html::activeTextInput($model, 'id', ['class' => 'form-control']) ?>
            <?= Html::error($model, 'id', []) ?>
        </div>
    </div>

<?php
$this->registerJs('
$(document).on("keyup", "#productrelatedsearch-id" , function(event,k) {
    var data = $("#RelatedProductsGrid").yiiGridView("data");

    $.pjax({
        url: data.settings.filterUrl,
        container: "#pjax-RelatedProductsGrid",
        type:"GET",
        push:false,
        timeout:false,
        scrollTo:false,
        data:$(this).serialize()
    });
    return false;
});
');
