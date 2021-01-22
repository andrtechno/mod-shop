<?php

use yii\widgets\Pjax;
use panix\engine\grid\GridView;

Pjax::begin([
    'id' => 'pjax-grid-producttype',
]);
echo GridView::widget([
    'id'=>'grid-producttype',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => ['title' => $this->context->pageName],
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn',
            'contentOptions' => ['class' => 'text-center']
        ],
        'name',
        'productsCount',
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{update} {delete}',
        ]
    ]
]);
Pjax::end();
