<?php
use yii\helpers\Html;
use panix\mod\shop\models\Product;

$cm = Yii::$app->currency;



$getDefaultMin = (int)floor(Yii::$app->controller->getMinPrice());
$getDefaultMax = (int)ceil(Yii::$app->controller->getMaxPrice());
$getMax = Yii::$app->controller->currentMaxPrice;
$getMin = Yii::$app->controller->currentMinPrice;




$min = (int)floor($getMin); //$cm->convert()
$max = (int)ceil($getMax);

?>
<?php if (true) { ?>
    <div class="card bg-light filter filter-price">

        <div class="card-header">
            <h5><?= Yii::t('shop/default', 'FILTER_BY_PRICE') ?></h5>
        </div>
        <div class="card-body">
            <?php
            echo Html::beginForm();
            echo Html::hiddenInput('min_price', (isset($_GET['min_price'])) ? $getMin : null, ['id' => 'min_price']);
            echo Html::hiddenInput('max_price', (isset($_GET['max_price'])) ? $getMax : null, ['id' => 'max_price']);
            echo \yii\jui\Slider::widget([
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
        <span id="mn"><?php echo Yii::$app->currency->number_format($getMin); ?></span>
        до   <span id="mx"><?php echo Yii::$app->currency->number_format($getMax); ?></span>
        (<?= Yii::$app->currency->active->symbol ?>)</span>

            <?php echo Html::submitButton('OK', ['class' => 'btn btn-xs btn-danger']); ?>
            <?php echo Html::endForm(); ?>
        </div>
    </div>
<?php } ?>