<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php
$this->registerJs("cart.spinnerRecount = false;", yii\web\View::POS_BEGIN, 'cart');
?>



<?php
foreach($model->categories as $cat){
    echo $cat->name;
}
?>



<div class="col-sm-12">
    <div class="row">
        <div class="col-sm-6">
            <?= Html::img($model->getMainImageUrl('500x500')); ?>
        </div>
        <div class="col-sm-6">
            <div class="btn-group">
                <?php
                if ($prev = $model->getNextOrPrev('prev')) {
                    echo Html::a('prev ' . $prev->name, $prev->getUrl(), ['class' => 'btn btn-default']);
                }
                if ($next = $model->getNextOrPrev('next')) {
                    echo Html::a($next->name . ' next', $next->getUrl(), ['class' => 'btn btn-default']);
                }
                ?>
            </div>
            <h1><?= $model->name ?></h1>
            
            <?php if($model->manufacturer_id){ ?>
             <?= Html::a($model->manufacturer->name,$model->manufacturer->getUrl()); ?>
            <?php } ?>
           
            
            <div class="price">
                <span><?= Yii::$app->currency->convert($model->price); ?></span>
                <sub><?= Yii::$app->currency->active->symbol; ?></sub>
      </div>
                <?php
                echo Html::beginForm(['/cart/add'], 'post', ['id' => 'form-add-cart-' . $model->id]);


                echo Html::hiddenInput('product_id', $model->id);
                echo Html::hiddenInput('product_price', $model->price);


                echo Html::a('<i class="icon-shopcart"></i>' . Yii::t('cart/default', 'BUY'), 'javascript:cart.add("#form-add-cart-' . $model->id . '")', array('class' => 'btn btn-primary'));
                ?>
                <?php
                echo yii\jui\Spinner::widget([
                    'name' => "quantity",
                    'value' => 1,
                    'clientOptions' => ['max' => 999],
                    'options' => ['class' => 'cart-spinner']
                ]);
                ?>

                <?php echo Html::endForm(); ?>
      

        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
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
                    'options' => ['id' => 'videl'],
                ];
            }



            echo yii\bootstrap\Tabs::widget(['items' => $tabs]);
            ?>
        </div>
    </div>
</div>



