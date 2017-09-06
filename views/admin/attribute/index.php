<?php
use yii\widgets\Pjax;
use panix\engine\grid\sortable\SortableGridView;

Pjax::begin([
    'id' => 'pjax-container', 'enablePushState' => false,
    'linkSelector' => 'a:not(.linkTarget)'
]);
echo SortableGridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => $this->render('@app/web/themes/admin/views/layouts/_grid_layout', ['title' => $this->context->pageName]), //'{items}{pager}{summary}'
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn',
            'contentOptions' => ['class' => 'text-center']
        ],

        'title',

        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{update} {switch} {delete}',
                ]
            ]
        ]);
        ?>
        <?php Pjax::end(); ?>

?>