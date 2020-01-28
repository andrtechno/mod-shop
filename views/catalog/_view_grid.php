<?php

use panix\engine\Html;
use yii\helpers\HtmlPurifier;

?>


<div class="product">
    <div class="product-image">
        <?php
        echo Html::a(Html::img($model->getMainImage('400x')->url, ['alt' => $model->name, 'class' => 'img-fluid']), $model->getUrl());
        ?>
    </div>

    <div class="product-info">
        <div class="product-title">
            <h4 class="group inner list-group-item-heading"><?= Html::a(Html::encode($model->name), $model->getUrl()) ?></h4>

        </div>

        <div class="product-price clearfix">

            <div>
                <span class="price">
                    <span><?= $model->priceRange() ?></span>
                    <sup><?= Yii::$app->currency->active['symbol'] ?></sup>
                </span>
            </div>

            <?php if ($model->hasDiscount) { ?>
                <div>
                    <span class="price price-strike">
                        discount
                        <span><?= Yii::$app->currency->number_format(Yii::$app->currency->convert($model->originalPrice)) ?></span>
                        <sup><?= Yii::$app->currency->active['symbol'] ?></sup>
                    </span>
                </div>

            <?php } ?>


        </div>

    </div>
    <div class="">

        <?php
        echo $model->beginCartForm();
        ?>
        <div class="action btn-group">


        </div>
        <?php echo Html::a(Html::icon('shopcart') . ' ' . Yii::t('cart/default', 'BUY'), 'javascript:cart.add(' . $model->id . ')', array('class' => 'btn btn-primary')); ?>


        <?php echo $model->endCartForm(); ?>
    </div>
</div>










