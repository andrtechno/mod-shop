<?php

use yii\helpers\Html;

$languages = Yii::$app->languageManager->getLanguages();
?>


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
                    <span class="d-none d-md-inline-block"><?php echo $data->name; ?></span>
                </a>
            </li>
        <?php }
    } ?>
</ul>

<div class="tab-content" id="myTabContent">
    <?php foreach ($languages as $language => $data) {
        if (in_array($language, ['uk', 'ru'])) {
            ?>
            <?php $active = ($language == Yii::$app->language) ? 'show active' : ''; ?>
            <div class="tab-pane fade <?= $active ?>" id="t<?= md5($language); ?>"
                 role="tabpanel"
                 aria-labelledby="t<?= md5($language); ?>-tab">

                <?php
                echo $form->field($model, 'seo_brand_title_' . $language)->textInput();
                echo $form->field($model, 'seo_brand_description_' . $language)->textInput();
                echo $form->field($model, 'seo_brand_h1_' . $language)->textInput();
                ?>
            </div>
        <?php }
    } ?>
</div>


<div class="form-group">
    <div class="col-12">
        <h5>Шаблоны</h5>
        <div><code>{name}</code> &mdash; Название бренда</div>
    </div>
</div>

<div class="form-group text-center">
    <h4>Продвижение бренда в категории</h4>
    <div class="text-muted">Выбранный бренд в фильтрах</div>
</div>
<ul class="nav nav-tabs" role="tablist">
    <?php foreach ($languages as $language => $data) {
        if (in_array($language, ['uk', 'ru'])) {
            ?>
            <?php $active = ($language == Yii::$app->language) ? 'active' : ''; ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $active ?>" id="home-tab" data-toggle="tab"
                   href="#t<?= md5($language); ?>-catalog" role="tab"
                   aria-controls="t<?= md5($language); ?>"
                   aria-selected="true">

                    <?php echo Html::img("/uploads/language/{$data->flag_name}"); ?>
                    <span class="d-none d-md-inline-block"><?php echo $data->name; ?></span>
                </a>
            </li>
        <?php }
    } ?>
</ul>

<div class="tab-content" id="myTabContent">
    <?php foreach ($languages as $language => $data) {
        if (in_array($language, ['uk', 'ru'])) {
            ?>
            <?php $active = ($language == Yii::$app->language) ? 'show active' : ''; ?>
            <div class="tab-pane fade <?= $active ?>" id="t<?= md5($language); ?>-catalog"
                 role="tabpanel"
                 aria-labelledby="t<?= md5($language); ?>-catalog-tab">

                <?php
                echo $form->field($model, 'seo_catalog_brand_title_' . $language)->textInput();
                echo $form->field($model, 'seo_catalog_brand_description_' . $language)->textInput();
                echo $form->field($model, 'seo_catalog_brand_h1_' . $language)->textInput();
                ?>
            </div>
        <?php }
    } ?>
</div>


<div class="form-group">
    <div class="col-12">
        <h5>Шаблоны</h5>
        <div><code>{name}</code> &mdash; Название бренда</div>
        <div><code>{category}</code> &mdash; Название категории</div>
    </div>
</div>
