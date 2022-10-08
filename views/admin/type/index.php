<?php

use yii\widgets\Pjax;
use panix\engine\grid\GridView;

Pjax::begin([
    'id' => 'pjax-grid-producttype',
]);
echo GridView::widget([
    'id' => 'grid-producttype',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => ['title' => $this->context->pageName],
    'columns' => [
        /*[
            'class' => 'yii\grid\SerialColumn',
            'contentOptions' => ['class' => 'text-center']
        ],*/
        [
            'attribute' => 'id',
            'contentOptions' => ['class' => 'text-center'],
            'filterOptions' => ['style' => 'width:100px'],
        ],
        'name',
        [
            'attribute' => 'productsCount',
            'contentOptions' => ['class' => 'text-center'],
            'format' => 'raw',
            'value' => function ($model) {
                return \panix\engine\Html::a($model->productsCount, ['/admin/shop/product', 'ProductSearch[type_id]' => $model->id], ['target' => '_blank']);
            }
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{update} {delete}',
        ]
    ]
]);
Pjax::end();
