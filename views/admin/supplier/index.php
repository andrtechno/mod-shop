<?php

use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

Pjax::begin(['dataProvider' => $dataProvider, 'id' => 'pjax-grid-suppliers']);
echo GridView::widget([
    'id' => 'grid-suppliers',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'showFooter' => true,
    'layoutOptions' => ['title' => $this->context->pageName]
]);

Pjax::end();
