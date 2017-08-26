<?php

use yii\helpers\Html;
//use app\cms\grid\AdminGridView;
use panix\engine\grid\sortable\SortableGridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\pages\models\PagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>




<?php // echo $this->render('_search', ['model' => $searchModel]);  ?>


<?php

Pjax::begin([
    'id' => 'pjax-container', 'enablePushState' => false,
]);
?>
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
            'attribute'=>'price',
            'format'=>'html',
            'contentOptions'=>['class'=>'text-center'],
            'value' => function($model){
                return $model::formatPrice($model->price).' '.Yii::$app->currency->main->symbol;
            }
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{view} {update} {switch} {delete}',
            'buttons' => [
                'view' => function ($url, $model, $key) {
                    return Html::a('<i class="icon-search"></i>', $model->getUrl(), [
                                'title' => Yii::t('yii', 'Delete'),
                                'target' => '_blank'
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

