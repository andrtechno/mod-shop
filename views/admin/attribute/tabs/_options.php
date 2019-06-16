<?php

use panix\mod\shop\models\translate\AttributeOptionTranslate;
use panix\engine\Html;
use panix\engine\widgets\Pjax;
use panix\mod\shop\models\AttributeOption;

\panix\mod\shop\bundles\admin\AttributeAsset::register($this);
?>

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
                <input name="sample" type="text" class="value form-control input-lang" style="background-image:url(/uploads/language/<?= $k ?>.png" />
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
$columns[] = [
    'class' => 'panix\engine\grid\sortable\Column',
    'url' => ['/admin/shop/attribute/sortableOptions']
];
$data = [];
$data2 = [];
$test = [];
foreach ($model->options as $k => $o) {
    //echo print_r($o->translations);
    $data2['delete'] = '<a href="#" class="delete-option-attribute btn btn-sm btn-outline-danger"><i class="icon-delete"></i></a>';
    foreach (Yii::$app->languageManager->languages as $k => $l) {

        $otest = AttributeOptionTranslate::find()->where([
            'object_id' => $o->id,
            'language_id' => $l->id])
            ->one();


        /*$otest = AttributeOption::find()
            ->where([AttributeOption::tableName().'.id' => $o->id])
            ->translate($l->id)
            ->one();*/


        if ($otest) {
            $data2['name' . $k] = Html::textInput('options[' . $o->id . '][]', Html::encode($otest->value), ['class' => 'form-control input-lang', 'style' => 'background-image:url(/uploads/language/' . $k . '.png);']);
        }else{
            $data2['name' . $k] = Html::textInput('options[' . $o->id . '][]', '', ['class' => 'form-control input-lang', 'style' => 'background-image:url(/uploads/language/' . $k . '.png);']);
        }
        $data2['products'] = Html::a($o->productsCount, ['/admin/shop/product/index', 'ProductSearch[eav][' . $model->name . ']' => $o->id], ['target' => '_blank']);
        $data[$o->id] = (array) $data2;
    }
}


foreach (Yii::$app->languageManager->languages as $k => $l) {

    $columns[] = [
        'header' => $l->name,
        'attribute' => 'name' . $k,
        'format' => 'raw',
            //  'value' => '$data->name'
    ];
    $sortAttributes[] = 'name' . $k;
}
$columns[] = [
    'header' => Yii::t('shop/admin', 'PRODUCT_COUNT'),
    'attribute' => 'products',
    'format' => 'raw',
    'contentOptions' => ['class' => 'text-center'],
];
$columns[] = [
    'header' => Yii::t('app', 'OPTIONS'),
    'attribute' => 'delete',
    'format' => 'html',
    'contentOptions' => ['class' => 'text-center'],
    'filterOptions'=>['class' => 'text-center'],
    'filter' => '<a id="add-option-attribute" class="btn btn-sm btn-success" title="Добавить опцию" href="#"><i class="icon-add"></i></a>'
];


$data_array = new \yii\data\ArrayDataProvider([
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
    'dataProvider' => $data_array,
    'rowOptions' => ['class' => 'sortable-column'],
    'enableLayout'=>false,
    'layout'=>'{items}',
    'columns' => $columns,
    'filterModel'=>true
]);
Pjax::end();
?>
