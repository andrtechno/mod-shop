<?php

namespace panix\mod\shop\commands;

use panix\engine\CMS;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductCategoryRef;
use Yii;
use panix\engine\console\controllers\ConsoleController;


/**
 * DEV
 * @package panix\mod\shop\commands
 */
class DevController extends ConsoleController
{
    public $attributesNames = [
        'Диагональ экрана' => [

        ],
        'Разрешение дисплея' => [

        ],
        'Тип матрицы' => [

        ]
    ];

    public function beforeAction($action)
    {

        return parent::beforeAction($action);
    }
    public function actionTest(){

        for ($i = 1; $i <= 10; $i++) {
            copy('https://i.citrus.ua/uploads/shop/c/2/c27e2c410abf6f7b4221980e5dc4e4d3.jpg', Yii::getAlias('@app/web/uploads').DIRECTORY_SEPARATOR.'pic'.$i.'.jpg');
        }
    }

    public function actionDelete(){
        Yii::$app->db->createCommand()
            ->delete(Product::tableName(), ['!=','id',range(1,30)])
            ->execute();
    }
    public function actionIndex()
    {

        //Product::getDb()->createCommand()->truncateTable(Product::tableName())->execute();
        for ($i = 1; $i <= 1000; $i++) {
            $data[] = [
                77000 + $i,
                3,
                3,
                5,
                rand(1, 8),
                CMS::gen(255),
                rand(100, 5000),
                rand(50, 2500),
                CMS::gen(10) . '_ru',
                CMS::gen(10) . '_uk',
                $i,
                time(),
                time(),
            ];


            $data2[] = [
                50 + $i,
                3,
                1,
                3
            ];

        }


        Product::getDb()->createCommand()->batchInsert(Product::tableName(), [
            'id',
            'type_id',
            'currency_id',
            'main_category_id',
            'manufacturer_id',
            'slug',
            'price',
            'price_purchase',
            'name_ru',
            'name_uk',
            'ordern',
            'created_at',
            'updated_at',
        ], $data)->execute();


        ProductCategoryRef::getDb()->createCommand()->batchInsert(ProductCategoryRef::tableName(), [
            'product',
            'category',
            'is_main',
            'switch',
        ], $data2)->execute();
    }
}
