
    <?php
    use yii\helpers\Html;
    use panix\mod\shop\models\ShopProduct;
    $cm = Yii::$app->currency;
    //$getMin = $this->controller->getMinPrice();
    //$getMax = $this->controller->getMaxPrice();

    $getMax2 = (int)$this->context->currentMaxPrice;
    $getMin2 = (int)$this->context->currentMinPrice;
    
    $getMax = $this->context->_maxprice;
    $getMin = $this->context->_minprice;
    

    $min = (int) floor($getMin2); //$cm->convert()
    $max = (int) ceil($getMax2);
    //  echo $cm->convert($getMin);
    ?>
<?php if (($getMin2 > 0 && $getMax2 > 0) && ($getMin2 != $getMax2)){ ?>
<div class="panel panel-default" id="filter-current">

    <div class="panel-heading">
        <div class="panel-title"><?= Yii::t('shop/default', 'FILTER_BY_PRICE') ?></div>
    </div>
    <div class="panel-body">
<?php
    echo Html::beginForm();
    echo Html::hiddenInput('min_price', (isset($_GET['min_price'])) ? $getMin : null, ['id'=>'min_price']);
    echo Html::hiddenInput('max_price', (isset($_GET['max_price'])) ? $getMax : null, ['id'=>'max_price']);
    echo \yii\jui\Slider::widget([
        'clientOptions' => [
            'range' => true,
           // 'disabled' => (int) $getMin === (int) $getMax,
            'min' => $min, //$prices['min'],//$min,
            'max' => $max, //$prices['max'],//$max,
            'values' => [$getMin2, $getMax2],

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
                                $("#mn").text("' . ShopProduct::formatPrice($min) . '");
				$("#mx").text("' . ShopProduct::formatPrice($max) . '");
                    }'
            ],
    ]);
    ?>
    <span class="min-max">
        Цена от  
        <span id="mn"><?= floor($this->context->getCurrentMinPrice()); ?></span>
        до   <span id="mx"><?= ceil($this->context->getCurrentMaxPrice()); ?></span>
        (<?= Yii::$app->currency->active->symbol ?>)</span>

    <?php
    echo Html::submitButton('OK', ['class'=>'btn btn-xs btn-danger']);
    ?>
    <?php
    echo Html::endForm();
    ?>
    </div>
</div>
<?php } ?>