<?php

use yii\helpers\Html;
use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;
use panix\engine\components\Browser;
use panix\engine\CMS;

?>

<?php Pjax::begin(['dataProvider' => $dataProvider]); ?>
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
            'attribute' => 'query',
            'contentOptions' => ['class' => 'text-left'],
            'format' => 'html',
            'value' => function ($model) {
                return Html::a($model->query, ['/shop/search/index', 'q' => $model->query]);
            }
        ],
        [
            'attribute' => 'ip_create',
            'contentOptions' => ['class' => 'text-center'],
            'format' => 'html',
            'value' => function ($model) {
                return $model->ip_create;
            }
        ],
        [
            'attribute' => 'user_agent',
            'contentOptions' => ['class' => 'text-left'],
            'format' => 'html',
            'value' => function ($model) {

                $browser = new Browser($model->user_agent);

                $html = '<span class="badge badge-light">' . $browser->getPlatformIcon() . ' ' . $browser->getPlatform() . '</span>';
                $html .= $browser->getBrowser() . ' (v ' . $browser->getVersion() . ')';
                return $html;


            }
        ],
        [
            'attribute' => 'result',
            'contentOptions' => ['class' => 'text-center'],
            'format' => 'html',
            'value' => function ($model) {

                if ($model->result) {
                    $value = 'Успешный результат';
                    $class = 'success';
                } else {
                    $value = 'Нет результата';
                    $class = 'light';
                }
                $labelOptions['class'] = 'badge badge-' . $class;
                return Html::tag('span', $value, $labelOptions);
            }
        ],
        [
            'attribute' => 'user_id',
            'contentOptions' => ['class' => 'text-center'],
            'format' => 'html',
            'value' => function ($model) {
                if ($model->user_id) {
                    $value = $model->user->getDisplayName();
                    $class = 'secondary';
                } else {
                    $value = Yii::t('app/default', 'GUEST');
                    $class = 'light';
                }
                $labelOptions['class'] = 'badge badge-' . $class;
                return Html::tag('span', $value, $labelOptions);
            }
        ],
        [
            'attribute' => 'created_at',
            'class' => 'panix\engine\grid\columns\jui\DatepickerColumn',
        ]
    ],
]);
?>
<?php Pjax::end(); ?>

