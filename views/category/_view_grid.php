<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

?>


<div class="product">
    <div class="product-image">
        <?php
        echo Html::a(Html::img($model->getMainImageUrl('400x250'), ['alt' => $model->name, 'class' => 'group list-group-image']), $model->getUrl());
        ?>
    </div>

   <div class="product-info">
        <div class="product-title">
            <h4 class="group inner list-group-item-heading"><?= Html::a(Html::encode($model->name), $model->getUrl()) ?></h4>

        </div>

        <div class="product-price clearfix">

            <span class="price float-left"><span><?= $model->priceRange() ?></span> <sup><?= Yii::$app->currency->active->symbol ?></sup></span>

            <div class="product-price">

                <?php if ($model->appliedDiscount) { ?>

                    <div class="product-price clearfix product-price-discount">
                        <span><?= $model::formatPrice(Yii::$app->currency->convert($model->originalPrice)) ?></span><sup><?= Yii::$app->currency->active->symbol ?></sup>
                    </div>
                <?php } ?>
                <?= Yii::$app->currency->convert($model->price); ?>
                <sup><?= Yii::$app->currency->active->symbol; ?></sup>
            </div>


        </div>

    </div>
    <div class="">

        <?php
        echo $model->beginCartForm();
        ?>
        <div class="action btn-group">


        </div><!-- /.action -->
        <?php echo Html::a('<i class="icon-shopcart"></i>' . Yii::t('cart/default', 'BUY'), 'javascript:cart.add(' . $model->id . ')', array('class' => 'btn btn-primary')); ?>


        <?php echo $model->endCartForm(); ?>
    </div>
</div>










