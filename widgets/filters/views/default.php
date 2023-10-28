<?php

use yii\helpers\Html;

?>
<div id="filters">
    <?php
    echo Html::beginForm($currentUrl, 'GET', ['id' => 'filter-form','data-category_id'=>(isset($model->id))?$model->id:'']);
    echo Html::hiddenInput('route',Yii::$app->controller->route);
    if(isset($model->id) && in_array(Yii::$app->controller->route,['shop/catalog/view','shop/brand/view']))
    echo Html::hiddenInput('param',$model->id);
    if(Yii::$app->request->get('q')){
        echo Html::hiddenInput('param',Yii::$app->request->get('q'));
    }
    ?>
    <div id="scroll-sidebar-outer">
        <div id="scroll-sidebar">
            <div id="ocfilter-button" class="d-none">
                <a href="#" class="btn btn-primary button-apply">Загрузка...</a>
            </div>
            <?php
            echo Html::beginTag('div', ['id' => 'ajax_filter_current']);
            if (!empty($active)) {
               // echo Yii::$app->controller->refreshUrl;
                $url = Yii::$app->controller->refreshUrl;
                echo $this->render(Yii::$app->getModule('shop')->filterViewCurrent, ['active' => $active, 'dataModel' => $this->context->model, 'url' => $url]);
            }
            echo Html::endTag('div');
            ?>

            <?php

            if ($this->context->priceView)
                echo $this->render($this->context->priceView, [
                    'priceMin' => $priceMin,
                    'priceMax' => $priceMax,
                    'currentPrice'=>$currentPrice,
                    //'currentPriceMin' => $currentPriceMin,
                    //'currentPriceMax' => $currentPriceMax,
                ]);

            if ($this->context->brandView && $brands)
                echo $this->render($this->context->brandView, ['brands' => $brands]);

            if ($this->context->attributeView)
                echo $this->render($this->context->attributeView, ['attributes' => $attributes]);

            ?>
        </div>
    </div>
    <div class="filter-buttons d-block d-lg-none tc dn">
        <?= Html::button(Yii::t('default','APPLY'), ['class' => 'pl-3 pr-3 btn button clear_filter_js', 'id' => 'filter-apply']); ?>
    </div>

    <?= Html::endForm(); ?>

</div>
