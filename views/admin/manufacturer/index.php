<?php

use yii\helpers\Html;
use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

Pjax::begin(['id'=>  'pjax-'.strtolower(basename($dataProvider->query->modelClass)),]);
echo GridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'rowOptions' => function ($model, $key, $index, $grid) {
        return ['class' => 'sortable-column'];
    },
    'layoutOptions' => ['title' => $this->context->pageName],
    'columns' => [
        [
            'class' => \panix\engine\grid\sortable\Column::className(),
            'url' => ['/admin/shop/default/sortable']
        ],
        [
            'class' => 'yii\grid\SerialColumn',
            'contentOptions' => ['class' => 'text-center']
        ],
        'name',
        ['class' => 'panix\engine\grid\columns\ActionColumn'],
    ],
]);

Pjax::end();


echo \yii\bootstrap\ButtonDropdown::widget([
    'label' => 'Action',
    'dropdown' => [
        'items' => [
            ['label' => 'DropdownA', 'url' => '/'],
            ['label' => 'DropdownB', 'url' => '#'],
        ],
    ],
]);
