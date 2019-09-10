<?php
use yii\helpers\Html;
use panix\mod\shop\models\Product;

$cm = Yii::$app->currency;


if (Yii::$app->controller->getMinPrice() && Yii::$app->controller->getMaxPrice()) {
    $getDefaultMin = (int)floor(Yii::$app->controller->getMinPrice());
    $getDefaultMax = (int)ceil(Yii::$app->controller->getMaxPrice());
    $getMax = Yii::$app->controller->getCurrentMaxPrice();
    $getMin = Yii::$app->controller->getCurrentMinPrice();


    $min = (int)floor($getMin); //$cm->convert()
    $max = (int)ceil($getMax);
    echo $min;
    ?>
    <?php if (true) { ?>
        <div class="card filter filter-price">
            <div class="card-header collapsed" data-toggle="collapse"
                 data-target="#collapse-<?= md5('prices') ?>" aria-expanded="true"
                 aria-controls="collapse-<?= md5('prices') ?>">
                <h5><?= Yii::t('shop/default', 'FILTER_BY_PRICE') ?></h5>
            </div>
            <div class="card-collapse collapse in" id="collapse-<?= md5('prices') ?>">
                <div class="card-body">
                    <?php
                    //echo Html::beginForm(); ?>
                    <div class="row">
                        <div class="col-6">
                            <?php
                            echo Html::textInput('min_price', (isset($_GET['min_price'])) ? $getMin : null, ['id' => 'min_price', 'class' => '']);
                            ?>
                        </div>
                        <div class="col-6">
                            <?php
                            echo Html::textInput('max_price', (isset($_GET['max_price'])) ? $getMax : null, ['id' => 'max_price', 'class' => '']);
                            ?>
                        </div>
                    </div>
                    <?php echo \yii\jui\Slider::widget([
                        'clientOptions' => [
                            'range' => true,
                            // 'disabled' => $getDefaultMin === $getDefaultMax,
                            'min' => $getDefaultMin, //$prices['min'],//$min,
                            'max' => $getDefaultMax, //$prices['max'],//$max,
                            'values' => [$getMin, $getMax],

                        ],
                        'clientEvents' => [
                            'slide' => 'function(event, ui) {
                        $("#min_price").val(ui.values[0]);
                        $("#max_price").val(ui.values[1]);
                        $("#mn").text(price_format(ui.values[0]));
                        $("#mx").text(price_format(ui.values[1]));
			        }',
                            'create' => 'function(event, ui){
                        $("#min_price").val(' . $min . ');
                        $("#max_price").val(' . $max . ');
                        $("#mn").text("' . Yii::$app->currency->number_format($min) . '");
                        $("#mx").text("' . Yii::$app->currency->number_format($max) . '");
                    }'
                        ],
                    ]);
                    ?>
                    <span class="min-max">
        Цена от
        <span id="mn" class="price price-sm"><?php echo Yii::$app->currency->number_format($getMin); ?></span>
        до   <span id="mx" class="price price-sm"><?php echo Yii::$app->currency->number_format($getMax); ?></span>
        (<?= Yii::$app->currency->active['symbol'] ?>)</span>

                    <?php //echo Html::submitButton('OK', ['class' => 'btn btn-sm btn-warning']); ?>
                    <?php //echo Html::endForm(); ?>
                </div>
            </div>
        </div>
    <?php }
} ?>