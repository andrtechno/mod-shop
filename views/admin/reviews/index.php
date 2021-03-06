<?php

use panix\mod\shop\models\ProductReviews;
use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;
use panix\mod\shop\models\ProductType;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

Pjax::begin(['id' => 'pjax-grid-product-reviews']);
echo GridView::widget([
    'id' => 'grid-product-reviews',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'showFooter' => true,
    'layoutOptions' => ['title' => $this->context->pageName],
    'columns' => [
        [
            'attribute' => 'user_name',
            'format' => 'raw',
            'value' => function ($model) {
                /** @var ProductReviews $model */
                return $model->getDisplayName() . ' ' . $model->getGridStatusLabel();
            },
        ],
        ['attribute' => 'text'],
        /*[
            'attribute' => 'product_id',
            'format' => 'raw',
            'value' => function ($model) {
                $html = Html::a($model->product->name, $model->product->getUrl(), ['data-pjax' => 0, 'target' => '_blank']);

                return $html;
            }
        ],*/
        [
            'attribute' => 'rate',
            'filter' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5],
            'filterInputOptions' => ['class' => 'form-control', 'prompt' => html_entity_decode('&mdash;')],

            'format' => 'raw',
            'value' => function ($model) {
                return \panix\ext\rating\RatingInput::widget([
                    'model' => $model,
                    'attribute' => 'rate',
                    'options' => [
                        'readOnly' => true,
                        //'starOff' => $this->theme->asset[1] . '/img/star-off.svg',
                        //'starOn' => $this->theme->asset[1] . '/img/star-on.svg',

                    ]
                ]);
            }
        ],
        [
            'attribute' => 'created_at',
            'class' => 'panix\engine\grid\columns\jui\DatepickerColumn',
        ],
        [
            'attribute' => 'updated_at',
            'class' => 'panix\engine\grid\columns\jui\DatepickerColumn',
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{update} {delete}'
        ]
    ]
]);

Pjax::end();

