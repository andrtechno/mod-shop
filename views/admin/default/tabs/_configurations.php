<?php
use panix\engine\Html;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Attribute;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\search\ProductSearch;
use yii\grid\GridView;
use panix\engine\data\ActiveDataProvider;
/**
 * Confirutable products tab
 *
 * @var Controller $this
 * @var Product $product Current product
 * @var Product $model
 */

\panix\mod\shop\assets\admin\ConfigurationsAsset::register($this);
// For grid view we use new products instance
$model =  Product::find(); 
$model2 =  new Product; 

if (isset($_GET['ConfProduct']))
    $model->attributes = $_GET['ConfProduct'];

$columns = array(
 /*   array(
        'class' => 'CheckBoxColumn',
        'checked' => (!empty($product->configurations) && !isset($clearConfigurations) && !$product->isNewRecord) ? 'true' : 'false'
    ),*/
    array(
        'attribute' => 'id',
        'format' => 'text',
        //'value' => '$data->id',
        'filter' => Html::textInput('ConfProduct[id]', $model2->id)
    ),
    array(
        'attribute' => 'name',
        'format' => 'raw',
            'value' => function($model) {
        return Html::a(Html::encode($model->name), array("update", "id"=>$model->id), array("target"=>"_blank"));
    },

        'filter' => Html::textInput('ConfProduct[name]', $model2->name)
    ),
    array(
        'attribute' => 'sku',
        //'value' => '$data->sku',
        'filter' => Html::textInput('ConfProduct[sku]', $model2->sku)
    ),
    array(
        'attribute' => 'price',
           'format' => 'raw',
        //'value' => '$data->price',
        'filter' => Html::textInput('ConfProduct[price]', $model2->price)
    ),
);

// Process attributes
$eavAttributes = array();
$attributeModels = Attribute::find();
//$attributeModels->setTableAlias('Attribute');
$attributeModels = $attributeModels->where(['id'=>$product->configurable_attributes])->all();

foreach ($attributeModels as $attribute) {
    $selected = null;

    if (isset($_GET['eav'][$attribute->name]) && !empty($_GET['eav'][$attribute->name])) {
        $eavAttributes[$attribute->name] = $_GET['eav'][$attribute->name];
        $selected = $_GET['eav'][$attribute->name];
    }
    else
        array_push($eavAttributes, $attribute->name);

    $columns[] = array(
        'attribute' => 'eav_' . $attribute->name,
        'header' => $attribute->title,
        'contentOptions' => array('class' => 'eav'),
        'filter' => Html::dropDownList('eav[' . $attribute->name . ']', $selected, ArrayHelper::map($attribute->options, 'id', 'value'), array(
            'empty' => '---',
        ))
    );
}

if (!empty($eavAttributes))
    $model = $model->withEavAttributes($eavAttributes);


// On edit display only saved configurations
//$cr = new CDbCriteria;
$exclude[] = $product->id;
            foreach ($exclude as $id) {
                //$model->andWhere(['!=', '{{%shop_product}}.id', $id]);
            }


//$model->use_configurations = false;
//$searchModel = new ProductSearch();
//$searchModel->exclude[] = $product->id;
//$searchModel->use_configurations = false;

$configure = [];

if (!empty($product->configurations) && !isset($clearConfigurations) && !$product->isNewRecord){
  // $configure['conf']=$product->configurations;
      $model->andWhere(['IN','id',$product->configurations]);
   //$dataProvider->andWhere(['IN','id',$product->configurations]);//addInCondition('t.id', $product->configurations);
}
//$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(),$configure);


        $dataProvider = new ActiveDataProvider([
            'query' => $model,

        ]);
        


echo GridView::widget([
        'id' => 'ConfigurationsProductGrid',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,

    'columns'=>$columns,
    'showFooter' => true,
    //   'footerRowOptions' => ['class' => 'text-center'],
  //  'rowOptions' => ['class' => 'sortable-column']
]);
/*
$this->widget('ext.adminList.GridView', array(
    'dataProvider' => $dataProvider,
    'ajaxUrl' => Yii::app()->createUrl('/admin/shop/products/ApplyConfigurationsFilter', array(
        'product_id' => $product->id,
        'configurable_attributes' => isset($_GET['ShopProduct']['configurable_attributes']) ? $_GET['ShopProduct']['configurable_attributes'] : $product->configurable_attributes,
    )),
    'genId' => false,
    'id' => 'ConfigurationsProductGrid',
    'template' => '{items}{summary}',//{pager}
    'enableCustomActions' => false,
    'enableSorting' => false,
    'autoColumns' => false,
    'enableHeader' => false,
    'selectionChanged' => 'js:function(id){processConfigurableSelection(id)}',
    'selectableRows' => 2,
    'filter' => $model,
    'columns' => $columns,
));*/
?>

<script type="text/javascript">
    initConfigurationsTable();
</script>