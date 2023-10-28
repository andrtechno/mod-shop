<?php

use yii\helpers\Html;
use panix\mod\shop\models\Product;

/**
 * @var $this \yii\web\View
 * @var $priceMin int
 * @var $priceMax int
 */
$cm = Yii::$app->currency;


$valueMin = (isset($currentPrice[0])) ? $currentPrice[0] : $priceMin;
$valueMax = (isset($currentPrice[1])) ? $currentPrice[1] : $priceMax;

if ($priceMin != $priceMax) {
    ?>

    <div class="col-12 col-md-3 widget">

        <h5 class="widget-title"><?= Yii::t('shop/default', 'FILTER_BY_PRICE') ?></h5>



        <div class="loke_scroll">
            <div class="filter-attribute filter-price" data-toggle="popover-price">
                <?php echo \yii\jui\Slider::widget([
                    'id'=>'slider-price',
                    'clientOptions' => [
                        'range' => true,
                        // 'disabled' => $getDefaultMin === $getDefaultMax,
                        'min' => $priceMin, //$prices['min'],//$min,
                        'max' => $priceMax, //$prices['max'],//$max,
                        'values' => [$valueMin, $valueMax],
                    ],
                    'clientEvents' => [

                        'slide' => 'function(event, ui) {
                                $("#min_price").val(ui.values[0]);
                                $("#max_price").val(ui.values[1]);
                                $("#mn").text(price_format(ui.values[0]));
                                $("#mx").text(price_format(ui.values[1]));
                            }',
                        'create' => 'function(event, ui){
                                $("#min_price").val(' . $valueMin . ');
                                $("#max_price").val(' . $valueMax . ');
                                $("#mn").text("' . Yii::$app->currency->number_format($priceMin) . '");
                                $("#mx").text("' . Yii::$app->currency->number_format($priceMax) . '");
                            }'
                    ],
                ]);
                ?>
                <div class="slider-values">
                    <?php
                    echo Html::textInput('slide[price][]', $valueMin, ['id' => 'min_price', 'data-default' => $priceMin, 'class' => 'input-price']);
                    ?>
                    <?php
                    echo Html::textInput('slide[price][]', $valueMax, ['id' => 'max_price', 'data-default' => $priceMax, 'class' => 'input-price']);
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
