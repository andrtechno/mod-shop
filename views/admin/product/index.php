<?php

use panix\engine\grid\GridView;
use panix\engine\widgets\Pjax;



echo \panix\ext\fancybox\Fancybox::widget(['target' => '.image a']);

Pjax::begin([
    'timeout' => 50000,
    'id' => 'pjax-' . strtolower(basename($dataProvider->query->modelClass)),
   // 'linkSelector' => 'a:not(.linkTarget)'
]);
echo GridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => ['title' => $this->context->pageName],
    'showFooter' => true,
    //   'footerRowOptions' => ['class' => 'text-center'],
    'rowOptions' => ['class' => 'sortable-column']
]);
Pjax::end();


