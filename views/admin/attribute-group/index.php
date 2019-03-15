<?php

use yii\widgets\Pjax;
use panix\engine\grid\GridView;

?>


<?php //echo $this->render('_search', ['model' => $searchModel]);   ?>


<?php Pjax::begin(); ?>
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
            'class' => \panix\engine\grid\sortable\Column::class,
            'url' => ['/shop/attribute-group/sortable']
        ],
        'name',
        ['class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{switch}{update}{delete}',
        ],
    ],
]);
?>
<?php Pjax::end(); ?>

