<?php
use panix\engine\grid\GridView;
use panix\engine\widgets\Pjax;
use panix\ext\fancybox\Fancybox;

echo Fancybox::widget(['target' => '.image a']);

Pjax::begin([
    'id' => 'pjax-grid-product',
]);


if(Yii::$app->request->isPjax){

    //Yii::$app->assetManager->bundles['yii\jui\JuiAsset']['css']= [];
    //Yii::$app->assetManager->bundles['yii\bootstrap4\BootstrapPluginAsset']['css']= [];
    ///Yii::$app->assetManager->bundles['yii\web\JqueryAsset']['css']= [];
    //Yii::$app->assetManager->bundles['yii\bootstrap4\BootstrapAsset']['css']= [];
   // foreach (Yii::$app->assetManager->bundles as $key=>$b){
        //Yii::$app->assetManager->bundles[$key]['css']= [];
    //}
}

\panix\mod\shop\bundles\admin\ProductIndex::register($this);




echo GridView::widget([
    'id' => 'grid-product',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layoutOptions' => [
        'title' => $this->context->pageName,
        'buttons' => [
            [
                'url' => ['create'],
                'label' => Yii::t('shop/admin', 'CREATE_PRODUCT'),
                'icon' => 'add'
            ]
        ]
    ],
    'showFooter' => true,
    //   'footerRowOptions' => ['class' => 'text-center'],
    'rowOptions' => ['class' => 'sortable-column']
]);
Pjax::end();


