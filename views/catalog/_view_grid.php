<?php

use panix\engine\Html;
use yii\helpers\HtmlPurifier;

?>


<div class="card mb-4 shadow-sm">
    <?php
    echo Html::a(Html::img($model->getMainImage('400x225')->url, ['alt' => $model->name, 'class' => 'card-img-top']), $model->getUrl());
    ?>

    <div class="card-body">

        <?= Html::a(Html::encode($model->name), $model->getUrl(), ['class' => 'card-text']) ?>
        <div class="d-flex justify-content-between align-items-center">
            <?= $model->beginCartForm(); ?>
            <div class="btn-group">
                <?= Html::button(Html::icon('shopcart') . ' ' . Yii::t('cart/default', 'BUY'), ['onclick' => 'javascript:cart.add(this)', 'class' => 'btn btn-sm btn-primary']); ?>
            </div>

            <?= $model->endCartForm(); ?>

            <small class="text-muted"><strong><?= $model->priceRange() ?></strong> <?= Yii::$app->currency->active['symbol'] ?></small>
        </div>
    </div>
</div>













