<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

\panix\mod\shop\assets\AdminAsset::register($this);
?>

<?php
//\yii\helpers\VarDumper::dump($model,10,true);
//echo $model->getRelatedProductCount(); 
?>

<table class="table table-striped table-bordered" id="relatedProductsTable">
<?php
//print_r($model->relatedProducts2);
?>
        <?php foreach ($model->relatedProducts as $related) { ?>

            <tr>
            <input type="hidden" value="<?php echo $related->id ?>" name="RelatedProductId[]">
            <td class="image text-center relatedProductLine<?php echo $related->id ?>">ads</td>
            <td><?php
                echo Html::a($related->name, array('/admin/shop/products/update', 'id' => $related->id), array(
                    'target' => '_blank'
                ));
                ?></td>
            <td class="text-center"><a class="btn btn-danger" href="javascript:void(0)" onclick="$(this).parents('tr').remove();"><?php echo Yii::t('app', 'DELETE') ?></a></td>
        </tr>
    <?php } ?>

</table>


<br/><br/>



<?php
/* Pjax::begin([
  'id' => 'pjax-container-related', 'enablePushState' => false,
  ]); */
?>


<?php



$searchModel = new panix\mod\shop\models\search\ShopProductSearch();
$searchModel->exclude = [$exclude];

foreach($model->relatedProducts as $d){
  //  $searchModel->exclude[] = $d->id;
}

$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());



echo \yii\grid\GridView::widget([
    'id' => 'RelatedProductsGrid',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    /*'rowOptions' => function ($model, $key, $index, $grid) {
        return ['id' => $model['id']];
    },*/
    //'layout' => $this->render('@app/web/themes/admin/views/layouts/_grid_layout', ['title' => $this->context->pageName]), //'{items}{pager}{summary}'
    'columns' => [
        //   'name',
        [
            'attribute' => 'name',
            'format' => 'raw',
 //'contentOptions' =>function ($model, $key, $index, $column){
  //              return ['class' => 'name','data-id'=>$model->id];
            //},
            'value' => function($model, $key, $index) {
        return Html::a($model->name, ["update", "id" => $model->id], ["target" => "_blank", "class" => "product-name", "data-id" => $model->id]);
    }

        //   'value' => 'Html::link(Html::encode($data->name), array("update", "id"=>$data->id), array("target"=>"_blank","class"=>"product-name","data-id"=>$data->id))',
        // 'filter' => Html::textField('RelatedProducts[name]', $model->name)
        ],
        [
            'attribute' => 'price',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
        return $model::formatPrice($model->price) . ' ' . Yii::$app->currency->main->symbol;
    }
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{add}',
            'buttons' => [
                'add' => function ($url, $model) {
                    return Html::a('<i class="icon-add"></i>', $model->id . '/' . Html::encode($model->name), [
                                'title' => Yii::t('yii', 'add'),
                                'class' => 'btn btn-success',
                                'onClick' => 'return AddRelatedProduct(this);',
                                'data-pjax' => false
                    ]);
                },
                    ],
                ]
            ]
        ]);
        ?>
        <?php //Pjax::end(); ?>


