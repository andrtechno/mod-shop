<?php
use panix\engine\grid\GridView;
use panix\engine\widgets\Pjax;
use panix\ext\fancybox\Fancybox;
use panix\mod\shop\bundles\admin\ProductIndex;
use panix\engine\CMS;

echo Fancybox::widget(['target' => '.image a']);
/*
$query1 = (new \yii\db\Query())
    ->select("id, created_at")
    ->from('{{%shop__currency_history}}');


$query2 = (new \yii\db\Query())
    ->select('id, created_at')
    ->where(['product_id'=>4])
    ->from('{{%shop__product_price_history}}');


$query1->union($query2);
echo $query1->createCommand()->rawSql;
$test = $query1->all();
\panix\engine\CMS::dump($test);die;
*/
/*
$testLang = new \panix\mod\shop\models\Product();
$testLang->name = 'ads';
$testLang->slug = CMS::gen(10);
$testLang->type_id = 1;
$testLang->price = 100;
$testLang->price_purchase=100;
$testLang->short_description = ' short description ';
$testLang->full_description = 'full description';
$testLang->save(false);
*/


/*
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

$response = file_get_contents("https://maps.co.weber.ut.us/arcgis/rest/services/SDE_composite_locator/GeocodeServer/findAddressCandidates?Street=&SingleLine=3042+N+1050+W&outFields=*&outSR=102100&searchExtent=&f=json", false, stream_context_create($arrContextOptions));
*/
$arrContextOptions=[
    "ssl"=>[
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ],
];
for ($i = 1; $i <= 10; $i++) {

    file_put_contents(Yii::getAlias('@uploads').DIRECTORY_SEPARATOR.'pic'.$i.'.jpg', file_get_contents('https://i.citrus.ua/uploads/shop/c/2/c27e2c410abf6f7b4221980e5dc4e4d3.jpg', false, stream_context_create($arrContextOptions)));
   // echo Yii::getAlias('@app/web/uploads');die;
   // copy('https://i.citrus.ua/uploads/shop/c/2/c27e2c410abf6f7b4221980e5dc4e4d3.jpg', Yii::getAlias('@app/web/uploads').DIRECTORY_SEPARATOR.'pic'.$i.'.jpg');
}




Pjax::begin(['dataProvider' => $dataProvider]);
ProductIndex::register($this);
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
    //'footerRowOptions' => ['class' => 'text-center'],
]);
Pjax::end();


