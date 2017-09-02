<?php

use yii\helpers\Url;
use panix\mod\shop\widgets\categories\CategoriesWidget;
use yii\helpers\Html;
use panix\mod\shop\models\ShopProduct;
?>



<div class="col-sm-3">
    <?= CategoriesWidget::widget([]) ?>
    <?php print_r($_GET);
    ?>
    <?php
    $cm = Yii::$app->currency;
    //$getMin = $this->controller->getMinPrice();
    //$getMax = $this->controller->getMaxPrice();
    $getMax2 = (int)$this->context->currentMaxPrice;
    $getMin2 = (int)$this->context->currentMinPrice;
    
    $getMax = $this->context->maxprice;
    $getMin = $this->context->minprice;
    

    $min = $getMin2;//(int) floor($getMin); //$cm->convert()
    $max = $getMax2;//(int) ceil($getMax);
    //  echo $cm->convert($getMin);
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
        <span id="mn"><?= $this->context->getCurrentMinPrice(); ?></span>
        до   <span id="mx"><?= $this->context->getCurrentMaxPrice(); ?></span>
        (<?= Yii::$app->currency->active->symbol ?>)</span>

    <?php
    echo Html::submitButton('OK', ['class'=>'btn btn-xs btn-danger']);
    ?>
    <?php
    echo Html::endForm();
    ?>
</div>   
<div class="col-sm-9">
    <?= Html::a('back', Url::previous()); ?>
    <h1><?= $this->context->model->name ?></h1>
    <?php
    echo \yii\widgets\ListView::widget([
        'dataProvider' => $provider,
        'itemView' => $itemView,
        'layout' => '{summary}{items}{pager}',
        'emptyText' => 'Empty',
        'options' => ['class' => 'row'],
        'itemOptions' => ['class' => 'col-sm-4'],
        'emptyTextOptions' => ['class' => 'alert alert-info']
    ]);
    ?>
</div>   