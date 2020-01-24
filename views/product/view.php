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

", yii\web\View::POS_END);

echo \yii\helpers\Inflector::titleize('CamelCase');

echo \yii\helpers\Inflector::ordinalize(15);

$words = ['Spain', 'France', 'Украина'];
echo \yii\helpers\Inflector::sentence($words);
?>

<div id="info"></div>
<div class="row">
    <div class="col-sm-6 col-xs-12">
        <?= Html::img($model->getMainImage('500x500')->url); ?>

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

            /**
             * @var $prev \panix\mod\shop\models\Product
             * @var $next \panix\mod\shop\models\Product
             */
            if ($prev = $model->getPrev(['switch'=>1, 'main_category_id' => $model->main_category_id])->one()) {
                echo Html::a(Html::icon('arrow-left'), $prev->getUrl(), ['title' => $prev->name]);
            }
            if ($next = $model->getNext(['switch'=>1, 'main_category_id' => $model->main_category_id])->one()) {
                echo Html::a(Html::icon('arrow-right'), $next->getUrl(), ['title' => $next->name]);
            }
            ?>
        </div>
        <h1><?= $model->name ?></h1>


        <span class="price">
            <span id="product_price"><?= Yii::$app->currency->number_format($model->getFrontPrice()); ?></span>
            <sup><?= Yii::$app->currency->active['symbol']; ?></sup>
        </span>
        <?= $model->beginCartForm(); ?>

        <?php
        echo $this->render('_configurations', ['model' => $model]);
        ?>
        <?php if ($model->appliedDiscount) { ?>
            <span class="price price-strike">
                <span><?= Yii::$app->currency->number_format(Yii::$app->currency->convert($model->discountPrice)) ?></span>
                <sup><?= Yii::$app->currency->active['symbol'] ?></sup>
            </span>
        <?php } ?>
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
                    <?= $model->getAttributeLabel('manufacturer_id'); ?>
                    : <?= Html::a($model->manufacturer->name, $model->manufacturer->getUrl()); ?>
                </li>

            <?php } ?>
            <li class="list-group-item">
                Категории
                <?php
                //foreach ($model->categories as $c) {
                //    $content[] = Html::a($c->name, $c->getUrl());
               // }
               // echo implode(', ', $content);
                ?>
            </li>

        </ul>

    </div>
</div>
<div class="row">
    <div class="col">
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
                'label' =>Yii::t('shop/default', 'SPECIFICATION'),
                'content' => $this->render('tabs/_attributes', ['model' => $model]),
                'options' => ['id' => 'attributes'],
            ];
        }
        if (Yii::$app->hasModule('comments')) {
        $tabs[] = [
            'label' => Yii::t('app/default', 'REVIEWS', ['n' => $model->commentsCount]),
            'content' => $this->render('tabs/_comments', ['model' => $model]),
            'options' => ['id' => 'comments'],
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
                'options' => ['id' => 'video'],
            ];
        }


        echo yii\bootstrap4\Tabs::widget(['items' => $tabs]);
        ?>
    </div>
</div>
