<?php

use panix\mod\shop\models\AttributeOptionTranslate;
use yii\helpers\Html;
use yii\widgets\Pjax;

\panix\mod\shop\assets\admin\AttributeAsset::register($this);
?>

<style type="text/css">

    table.optionsEditTable input[type="text"] {
        width: 200px;
    }
    tr.copyMe {
        display: none;
    }

</style>
<div class="panel-body-static text-right">
    <a class="plusOne btn btn-success" style="color:#fff" href="javascript:void(0)">
        <i class="icon-add"></i> Добавить опцию
    </a>
</div>
<table>
    <tr class="copyMe">
       
<?php foreach (Yii::$app->languageManager->languages as $k => $l) { ?>
            <td>
                <input name="sample" type="text" class="value form-control input-lang" style="background-image:url(/uploads/language/<?= $k ?>.png">
            </td>
<?php } ?>
        <td class="text-center">
            <a href="javascript:void(0);" class="deleteRow btn btn-default"><i class="icon-delete"></i></a>
        </td>
    </tr>
</table>
<?php
$columns = array();
/* $columns[] = array(
  'class' => 'ext.sortable.SortableColumn',
  'url'=>'/admin/shop/attribute/sortableAttributes'
  ); */
$data = array();
$data2 = array();
$test = array();
foreach ($model->options as $k => $o) {
    $data2['primaryKey'] = $o->id;
    $data2['delete'] = '<a href="#" class="deleteRow btn btn-default"><i class="icon-delete"></i></a>';
    foreach (Yii::$app->languageManager->languages as $k => $l) {

        $otest = AttributeOptionTranslate::find()->where([
                    'object_id' => $o->id,
                    'language_id' => $l->code])
                ->one();

        $data2['name' . $k] = Html::textInput('options[' . $o->id . '][]', Html::encode($otest->value), array('class' => 'form-control input-lang', 'style' => 'background-image:url(/uploads/language/' . $k . '.png);'));
    }
    $data[] = (object) $data2;
}


foreach (Yii::$app->languageManager->languages as $k => $l) {
    $columns[] = array(
        'header' => $l->name,
        'attribute' => 'name' . $k,
        'format' => 'raw',
            //  'value' => '$data->name'
    );
    $sortAttributes[] = 'name' . $k;
}

$columns[] = array(
    'header' => Yii::t('app', 'OPTIONS'),
    'attribute' => 'delete',
    'format' => 'html',
    'contentOptions' => array('class' => 'text-center'),
    'filter' => 'adssad'
);


$data_db = new \yii\data\ArrayDataProvider([
    'allModels' => $data,
    'pagination' => false,
        ]);








Pjax::begin([
    'id' => 'pjax-container', 'enablePushState' => false,
    'linkSelector' => 'a:not(.linkTarget)'
]);
echo panix\engine\grid\GridView::widget([
    'tableOptions' => ['class' => 'table table-striped optionsEditTable'],
    'dataProvider' => $data_db,
    'columns' => $columns
]);
?>
<?php Pjax::end(); ?>
