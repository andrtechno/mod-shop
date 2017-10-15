<?php

use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

Pjax::begin(['id' => 'pjax-' . strtolower(basename($dataProvider->query->modelClass))]);
echo GridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'rowOptions' => ['class' => 'sortable-column'],
    'showFooter' => true,
    'layoutOptions' => ['title' => $this->context->pageName]
]);

Pjax::end();

