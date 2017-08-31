<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<h1><?= $model->name ?></h1>
<p><?= $model->full_description ?></p>

<?php
if ($prev = $model->getNextOrPrev('prev')) {
    echo Html::a('prev '.$prev->name, $prev->getUrl());
}
echo '<br>';
if ($next = $model->getNextOrPrev('next')) {
    echo Html::a($next->name.' next', $next->getUrl());
}
?>
<?php ?>
<?php

echo Html::beginForm(['/cart/add'], 'post', ['id'=>'form-add-cart-' . $model->id]);


                        echo Html::hiddenInput('product_id', $model->id);
                        echo Html::hiddenInput('product_price', $model->price);

echo Html::input('text','quantity', 1, array('class' => 'spinner text-center  btn-group')); 
 echo Html::a('<i class="icon-shopcart"></i>' . Yii::t('shop/default', 'BUY'), 'javascript:cart.add("#form-add-cart-' . $model->id . '")', array('class' => 'btn btn-primary'));
?>
<div class="price">
    <span><?= Yii::$app->currency->convert($model->price); ?></span>
    <sub><?= Yii::$app->currency->active->symbol; ?></sub>
</div>
<?php echo Html::endForm(); ?>