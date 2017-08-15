<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use panix\engine\grid\sortable\SortableGridView;
?>




<?php // echo $this->render('_search', ['model' => $searchModel]);   ?>


<?php Pjax::begin(); ?>
<?=

SortableGridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => $this->render('@app/web/themes/admin/views/layouts/_grid_layout', ['title' => $this->context->pageName]), //'{items}{pager}{summary}'
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn',
            'contentOptions' => ['class' => 'text-center']
        ],
        'name',
        [
            'attribute' => 'is_default',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
                return $model->is_default == 1 ? Yii::t('app','YES') : Yii::t('app','NO');
            }
        ],
        [
            'attribute' => 'is_main',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
                return $model->is_main == 1 ? Yii::t('app','YES') : Yii::t('app','NO');
            }
        ],
        [
            'attribute' => 'rate',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
                return $model->rate;
            }
        ],
        ['class' => 'panix\engine\grid\ActionColumn',
            'template' => '{update}{delete}',
            'buttons' => [
                "active" => function ($url, $model) {
                    if ($model->switch == 1)
                        $icon = "pause";
                    else
                        $icon = "play";

                    return Html::a('dsadas', $url, [
                                'title' => Yii::t('app', 'Toogle Active'),
                                'data-pjax' => '1',
                                'data-toggle-active' => $model->id
                    ]);
                },
                    ]
                ],
            // ['class' => 'panix\engine\grid\ActionColumn'],
            ],
        ]);
        ?>
        <?php Pjax::end(); ?>

