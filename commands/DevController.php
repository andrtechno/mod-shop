<?php

namespace panix\mod\shop\commands;

use panix\engine\CMS;
use panix\mod\shop\models\Product;
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
    public function actionIndex()
    {

        Product::getDb()->createCommand()->truncateTable(Product::tableName())->execute();
        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                1,
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
        }


        Product::getDb()->createCommand()->batchInsert(Product::tableName(), [
            'type_id',
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
    }
}
