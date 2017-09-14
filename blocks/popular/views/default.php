<?php

use yii\helpers\Html;
use panix\engine\grid\GridView;
use yii\widgets\Pjax;
?>


<?= \panix\ext\fancybox\Fancybox::widget(['target' => '.image a']); ?>



<?php



echo GridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'layoutOptions' => ['title' => 'Popular'],
    'columns' => [
        [
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function($model) {
        return $model->renderGridImage('50x50');
    },
        ],
        'name',
        [
            'attribute' => 'views',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'attribute' => 'added_to_cart_count',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-center'],
        ],
    ]
]);

?>
