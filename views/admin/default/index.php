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
    'id' => 'pjax-container',
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
        'price',
        [
            'class' => 'panix\engine\grid\ActionColumn',
            'template' => '{update} {delete}',
            'buttons' => [
                /* 'delete' => function($url, $model) {
                  return Html::a('<i class="icon-delete"></i>', ['delete', 'id' => $model->id], [
                  'class' => 'btn btn-danger',
                  'data' => [
                  'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.',
                  'method' => 'post',
                  'pjax' => 0,
                  ],
                  ]);
                  } */
                'delete' => function ($url) {
                    return Html::a(Yii::t('yii', 'Delete'), '#', [
                                'title' => Yii::t('yii', 'Delete'),
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'onclick' => "
                                if (confirm('" . Yii::t('app', 'DELETE_COMFIRM') . "')) {
                                    $.ajax('$url', {
                                        type: 'POST'
                                    }).done(function(data) {
                                        $.pjax.reload({container: '#pjax-container'});
                                    });
                                }
                                return false;
                            ",
                    ]);
                },
                    ]
                ],
            ],
        ]);
        ?>
        <?php Pjax::end(); ?>

