<?php

use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

echo \panix\ext\fancybox\Fancybox::widget(['target' => '.image a']);
Pjax::begin(['dataProvider' => $dataProvider, 'id' => 'pjax-grid-brands']);

echo GridView::widget([
    'id' => 'grid-brands',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'showFooter' => true,
    'layoutOptions' => ['title' => $this->context->pageName]
]);

Pjax::end();

