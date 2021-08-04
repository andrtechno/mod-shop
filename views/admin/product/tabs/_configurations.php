<?php


use panix\engine\Html;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\traits\ProductTrait;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\search\ProductSearch;
use panix\engine\grid\GridView;
use panix\engine\data\ActiveDataProvider;
use panix\engine\widgets\Pjax;

//use yii\widgets\Pjax;
use panix\mod\shop\models\search\ProductConfigureSearch;

/**
 * @var \yii\web\View $this
 * @var Product $product Current product
 */

\panix\mod\shop\bundles\admin\ConfigurationsAsset::register($this);


// For grid view we use new products instance
//$model = Product::find();

$js = <<<JS


$(document).on('click','#ConfigurationsProductGrid input[type=\"checkbox\"]',function(){

    $.ajax({
        url:'/admin/shop/product/configurations?id='+$(this).val(),
        dataType:'json',
        type:'POST',
        data:{
            product_id:{$product->id},
            action:$(this).is(':checked')?1:0
        },
        success:function(response){
            common.notify(response.message,'success');
        }
    });

});
JS;

$this->registerJs($js);
$columns[] = [
    'class' => 'panix\engine\grid\columns\CheckboxColumn',
    'enableMenu' => false,
    'name' => 'ConfigurationsProduct',
    // 'value'=>5,
    'checkboxOptions' => function ($model, $key, $index, $column) use ($product) {
        return [
            'value' => $model->id,
            'checked' => (!empty($product->configurations) && !$product->isNewRecord) ? in_array($key, $product->configurations) : false
        ];
    },
];
$columns[] = [
    'attribute' => 'id',
    'format' => 'text',
    'contentOptions' => ['class' => 'text-center']
];
$columns[] = [
    'attribute' => 'name',
    'format' => 'raw',
    'value' => function ($model) {
        return Html::a(Html::encode($model->name), ["update", "id" => $model->id], ["target" => "_blank"]);
    },
];
$columns[] = [
    'attribute' => 'brand_id',
    'contentOptions' => ['class' => 'text-center'],
    'filter' => ArrayHelper::map(Brand::find()
        ->addOrderBy(['name_' . Yii::$app->language => SORT_ASC])
        // ->cache(3200, new DbDependency(['sql' => 'SELECT MAX(`updated_at`) FROM ' . Brand::tableName()]))
        ->all(), 'id', 'name_' . Yii::$app->language),
    'filterInputOptions' => ['class' => 'form-control', 'prompt' => html_entity_decode('&mdash; выберите производителя &mdash;')],
    'value' => function ($model) {
        return ($model->brand) ? $model->brand->name : NULL;
    }
];
$columns[] = [
    'attribute' => 'sku',
    'contentOptions' => ['class' => 'text-center']
];
$columns[] = [
    'attribute' => 'price',
    'format' => 'raw',
    'contentOptions' => ['class' => 'text-center'],
    'value' => function ($model) {
        return $model->getGridPrice();
    }
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
        // echo 'eav_' . $attribute->name . '.value';


        $data2 = Yii::$app->cache->get('configuration' . $attribute->id);
        if ($data2 === false) {
            //   $data2 = ArrayHelper::map($attribute->options, 'id', 'value');
            Yii::$app->cache->set('configuration' . $attribute->id, $data2, 86400);
        }


        $columns[] = [
            'attribute' => 'eav_' . $attribute->name . '.value',
            'header' => $attribute->title,
            'contentOptions' => ['class' => 'eav text-center'],

            'filter' => Html::dropDownList('eav[' . $attribute->name . ']', $selected, ArrayHelper::map($attribute->getOptions()->cache(86400), 'id', 'value'), [
                'prompt' => html_entity_decode($product::t('SELECT_ATTRIBUTE')),
                'class' => 'custom-select w-auto'
            ])
        ];
    }
}

$searchModel = new ProductConfigureSearch();
$searchModel->exclude[] = $product->id;
$searchModel->eavAttributes = $eavAttributes;
//print_r($product->getConfigurations());
$configure = [];


$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), ['attribute_id' => $attribute_id,'confs'=>$product->getConfigurations()]);
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
    'pager' => [
        'options' => [
            'class' => 'pagination justify-content-center pb-3 pt-3 mb-0'
        ]
    ]

]);
Pjax::end();


$this->registerJs('initConfigurationsTable();
$(document).on("beforeFilter", "#ConfigurationsProductGrid" , function(event,k) {
    var data = $(this).yiiGridView("data");
    console.log(data.settings.filterSelector);//
    $.pjax({
        url: data.settings.filterUrl,
        container: \'#pjax-ConfigurationsProductGrid\',
        type:"GET",
        push:false,
        timeout:false,
        scrollTo:false,
        data:$(data.settings.filterSelector).serialize()
    });
    return false;
});
');
?>
