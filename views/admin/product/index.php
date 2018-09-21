<?php
use panix\engine\grid\GridView;
use panix\engine\widgets\Pjax;
echo \panix\ext\fancybox\Fancybox::widget(['target' => '.image a']);
/*
use yii\jui\Dialog;
Dialog::begin([
    'clientOptions' => [
        'modal' => true,
    ],
]);

echo 'Dialog contents here...';

Dialog::end();
*/
Pjax::begin([
    'timeout' => 50000,
    'id' => 'pjax-' . strtolower(basename($dataProvider->query->modelClass)),
    'linkSelector' => 'a:not(.linkTarget)'
]);
echo GridView::widget([
    'id'=>'grid-product',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => [
        'title' => $this->context->pageName,
        'buttons'=>[
            [
                'url'=>['create'],
                'label'=>Yii::t('shop/admin', 'CREATE_PRODUCT'),
                'icon'=>'add'
            ]
        ]
    ],
    'showFooter' => true,
    //   'footerRowOptions' => ['class' => 'text-center'],
    'rowOptions' => ['class' => 'sortable-column']
]);
Pjax::end();


