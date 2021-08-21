<?php

use yii\helpers\Html;
use panix\mod\shop\models\Product;

$cm = Yii::$app->currency;


//if (($minPrice && $maxPrice) && ($minPrice !== $maxPrice)) {
$getDefaultMin = floor($priceMin);
$getDefaultMax = ceil($priceMax);



$min = (int)floor($currentPriceMin); //$cm->convert()
$max = (int)ceil($currentPriceMax);
//echo $getDefaultMin;
//echo '<br>';
//echo $getDefaultMax;
$valueMax = ($max) ? $max : $getDefaultMax;
$valueMin = ($min) ? $min : $getDefaultMin;
if ($getDefaultMin != $getDefaultMax) {
    ?>

    <div class="card filter-block filter-price">
        <a class="card-header collapsed h5" data-toggle="collapse"
           href="#collapse-<?= md5('prices') ?>" aria-expanded="true"
           aria-controls="collapse-<?= md5('prices') ?>">
            <?= Yii::t('shop/default', 'FILTER_BY_PRICE') ?>
        </a>
        <div class="card-collapse collapse in" id="collapse-<?= md5('prices') ?>">
            <div class="card-body pb-3">
                <?php
                //echo Html::beginForm();

                ?>

                <?php echo \yii\jui\Slider::widget([
                    'id'=>'slider-price',
                    'clientOptions' => [
                        'range' => true,
                        // 'disabled' => $getDefaultMin === $getDefaultMax,
                        'min' => $getDefaultMin, //$prices['min'],//$min,
                        'max' => $getDefaultMax, //$prices['max'],//$max,
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
                            $("#mn").text("' . Yii::$app->currency->number_format($min) . '");
                            $("#mx").text("' . Yii::$app->currency->number_format($max) . '");
                        }'
                    ],
                ]);
                ?>
                <?php
                //echo Html::hiddenInput('slide[default_price][]', $getDefaultMin, ['id' => 'slide_default_price_min']);
                //echo Html::hiddenInput('slide[default_price][]', $getDefaultMax, ['id' => 'slide_default_price_max']);
                ?>
                <span class="min-max">
        от
                    <?php
                    echo Html::textInput('slide[price][]', $valueMin, ['id' => 'min_price', 'data-default' => $getDefaultMin, 'class' => '']);
                    ?>
                    до
                    <?php
                    echo Html::textInput('slide[price][]', $valueMax, ['id' => 'max_price', 'data-default' => $getDefaultMax, 'class' => '']);
                    ?>
                    <?= Yii::$app->currency->active['symbol'] ?></span>

                <?php //echo Html::submitButton('OK', ['class' => 'btn btn-sm btn-warning']);
                ?>
                <?php //echo Html::endForm();
                ?>
            </div>
        </div>
    </div>
<?php } ?>
