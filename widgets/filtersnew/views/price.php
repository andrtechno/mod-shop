<?php
use yii\helpers\Html;
use panix\mod\shop\models\Product;

$cm = Yii::$app->currency;
$minPrice = Yii::$app->controller->getMinPrice();
$maxPrice = Yii::$app->controller->getMaxPrice();
var_dump($maxPrice);
echo '<br>';
var_dump($minPrice);
if (($minPrice && $maxPrice) && ($minPrice !== $maxPrice)) {
    $getDefaultMin = (int)floor($minPrice);
    $getDefaultMax = (int)ceil($maxPrice);
    $getMax = Yii::$app->controller->getCurrentMaxPrice();
    $getMin = Yii::$app->controller->getCurrentMinPrice();


    $min = (int)floor($getMin); //$cm->convert()
    $max = (int)ceil($getMax);

    ?>

    <div class="card filter filter-price">
        <a class="card-header collapsed h5" data-toggle="collapse"
           href="#collapse-<?= md5('prices') ?>" aria-expanded="true"
           aria-controls="collapse-<?= md5('prices') ?>">
            <?= Yii::t('shop/default', 'FILTER_BY_PRICE') ?>
        </a>
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
<?php } ?>