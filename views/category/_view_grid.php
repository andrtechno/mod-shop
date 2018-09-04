<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

?>
<?= $model->beginCartForm(); ?>
<div class="col-sm-4 item">
    <div class="thumbnail ">
        <?php
        echo Html::a(Html::img($model->getMainImageUrl('400x250'), ['alt' => $model->name, 'class' => 'group list-group-image']), $model->getUrl());
        ?>

        <div class="caption">
            <h4 class="group inner list-group-item-heading"><?= Html::a(Html::encode($model->name), $model->getUrl()) ?></h4>
            <div class="row">
                <div class="col-xs-12 col-md-6">
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
                <div class="col-xs-12 col-md-6">
                    <?php echo Html::a('<i class="icon-shopcart"></i>' . Yii::t('cart/default', 'BUY'), 'javascript:cart.add(' . $model->id . ')', array('class' => 'btn btn-primary')); ?>
                </div>
            </div>
        </div>

    </div>
</div>


<?php echo Html::endForm(); ?>
