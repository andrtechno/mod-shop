<?php

use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

Pjax::begin([
    'dataProvider' => $dataProvider,
]);
echo GridView::widget([
    'id'=>'grid-attribute',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => ['title' => $this->context->pageName],
    'showFooter' => true,
    //   'footerRowOptions' => ['class' => 'text-center'],
]);
Pjax::end();

