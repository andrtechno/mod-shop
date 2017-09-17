<?php

use yii\widgets\Pjax;
use panix\engine\grid\GridView;

Pjax::begin([
    'id' => 'pjax-container',
    //'enablePushState' => false,
   // 'linkSelector' => 'a:not(.linkTarget)'
]);
echo GridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => ['title' => $this->context->pageName],
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

