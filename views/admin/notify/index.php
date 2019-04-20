<?php

use yii\helpers\Html;
use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

echo \panix\ext\fancybox\Fancybox::widget(['target' => '.image a']);

Pjax::begin([
    'id' => 'pjax-container', 'enablePushState' => false,
]);
?>
<?=

GridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => ['title' => $this->context->pageName], //'{items}{pager}{summary}'
    'columns' => [
        [
            'attribute' => 'image',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function ($model) {
                return $model->product->renderGridImage('50x50');
            },
        ],
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a($model->product->name, $model->product->getUrl()); //$model->renderGridImage('50x50');
            },
        ],
        [
            'attribute' => 'email',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return Html::mailto($model->email, $model->email);
            },
        ],
        [
            'attribute' => 'product.availability',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                $class = '';
                if ($model->product->availability) {
                    $class = 'badge-success';
                } else {
                    $class = 'badge-danger';
                }
                return Html::tag('span', $model->product->availabilityItems[$model->product->availability], ['class' => 'badge ' . $class]); //$model->renderGridImage('50x50');
            },
        ],
        [
            'attribute' => 'product.quantity',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => 'product.quantity',
        ],
        [
            'attribute' => 'totalEmails',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            // 'attribute' => 'test',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-center', 'data-confirm' => Yii::t('cart/default', 'Вы уверены?')],
            'value' => function ($model) {
                return Html::a('Отправить письмо', ["/admin/shop/notify/send", "product_id" => $model->product_id], ['class' => 'btn btn-sm btn-primary']);
            },
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{update} {switch} {delete}',
        ]
    ]
]);
?>
<?php Pjax::end(); ?>