<?php

use panix\engine\Html;
use yii\helpers\HtmlPurifier;

?>


<div class="product">
    <div class="product-image">
        <?php
        echo Html::a(Html::img($model->getMainImageUrl('400x250'), ['alt' => $model->name, 'class' => 'img-fluid']), $model->getUrl());
        ?>
    </div>

   <div class="product-info">
        <div class="product-title">
            <h4 class="group inner list-group-item-heading"><?= Html::a(Html::encode($model->name), $model->getUrl()) ?></h4>

        </div>

        <div class="product-price clearfix">

            <span class="price"><span><?= $model->priceRange() ?></span> <sup><?= Yii::$app->currency->active->symbol ?></sup></span>



                <?php if ($model->appliedDiscount) { ?>

                    <div class="product-price product-price-discount">
                        <span><?= Yii::$app->currency->number_format(Yii::$app->currency->convert($model->originalPrice)) ?></span><sup><?= Yii::$app->currency->active->symbol ?></sup>
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
        <?php echo Html::a(Html::icon('shopcart') .' '. Yii::t('cart/default', 'BUY'), 'javascript:cart.add(' . $model->id . ')', array('class' => 'btn btn-primary')); ?>


        <?php echo $model->endCartForm(); ?>
    </div>
</div>










