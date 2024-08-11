<?php

use panix\mod\shop\models\translate\AttributeOptionTranslate;
use panix\engine\Html;
use panix\engine\widgets\Pjax;
use panix\mod\shop\models\AttributeOption;

\panix\mod\shop\bundles\admin\AttributeAsset::register($this);
/**
 * @var $this \yii\web\View
 * @var $model \panix\mod\shop\models\Attribute
 */

?>
<?php if ($model->sort) { ?>
    <div class="alert alert-warning">
        <?= $model::t('ALERT_ENABLE_SORT', $model::sortList()[$model->sort]); ?>
    </div>
<?php } ?>
<style type="text/css">
    table.optionsEditTable input[type="text"] {
        width: 200px;
    }

    tr.copyMe {
        display: none;
    }

</style>

<table>
    <tr class="copyMe">
        <td class="text-center">&mdash;</td>
        <?php foreach (Yii::$app->languageManager->languages as $k => $l) { ?>
            <td>
                <input name="sample" type="text" class="value form-control input-lang"
                       style="background-image:url(/uploads/language/<?= $k; ?>.png"/>
            </td>
        <?php } ?>
        <td class="text-center">&mdash;</td>
        <td class="text-center">
            <a href="#" class="delete-option-attribute btn btn-sm btn-default"><i class="icon-delete"></i></a>
        </td>
    </tr>
</table>
<?php


$columns = [];
if (!$model->sort) {
    $columns[] = [
        'class' => 'panix\engine\grid\sortable\Column',
        'url' => ['/admin/shop/attribute/sortableOptions']
    ];
}
$data = [];
$data2 = [];
$test = [];
foreach ($model->options as $k => $o) {
    //echo print_r($o->translations);
    $data2['delete'] = '<a href="#" class="delete-option-attribute btn btn-sm btn-outline-danger"><i class="icon-delete"></i></a>';
    //foreach (Yii::$app->languageManager->languages as $k => $l) {


    /*$otest = AttributeOption::find()
        ->where([AttributeOption::tableName().'.id' => $o->id])
        ->translate($l->id)
        ->one();*/
    foreach (Yii::$app->languageManager->languages as $k => $l) {
        $value = ($k == 'ru') ? 'value' : 'value_' . $l->code;
        $data2['name_' . $l->code] = Html::textInput('options[' . $o->id . '][]', $o->{$value}, ['class' => 'form-control input-lang', 'style' => 'background-image:url(/uploads/language/' . $k . '.png);']);
        $columns[$l->code] = [
            'header' => $l->name,
            'attribute' => 'name_' . $l->code,
            'format' => 'raw',
        ];
    }


    $data2['products'] = Html::a($o->productsCount, ['/admin/shop/product/index', 'ProductSearch[eav][' . $model->name . ']' => $o->id], ['target' => '_blank','data-pjax'=>0]);
    $data[$o->id] = (array)$data2;
    // }


}

$sortAttributes[] = 'name';

$columns[] = [
    'header' => Yii::t('shop/admin', 'PRODUCT_COUNT'),
    'attribute' => 'products',
    'format' => 'raw',
    'contentOptions' => ['class' => 'text-center'],
];
$columns[] = [
    'header' => Yii::t('app/default', 'OPTIONS'),
    'attribute' => 'delete',
    'format' => 'html',
    'contentOptions' => ['class' => 'text-center'],
    'filterOptions' => ['class' => 'text-center'],
    'filter' => Html::a(Html::icon('add'), '#', ['title' => 'Добавить опцию', 'class' => 'btn btn-sm btn-success', 'id' => 'add-option-attribute'])
];


$dataProvider = new \yii\data\ArrayDataProvider([
    'allModels' => $data,
    'pagination' => false,
]);


Pjax::begin([
    'id' => 'pjax-container',
    //'enablePushState' => false,
    //  'linkSelector' => 'a:not(.linkTarget)'
]);
echo panix\engine\grid\GridView::widget([
    'tableOptions' => ['class' => 'table table-striped optionsEditTable'],
    'dataProvider' => $dataProvider,
    'rowOptions' => ['class' => 'sortable-column'],
    'enableLayout' => false,
    'layout' => '{items}',
    'columns' => $columns,
    'filterModel' => true
]);
Pjax::end();



?>
<button type="button" class="btn btn-primary d-none" data-toggle="modal" data-target="#exampleModal">
    test
</button>


<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php $formOptions = \yii\widgets\ActiveForm::begin(['id'=>'form-options']); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $formOptions->field($modelOptions, 'name_uk') ?>
                <?= $formOptions->field($modelOptions, 'name_ru') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <?= Html::submitButton('save', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php \yii\widgets\ActiveForm::end(); ?>
        </div>
    </div>
</div>
