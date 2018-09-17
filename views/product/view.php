<?php

use panix\engine\Html;
use yii\widgets\ActiveForm;
?>
<?php
$this->registerJs("
cart.spinnerRecount = false;
cart.skin = 'dropdown';

$(document).ready(function() {
    $('.carousel').carousel({
        interval: 6000
    });
});

", yii\web\View::POS_BEGIN, 'cart');

panix\engine\widgets\owlcarousel\Carousel::widget([
    'target' => '.owl-carousel',
    'options' => [
        'loop' => false,
        'margin' => 10,
        'nav' => true,
        'dots' => true,
        //  'dotsContainer'=> '.dotsCont',
        'items' => 5,
        'URLhashListener' => false,
        'stagePadding' => 0,
        'dotData' => true,
        'responsive' => [
            0 => [
                'items' => 1,
                'nav' => false,
                'dots' => false
            ],
            768 => [
                'items' => 3
            ],
            960 => [
                'items' => 5
            ],
            1200 => [
                'items' => 6
            ],
            1920 => [
                'items' => 7
            ],
        ]
    ]
]);
echo \yii\helpers\Inflector::titleize('CamelCase');

echo \yii\helpers\Inflector::ordinalize(15);

$words = ['Spain', 'France', 'Украина'];
echo \yii\helpers\Inflector::sentence($words);
?>

<div id="info"></div>
<div class="row">
    <div class="col-sm-6 col-xs-12">
        <?= Html::img($model->getMainImageUrl('500x500')); ?>

        <?php echo panix\mod\discounts\widgets\countdown\Countdown::widget(['model' => $model]) ?>
        <div class="dotsCont">
            <div>Fake Dot 1</div>
            <div>Fake Dot 2</div>
            <div>Fake Dot 3</div>
        </div>
        <div class="owl-carousel owl-theme">
            <?php foreach ($model->getImages(['is_main' => 0]) as $k => $image) { ?>
                <?= Html::img($image->getUrl('100x100'), ['data-hash' => $image->id, 'data-dot' => $k + 1]); ?>
            <?php } ?>
        </div>
    </div>
    <div class="col-sm-6 col-xs-12">
        <div class="btn-group">
            <?php
            if ($prev = $model->getNextOrPrev('prev')) {
              //  echo Html::a('prev ' . $prev->name, $prev->getUrl(), ['class' => 'btn btn-default']);
            }
            if ($next = $model->getNextOrPrev('next')) {
              //  echo Html::a($next->name . ' next', $next->getUrl(), ['class' => 'btn btn-default']);
            }



            if ($prev = $model->objectPrev) {
                echo Html::a('prev ' . $prev->name, $prev->getUrl(), ['class' => 'btn btn-default']);
            }
            if ($next = $model->objectNext) {
                echo Html::a($next->name . ' next', $next->getUrl(), ['class' => 'btn btn-default']);
            }

            ?>
        </div>
        <h1><?= $model->name ?></h1>



        <?php if ($model->appliedDiscount) { ?>

            <span class="price price-discount">
                <span><?= Yii::$app->currency->number_format(Yii::$app->currency->convert($model->discountPrice)) ?></span>
                <sup><?= Yii::$app->currency->active->symbol ?></sup>
            </span>
        <?php } ?>
        <span class="price <?php echo($model->appliedDiscount) ? 'strike' : ''; ?>">
            <span><?= Yii::$app->currency->number_format($model->getDisplayPrice()); ?></span>
            <sup><?= Yii::$app->currency->active->symbol; ?></sup>
        </span>
        <?= $model->beginCartForm(); ?>
        <?php
        echo Html::a(Html::icon('shopcart') . Yii::t('cart/default', 'BUY'), 'javascript:cart.add(' . $model->id . ')', array('class' => 'btn btn-primary'));
        ?>
        <?php
        echo yii\jui\Spinner::widget([
            'name' => "quantity",
            'value' => 1,
            'clientOptions' => [
                'numberFormat' => "n",
                //'icons'=>['down'=> "icon-arrow-up", 'up'=> "custom-up-icon"],
                'max' => 999
            ],
            'options' => ['class' => 'cart-spinner'],
        ]);

        echo panix\mod\cart\widgets\buyOneClick\BuyOneClickWidget::widget();
        ?>
        <?php
        if (Yii::$app->user->isGuest) {
            echo Html::a(Yii::t('wishlist/default', 'BTN_WISHLIST'), ['/users/register'], []);
        } else {
            echo Html::a(Yii::t('wishlist/default', 'BTN_WISHLIST'), 'javascript:wishlist.add(' . $model->id . ');', []);
        }
        ?>
        <?php echo Html::endForm(); ?>


        <ul class="list-group">
            <?php if ($model->manufacturer_id) { ?>
                <li class="list-group-item">
                    <?= $model->getAttributeLabel('manufacturer_id'); ?>: <?= Html::a($model->manufacturer->name, $model->manufacturer->getUrl()); ?>
                </li>

            <?php } ?>
            <li class="list-group-item">
                Категории
                <?php
                foreach ($model->categories as $c) {
                    $content[] = Html::a($c->name, $c->getUrl());
                }
                echo implode(', ', $content);
                ?>
            </li>

        </ul>

    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <?php
        $tabs = [];
        if (!empty($model->full_description)) {
            $tabs[] = [
                'label' => $model->getAttributeLabel('full_description'),
                'content' => $model->full_description,
                //   'active' => true,
                'options' => ['id' => 'description'],
            ];
        }
        if ($model->eavAttributes) {
            $tabs[] = [
                'label' => 'Характеристики',
                'content' => $this->render('tabs/_attributes', ['model' => $model]),
                'options' => ['id' => 'attributes'],
            ];
        }
        if ($model->relatedProducts) {
            $tabs[] = [
                'label' => 'Связи',
                'content' => $this->render('tabs/_related', ['model' => $model]),
                'options' => ['id' => 'related'],
            ];
        }
        if ($model->video) {
            $tabs[] = [
                'label' => 'Видео',
                'content' => $this->render('tabs/_video', ['model' => $model]),
                'options' => ['id' => 'videl'],
            ];
        }



        echo yii\bootstrap4\Tabs::widget(['items' => $tabs]);
        ?>
    </div>
</div>
