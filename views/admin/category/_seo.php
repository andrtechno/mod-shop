<?php

/**
 * @var $form \panix\engine\bootstrap\ActiveForm
 * @var $model \panix\mod\shop\models\Category
 */
?>

<h5 class="form-group text-center pt-3 pb-3">Шаблон SEO для категории</h5>

<?= $form->field($model, 'meta_title')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'meta_description')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'h1')->textInput(['maxlength' => 255]) ?>


<h5 class="form-group text-center pt-3 pb-3">Шаблон SEO для подкатегорий вышеуказанной категории</h5>
<?php // $form->field($model, 'use_seo_parents')->checkbox() ?>
<?= $form->field($model, 'meta_child_title')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'meta_child_description')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'h1_child')->textInput(['maxlength' => 255]) ?>

<div class="form-group">
    <div class="col-12">
        <h5>Шаблоны</h5>
        <div><code>{name}</code> &mdash; Название категории</div>
        <div><code>{h1}</code> &mdash; H1, если пустой выведит Название категории</div>
        <div><code>{min_price}</code> &mdash; Минимальная цена</div>
        <div><code>{currency.symbol}</code> &mdash; Символ валюты</div>
    </div>
</div>
