<?php

echo \yii\widgets\ListView::widget([
    //'id'=>'list-product',
    'dataProvider' => $provider,
    'itemView' => $itemView,
    //'layout' => '{sorter}{summary}{items}{pager}',
    'layout' => '{items}{pager}',
    'emptyText' => 'Empty',
    'options' => ['class' => 'row list-view ' . $itemView],
    'itemOptions' => ['class' => 'item ' . (($itemView == '_view_grid') ? 'col-md-4' : 'col-md-12')],
    'sorter' => [
        //'class' => \yii\widgets\LinkSorter::class,
        'attributes' => ['price', 'sku']
    ],
    /*'pager' => [
        'class' => \panix\wgt\scrollpager\ScrollPager::class,
        'triggerTemplate' => '<div class="ias-trigger" style="text-align: center; cursor: pointer;width: 100%;">{text}</div>',
        'spinnerTemplate' => '<div class="ias-spinner" style="text-align: center;width: 100%;"><img src="{src}" alt="" /></div>',
        'spinnerSrc' => $this->context->assetUrl . '/images/ajax.gif'

    ],*/
    'pager' => [
        'class' => \panix\wgt\scrollpager\ScrollPager::class,
        'triggerOffset' => 1,
        'linkPagerOptions' => ['class' => 'pagination'],
        'historyPrevText' => '',
        'triggerText' => Yii::t('default', 'LOAD_MORE'),
        'noneLeftTemplate' => '<div class="ias-noneleft item col-6 col-sm-6 col-md-6 col-lg-4"><div class="product-item">{text}</div></div>',
        'triggerTemplate' => '<div class="ias-trigger col-12"><div>{text}</div></div>',
        'spinnerTemplate' => '<div class="ias-trigger ias-spinner col-12"><div>Loading...</div></div>',
        'eventOnNext' => new \yii\web\JsExpression("function(pageIndex){
            $('.pagination li.page-item').removeClass('active');
            var parse = pageIndex.match(/page\/(\d+)/);
            var page = parseInt(parse[1]);

            //v1
            //var item = $('.pagination .page-item:not(.next,.last,.prev,.first) a[data-page=\"'+page+'\"]');
            //item.closest('li').addClass('active');

            //v2
            $('.pagination #page-item-'+page).addClass('active');
        }"),
        //'historyPrevTemplate'=>'',
        //'historyPrevTemplate'=>'<div class="ias-trigger col-12 ias-trigger-prev" style="text-align: center; cursor: pointer;">{text}</div>'
    ],
    'emptyTextOptions' => ['class' => 'alert alert-info']
]);
?>
