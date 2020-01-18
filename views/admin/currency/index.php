<?php

use yii\helpers\Html;
use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

?>


<?php // echo $this->render('_search', ['model' => $searchModel]);   ?>


<?php Pjax::begin(['dataProvider'=>$dataProvider]); ?>
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
            'class' => 'panix\engine\grid\sortable\Column',
            'url' => ['/admin/shop/currency/sortable']
        ],
        'name',
        [
            'class' => 'panix\engine\grid\columns\BooleanColumn',
            'attribute' => 'is_default',
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'class' => 'panix\engine\grid\columns\BooleanColumn',
            'attribute' => 'is_main',
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'attribute' => 'rate',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return $model->rate;
            }
        ],
        ['class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{update}{delete}',
            'buttons' => [
                "active" => function ($url, $model) {
                    if ($model->switch == 1)
                        $icon = "pause";
                    else
                        $icon = "play";

                    return Html::a('dsadas', $url, [
                        'title' => Yii::t('app/default', 'Toogle Active'),
                        'data-pjax' => '1',
                        'data-toggle-active' => $model->id
                    ]);
                },
            ]
        ],
        // ['class' => 'panix\engine\grid\columns\ActionColumn'],
    ],
]);
?>
<?php Pjax::end(); ?>

