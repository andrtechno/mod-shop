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

$testLang = new \panix\mod\shop\models\Product();
$testLang->name = 'ads';
$testLang->slug = CMS::gen(10);
$testLang->type_id = 1;
$testLang->price = 100;
$testLang->short_description = ' short description ';
$testLang->full_description = 'full description';
$testLang->save(false);



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


