<?php

use yii\helpers\Html;
use panix\engine\grid\GridView;
use panix\engine\widgets\Pjax;
?>


<?= \panix\ext\fancybox\Fancybox::widget(['target' => '.image a']); ?>

<?php //echo $this->render('_search', ['model' => $searchModel]);   ?>


<?php

Pjax::begin([
    'timeout' => 50000,
'id'=>  'pjax-'.strtolower(basename($dataProvider->query->modelClass)),
    'enablePushState' => true,
    'linkSelector' => 'a:not(.linkTarget)'
]);
?>
<?=

GridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => ['title' => $this->context->pageName],
    'rowOptions' => function ($model, $key, $index, $grid) {
return ['class' => 'sortable-column'];
},
    'columns' => [
        [
            'class' => \panix\engine\grid\sortable\Column::className(),
            'url' => ['/admin/shop/default/sortable']
        ],
        [
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function($model) {
        return $model->renderGridImage('50x50');
    },
        ],
        'name',
        [
            'attribute' => 'price',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
        return $model::formatPrice($model->price) . ' ' . Yii::$app->currency->main->symbol;
    }
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{view} {update} {switch} {delete}',
            'buttons' => [
                'view' => function ($url, $model, $key) {
                    return Html::a('<i class="icon-search"></i>', $model->getUrl(), [
                                'title' => Yii::t('yii', 'Delete'),
                                'target' => '_blank',
                                'class' => 'linkTarget'
                    ]);
                },
                    ],
                /* 'urlCreator' => function ($action, $model, $key, $index) {
                  if ($action === 'view') {
                  $url = $model->getUrl(); // your own url generation logic
                  return $url;
                  }
                  } */
                ]
            ]
        ]);
        ?>
        <?php Pjax::end(); ?>

