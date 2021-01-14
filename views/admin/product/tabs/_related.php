<?php

use panix\engine\Html;
use yii\widgets\Pjax;

/**
 * @var \panix\mod\shop\models\Product $model
 * @var \yii\web\View $this
 */
\panix\mod\shop\bundles\AdminAsset::register($this);
$searchModel = new panix\mod\shop\models\search\ProductRelatedSearch();
?>

<table class="table table-striped table-bordered" id="relatedProductsTable">
    <?php
    foreach ($model->relatedProducts as $related) {
        //$searchModel->exclude[]=$related->id;
        ?>
        <tr>
            <input type="hidden" value="<?= $related->id ?>" name="RelatedProductId[]">
            <td class="image text-center relatedProductLine<?= $related->id ?>"><?= $related->renderGridImage('50x50'); ?></td>
            <td>
                <?= Html::a($related->name, ['/admin/shop/products/update', 'id' => $related->id], [
                    'target' => '_blank'
                ]);
                ?>
            </td>
            <td class="text-center">
                <a class="btn btn-danger" href="#" onclick="$(this).parents('tr').remove();"><?= Yii::t('app/default', 'DELETE') ?></a>
            </td>
        </tr>
    <?php } ?>

</table>

<?php

$searchModel->exclude[] = $exclude;
//$searchModel->detachBehavior(['seo','comments','imagesBehavior']);

$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

Pjax::begin([
    //'dataProvider' => $dataProvider,
    'id'=>'pjax-RelatedProductsGrid',
    'enablePushState' => false,
    'timeout' => false
]);
echo \panix\engine\grid\GridView::widget([
    'id' => 'RelatedProductsGrid',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'enableLayout'=>false,
    /*'rowOptions' => function ($model, $key, $index, $grid) {
        return ['id' => $model['id']];
    },*/

    'filterUrl' => [
        'apply-related-filter',
        'product_id' => $model->id,
     //   'configurable_attributes' => isset($_GET['configurable_attributes']) ? $_GET['configurable_attributes'] : $product->configurable_attributes
    ],
    'columns' => [
        [
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function ($model) {
                return $model->renderGridImage('50x50');
            },
        ],
        [
            'attribute' => 'name',
            'format' => 'raw',
            //'contentOptions' =>function ($model, $key, $index, $column){
            //              return ['class' => 'name','data-id'=>$model->id];
            //},
            'value' => function ($model, $key, $index) {
                return Html::a($model->name, ["update", "id" => $model->id], ["target" => "_blank", "class" => "product-name", "data-id" => $model->id]);
            }

            //   'value' => 'Html::link(Html::encode($data->name), array("update", "id"=>$data->id), array("target"=>"_blank","class"=>"product-name","data-id"=>$data->id))',
            // 'filter' => Html::textField('RelatedProducts[name]', $model->name)
        ],
        [
            'attribute' => 'price',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return Yii::$app->currency->number_format($model->price) . ' ' . Yii::$app->currency->main['symbol'];
            }
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{add}',
            'buttons' => [
                'add' => function ($url, $model) { //$model->id . '/' . Html::encode($model->name)
                    return Html::a(Html::icon('add'), '#', [
                        'title' => Yii::t('app/default', 'ADD'),
                        'class' => 'btn btn-sm btn-success',
                        'onClick' => 'return AddRelatedProduct(this);',
                        'data-pjax' => false
                    ]);
                },
            ],
        ]
    ]
]);
Pjax::end();


$this->registerJs('
$(document).on("beforeFilter", "#RelatedProductsGrid" , function(event,k) {
    var data = $(this).yiiGridView("data");

    $.pjax({
        url: data.settings.filterUrl,
        container: "#pjax-RelatedProductsGrid",
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


