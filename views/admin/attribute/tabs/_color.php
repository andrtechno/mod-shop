<?php

use panix\mod\shop\models\translate\AttributeOptionTranslate;
use panix\engine\Html;
use panix\engine\widgets\Pjax;
use panix\ext\multipleinput\MultipleInput;

\panix\mod\shop\bundles\admin\AttributeAsset::register($this);

/**
 * @var $this \yii\web\View
 * @var $form \panix\engine\bootstrap\ActiveForm
 * @var $model \panix\mod\shop\models\Attribute
 */

$this->registerCss('
    table.optionsEditTable input[type="text"] {
        width: 200px;
    }

    tr.copyMe {
        display: none;
    }
');
/*
$post = Yii::$app->request->post('options');
$res = [];
foreach ($model->optionsArray as $o){
    $res[$o->id]=$o->id;
}
\panix\engine\CMS::dump($res);
echo '<br><br>';
\panix\engine\CMS::dump($post);
die;
*/
//print_r(array_diff(array_keys($post),array_keys($model->options)));
?>

<table>
    <tr class="copyMe">
        <td class="text-center">&mdash;</td>
        <?php foreach (Yii::$app->languageManager->languages as $k => $l) { ?>
            <td>
                <input name="sample" type="text" class="value form-control"/>
            </td>
        <?php } ?>
        <td class="text-center">
            <div class="alert alert-info">После сохранение будут доступны настройки цветов</div>
            <?php
            /*echo MultipleInput::widget([
                'value' => '',
                'name' => 'options[' . rand(2, 20) . '][data]',
                'min' => 1,
                'allowEmptyList' => false,
                'sortable' => true,
                'addButtonPosition' => MultipleInput::POS_ROW, // show add button in the header
                'columns' => [
                    [
                        'name' => 'color',
                        'type' => \panix\ext\colorpicker\ColorPicker::class,
                        'enableError' => false,
                        'options' => ['class' => 'tester'],
                        // 'title' => $model::t('COLOR'),
                        //'headerOptions' => [
                        //'style' => 'width: 250px;',
                        //],
                    ],

                ]
            ]);*/
            ?>

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
    foreach (Yii::$app->languageManager->languages as $k => $l) {

        $otest = AttributeOptionTranslate::find()->where([
            'object_id' => $o->id,
            'language_id' => $l->id])
            ->one();


        /*$otest = AttributeOption::find()
            ->where([AttributeOption::tableName().'.id' => $o->id])
            ->translate($l->id)
            ->one();*/

        /*$data2['data'] = \panix\ext\colorpicker\ColorPicker::widget([
                'name'=>'options[' . $o->id . '][]',
            'value'=>($o->data)?Html::decode($o->data):'',
        ]).' '.Html::a('add','#',['']);*/


        $data2['data'] = MultipleInput::widget([
            'value' => unserialize($o->data),
            'name' => 'options[' . $o->id . '][data]',
            'min' => 1,
            'max'=>5,
            'allowEmptyList' => false,
            //'enableGuessTitle' => true,
            'sortable' => true,
            'addButtonPosition' => MultipleInput::POS_ROW, // show add button in the header
            'columns' => [
                [
                    'name' => 'color',
                    'type' => \panix\ext\colorpicker\ColorPicker::class,

                    'options'=>[
                        'mode'=>'picker',
                       // 'selector'=>'.field-options-4-data-0-color'
                    ],
                    'enableError' => false,
                ],

            ]
        ]);


        if ($otest) {
            $data2['name' . $k] = Html::textInput('options[' . $o->id . '][]', Html::decode($otest->value), ['class' => 'form-control input-lang', 'style' => 'background-image:url(/uploads/language/' . $k . '.png);']);
        } else {
            $data2['name' . $k] = Html::textInput('options[' . $o->id . '][]', '', ['class' => 'form-control input-lang', 'style' => 'background-image:url(/uploads/language/' . $k . '.png);']);
        }
        $data2['products'] = Html::a($o->productsCount, ['/admin/shop/product/index', 'ProductSearch[eav][' . $model->name . ']' => $o->id], ['target' => '_blank']);
        $data[$o->id] = (array)$data2;
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
    'header' => Yii::t('shop/admin', 'data'),
    'attribute' => 'data',
    'format' => 'raw',
    'contentOptions' => ['class' => 'text-center'],
];
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


//echo \panix\ext\colorpicker\ColorPicker::widget(['name'=>'dssad','mode'=>'selector2','value'=>'#ccc']);
?>


