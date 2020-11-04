<?php


use panix\engine\Html;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Attribute;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\search\ProductSearch;
use panix\engine\grid\GridView;
use panix\engine\data\ActiveDataProvider;
use panix\engine\widgets\Pjax;
//use yii\widgets\Pjax;
use panix\mod\shop\models\search\ProductConfigureSearch;

/**
 * @var Controller $this
 * @var Product $product Current product
 */

\panix\mod\shop\bundles\admin\ConfigurationsAsset::register($this);


// For grid view we use new products instance
//$model = Product::find();

$columns[] = [
    'class' => 'panix\engine\grid\columns\CheckboxColumn',
    'enableMenu' => false,
    'name' => 'ConfigurationsProduct',
    // 'value'=>5,
    'checkboxOptions' => function ($model, $key, $index, $column) use ($product) {
        return [
            'value' => $model->id,
            'checked' => (!empty($product->configurations) && !$product->isNewRecord) ? in_array($key,$product->configurations) : false
        ];
    },
];
$columns[] = [
    'attribute' => 'id',
    'format' => 'text',
];
$columns[] = [
    'attribute' => 'name',
    'format' => 'raw',
    'value' => function ($model) {
        return Html::a(Html::encode($model->name), ["update", "id" => $model->id], ["target" => "_blank"]);
    },
];
$columns[] = [
    'attribute' => 'sku',
];
$columns[] = [
    'attribute' => 'price',
    'format' => 'raw',
    //'value' => '$data->price',
];


// Process attributes
$eavAttributes = [];
$attributeModels = Attribute::find();

if (isset(Yii::$app->request->get('Product')['configurable_attributes'])) {
    $attribute_id = Yii::$app->request->get('Product')['configurable_attributes'];
} else {
    $attribute_id = $product->configurable_attributes;
}
$attributeModels = $attributeModels->where([Attribute::tableName() . '.id' => $attribute_id])->all();

foreach ($attributeModels as $attribute) {
    $selected = null;

    if (isset($_GET['eav'][$attribute->name]) && !empty($_GET['eav'][$attribute->name])) {
        $eavAttributes[$attribute->name] = $_GET['eav'][$attribute->name];
        $selected = $_GET['eav'][$attribute->name];
    } else
        array_push($eavAttributes, $attribute->name);

    if ($attribute->title && $attribute->name) {
        $columns[] = [
            'attribute' => 'eav_' . $attribute->name . '.value',
            'header' => $attribute->title,
            'contentOptions' => ['class' => 'eav'],

            'filter' => Html::dropDownList('eav[' . $attribute->name . ']', $selected, ArrayHelper::map($attribute->options, 'id', 'value'), [
                'prompt' => html_entity_decode($product::t('SELECT_ATTRIBUTE')),
                'class' => 'custom-select w-auto'
            ])
        ];
    }
}

$searchModel = new ProductConfigureSearch();
$searchModel->exclude[] = $product->id;
$searchModel->eavAttributes = $eavAttributes;

$configure = [];


$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), ['attribute_id' => $attribute_id]);
Pjax::begin([
    'id' => 'pjax-ConfigurationsProductGrid',
    'enablePushState' => false,
    'timeout' => false
]);
echo GridView::widget([
    'id' => 'ConfigurationsProductGrid',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'filterUrl' => [
        'apply-configurations-filter',
        'product_id' => $product->id,
        'configurable_attributes' => isset($_GET['configurable_attributes']) ? $_GET['configurable_attributes'] : $product->configurable_attributes
    ],
    'enableLayout' => false,
    'columns' => $columns,
    'showFooter' => false,
    'enableColumns' => false,

]);
Pjax::end();


$this->registerJs('initConfigurationsTable();
$(document).on("beforeFilter", "#ConfigurationsProductGrid" , function(event,k) {
    var data = $(this).yiiGridView("data");
    $.pjax({
        url: data.settings.filterUrl,
        container: \'#pjax-ConfigurationsProductGrid\',
        type:"GET",
        push:false,
        timeout:false,
        scrollTo:false,
        data:$(this).closest("form").serialize()
    });
    return false;
});
');
?>
