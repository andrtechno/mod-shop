<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php
$this->registerJs("cart.spinnerRecount = false;", yii\web\View::POS_BEGIN,'cart');
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


 echo Html::a('<i class="icon-shopcart"></i>' . Yii::t('shop/default', 'BUY'), 'javascript:cart.add("#form-add-cart-' . $model->id . '")', array('class' => 'btn btn-primary'));
?>
<?php
echo yii\jui\Spinner::widget([
    'name'  => "quantity",
    'value'=>1,
    'clientOptions' => ['max' => 999],
    'options'=>['class'=>'cart-spinner']
]);
?>
<div class="price">
    <span><?= Yii::$app->currency->convert($model->price); ?></span>
    <sub><?= Yii::$app->currency->active->symbol; ?></sub>
</div>
<?php echo Html::endForm(); ?>