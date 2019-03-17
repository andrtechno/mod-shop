<?php
echo \yii\widgets\ListView::widget([
    //'id'=>'list-product',
    'dataProvider' => $provider,
    'itemView' => $itemView,
    //'layout' => '{sorter}{summary}{items}{pager}',
    'layout' => '{items}{pager}',
    'emptyText' => 'Empty',
    'options' => ['class' => 'row list-view '.$itemView],
    'itemOptions' => ['class' => 'item'],
    'sorter' => [
        //'class' => \yii\widgets\LinkSorter::className(),
        'attributes'=>['price','sku']
    ],
    'pager' => [
        'class' => \panix\wgt\scrollpager\ScrollPager::class,
        'triggerTemplate' => '<div class="ias-trigger" style="text-align: center; cursor: pointer;"><a href="#">{text}</a></div>'
    ],
    'emptyTextOptions' => ['class' => 'alert alert-info']
]);
?>