<?php
use panix\engine\Html;
use yii\helpers\ArrayHelper;

$product = new \panix\mod\shop\models\Product();
$templates = [
    'product_id' => $product->getAttributeLabel('id'),
    'product_name' => $product->getAttributeLabel('name'),
    'product_type' => $product->getAttributeLabel('type_id'),
    'product_sku' => $product->getAttributeLabel('sku'),
    'product_price' => $product->getAttributeLabel('price'),
    'product_category' => $product->getAttributeLabel('main_category_id'),
    'product_brand' => $product->getAttributeLabel('brand_id'),
    'currency.symbol' => Yii::$app->currency->active['symbol'],
    'currency.iso' => Yii::$app->currency->active['iso'],
];

?>
<div class="form-group row">
    <div class="col-sm-4"><?= Html::activeLabel($model, 'product_name', ['class' => 'col-form-label']); ?></div>
    <div class="col-sm-8"><?= Html::activeTextInput($model, 'product_name', ['class' => 'form-control']); ?></div>
</div>
<div class="form-group row">
    <div class="col-sm-4"><?= Html::activeLabel($model, 'product_title', ['class' => 'col-form-label']); ?></div>
    <div class="col-sm-8"><?= Html::activeTextInput($model, 'product_title', ['class' => 'form-control']); ?></div>
</div>
<div class="form-group row">
    <div class="col-sm-4"><?= Html::activeLabel($model, 'product_description', ['class' => 'col-form-label']); ?></div>
    <div class="col-sm-8"><?= Html::activeTextarea($model, 'product_description', ['class' => 'form-control']); ?></div>
</div>

<table class="table table-striped">
    <tr>
        <th width="30%">Код</th>
        <th width="70%">Описание</th>
    </tr>
    <?php foreach ($templates as $code => $desc) { ?>
        <tr>
            <td><code>{<?= $code; ?>}</code></td>
            <td><?= $desc; ?></td>
        </tr>
    <?php } ?>
    <tr>
        <th colspan="2">Атрибуты</th>
    </tr>
    <?php foreach ($model->shopAttributes as $tpl) { ?>
        <tr>
            <td><code>{eav_<?= $tpl->name; ?>.value}</code> &mdash; значение<br/><code>{eav_<?= $tpl->name; ?>.name}</code> &mdash; название</td>
            <td><?= $tpl->title; ?></td>
        </tr>
    <?php } ?>
</table>
