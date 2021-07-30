<?php

use yii\helpers\Html;

?>
<div id="filters">
    <?php
    echo Html::beginForm($currentUrl, 'GET', ['id' => 'filter-form','data-category_id'=>(isset($model->id))?$model->id:'']);
    ?>
    <div id="scroll-sidebar-outer">
        <div id="scroll-sidebar">
            <div id="ocfilter-button" class="d-none">
                <a href="#" class="btn btn-primary">Загрузка...</a>
            </div>
            <?php
            echo Html::beginTag('div', ['id' => 'ajax_filter_current']);
            if (!empty($active)) {
                $url = ($this->model) ? $this->model->getUrl() : ['/' . Yii::$app->requestedRoute];
                echo $this->render(Yii::$app->getModule('shop')->filterViewCurrent, ['active' => $active, 'dataModel' => $this->context->model, 'url' => $url]);
            }
            echo Html::endTag('div');
            ?>

            <?php

            if ($this->context->priceView)
                echo $this->render($this->context->priceView, [
                    'priceMin' => $priceMin,
                    'priceMax' => $priceMax,
                    'currentPriceMin' => $currentPriceMin,
                    'currentPriceMax' => $currentPriceMax,
                ]);

            if ($this->context->manufacturerView)
                echo $this->render($this->context->manufacturerView, ['manufacturers' => $manufacturers]);

            if ($this->context->attributeView)
                echo $this->render($this->context->attributeView, ['attributes' => $attributes]);

            ?>
        </div>
    </div>
    <div class="row2 no-gutters2 filter-buttons">
        <?php
        echo Html::a(Yii::t('shop/default', 'Сбросить'), $refreshUrl, ['class' => 'btn btn-outline-secondary pl-3 pr-3', 'id' => 'filter-reset'])
        ?>
        <?= Html::button('Применить', ['class' => ' col22 btn btn-danger pl-3 pr-3', 'id' => 'filter-apply']); ?>

        <?php // Html::button('Стросить', ['class' => 'btn d-block btn-outline-secondary pl-3 pr-3', 'id' => 'filter-reset']); ?>
    </div>

    <?= Html::endForm(); ?>

</div>
