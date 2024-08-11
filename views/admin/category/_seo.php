<?php

use yii\helpers\Html;

/**
 * @var $form \panix\engine\bootstrap\ActiveForm
 * @var $model \panix\mod\shop\models\Category
 */

$languages = Yii::$app->languageManager->getLanguages();
?>

<h5 class="form-group text-center pt-3 pb-3">Шаблон SEO для категории</h5>

<ul class="nav nav-tabs" role="tablist">
    <?php foreach ($languages as $language => $data) {
        if (in_array($language, ['uk', 'ru'])) {
            ?>
            <?php $active = ($language == Yii::$app->language) ? 'active' : ''; ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $active ?>" id="home-tab" data-toggle="tab"
                   href="#t<?= md5($language); ?>" role="tab"
                   aria-controls="t<?= md5($language); ?>"
                   aria-selected="true">

                    <?php echo Html::img("/uploads/language/{$data->flag_name}"); ?>
                    <span class="d-none d-md-inline-block"><?= $data->name; ?></span>
                </a>
            </li>
        <?php }
    } ?>
</ul>

<div class="tab-content" id="myTabContent">
    <?php foreach ($languages as $language => $data) {
        if (in_array($language, ['uk', 'ru'])) {

            $field = ($language != 'uk') ? '_' . $language : '';

            ?>
            <?php $active = ($language == Yii::$app->language) ? 'show active' : ''; ?>
            <div class="tab-pane fade <?= $active ?>" id="t<?= md5($language); ?>"
                 role="tabpanel"
                 aria-labelledby="t<?= md5($language); ?>-tab">

                <?php
                echo $form->field($model, 'meta_title_' . $language)->textInput();
                echo $form->field($model, 'meta_description_' . $language)->textInput();
                echo $form->field($model, 'h1_' . $language)->textInput();
                ?>
            </div>
        <?php }
    } ?>
</div>



<h5 class="form-group text-center pt-3 pb-3">Шаблон SEO для подкатегорий вышеуказанной категории</h5>
<?php // $form->field($model, 'use_seo_parents')->checkbox() ?>


<ul class="nav nav-tabs" role="tablist">
    <?php foreach ($languages as $language => $data) {
        if (in_array($language, ['uk', 'ru'])) {
            ?>
            <?php $active = ($language == Yii::$app->language) ? 'active' : ''; ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $active ?>" id="home-tab" data-toggle="tab"
                   href="#t2<?= md5($language); ?>" role="tab"
                   aria-controls="t2<?= md5($language); ?>"
                   aria-selected="true">

                    <?php echo Html::img("/uploads/language/{$data->flag_name}"); ?>
                    <span class="d-none d-md-inline-block"><?= $data->name; ?></span>
                </a>
            </li>
        <?php }
    } ?>
</ul>

<div class="tab-content" id="myTabContent">
    <?php foreach ($languages as $language => $data) {
        if (in_array($language, ['uk', 'ru'])) {

            $field = ($language != 'uk') ? '_' . $language : '';

            ?>
            <?php $active = ($language == Yii::$app->language) ? 'show active' : ''; ?>
            <div class="tab-pane fade <?= $active ?>" id="t2<?= md5($language); ?>"
                 role="tabpanel"
                 aria-labelledby="t2<?= md5($language); ?>-tab">

                <?php
                echo $form->field($model, 'meta_child_title_' . $language)->textInput();
                echo $form->field($model, 'meta_child_description_' . $language)->textInput();
                echo $form->field($model, 'h1_child_' . $language)->textInput();
                ?>
            </div>
        <?php }
    } ?>
</div>


<div class="form-group">
    <div class="col-12">
        <h5>Шаблоны</h5>
        <div><code>{name}</code> &mdash; Название категории</div>
        <div><code>{h1}</code> &mdash; H1, если пустой выведит Название категории</div>
        <div><code>{min_price}</code> &mdash; Минимальная цена</div>
        <div><code>{currency.symbol}</code> &mdash; Символ валюты</div>
    </div>
</div>
