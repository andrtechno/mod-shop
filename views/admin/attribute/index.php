<?php

use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

Pjax::begin([
    'id' => 'pjax-' . strtolower(basename($dataProvider->query->modelClass)),
    //'enablePushState' => false,
   // 'linkSelector' => 'a:not(.linkTarget)'
]);
echo GridView::widget([
    'id'=>'grid-attribute',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => ['title' => $this->context->pageName],
    'showFooter' => true,
    //   'footerRowOptions' => ['class' => 'text-center'],
    'rowOptions' => ['class' => 'sortable-column']
]);
Pjax::end();

