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

        <td>
            <input name="sample" type="text" class="value form-control input-lang"
                   style="background-image:url(/uploads/language/ru.png"/>
        </td>
        <td>
            <input name="sample" type="text" class="value form-control input-lang"
                   style="background-image:url(/uploads/language/ua.png"/>
        </td>

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
    //foreach (Yii::$app->languageManager->languages as $k => $l) {



        /*$otest = AttributeOption::find()
            ->where([AttributeOption::tableName().'.id' => $o->id])
            ->translate($l->id)
            ->one();*/



    $data2['name'] = Html::textInput('options[' . $o->id . '][]', $o->value, ['class' => 'form-control input-lang', 'style' => 'background-image:url(/uploads/language/ru.png);']);
    $data2['name_ua'] = Html::textInput('options[' . $o->id . '][]', $o->value_ua, ['class' => 'form-control input-lang', 'style' => 'background-image:url(/uploads/language/ua.png);']);

        $data2['products'] = Html::a($o->productsCount, ['/admin/shop/product/index', 'ProductSearch[eav][' . $model->name . ']' => $o->id], ['target' => '_blank']);
        $data[$o->id] = (array)$data2;
   // }
}




$columns[] = [
    'header' => 'ru',
    'attribute' => 'name',
    'format' => 'raw',
];
$columns[] = [
    'header' => 'ua',
    'attribute' => 'name_ua',
    'format' => 'raw',
];
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
    'enableLayout' => false,
    'layout' => '{items}',
    'columns' => $columns,
    'filterModel' => true
]);
Pjax::end();
?>
