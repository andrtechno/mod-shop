<?php

namespace panix\mod\shop\commands;

use panix\engine\CMS;
use panix\mod\discounts\models\Discount;
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

    public function actionRefreshHotSale()
    {
        $aggregate = 86400*90;
       // $newDate = date('Y-m-d', strtotime('+3 month'));
        $newDate = date('Y-m-d', time()+$aggregate);


echo time() - 86400 *50;
echo '---';


        $productQuery = Product::find()->where(['>=','added_to_cart_count',10])->andWhere(['<=','added_to_cart_date',time()-$aggregate]);
        //$productQuery->int2between(time(), time() - (86400),'added_to_cart_date');
     //   echo $newDate;
       echo $productQuery->createCommand()->rawSql;
        echo $productQuery->count();
        die;
    }

    public function actionDelete()
    {
        Yii::$app->db->createCommand()
            ->delete(Product::tableName(), ['!=', 'id', range(1, 30)])
            ->execute();
    }

    public function actionIndex()
    {
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
        // $input = array("Для волос", "Крем", "Маска", "Для глаз", "увлажнитель", 'Лицо', 'Тело','мыло','Духи','для кожи');
        $lorem = str_replace([',', '.'], ['', ''], $lorem);
        $input = explode(' ', $lorem);
        $rand_keys = array_rand($input, 2);
        //  echo $input[$rand_keys[0]] . "\n";
        // echo $input[$rand_keys[1]] . "\n";

        //Product::getDb()->createCommand()->truncateTable(Product::tableName())->execute();
        for ($i = 1; $i <= 5; $i++) {
            $id = 720 + $i;
            $name = implode(' ',$this->array_random($input, 3));
            $categoryId = 5;
            $products[] = [
                $id,
                3,
                3,
                $categoryId,
                rand(1, 8),
                CMS::slug($name),
                rand(100, 5000),
                rand(50, 2500),
                $name.'_ru',
                $name.'_ua',
                $id,
                time(),
                time(),
            ];


            $data2[] = [
                $id,
                $categoryId,
                1,
                3
            ];

        }


       // print_r($this->array_random($input, 3));
       // die;

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
        ], $products)->execute();


        ProductCategoryRef::getDb()->createCommand()->batchInsert(ProductCategoryRef::tableName(), [
            'product',
            'category',
            'is_main',
            'switch',
        ], $data2)->execute();
    }

    private function array_random(array $array, int $n = 1): array
    {
        if ($n < 1 || $n > count($array)) {
            // throw new OutOfBoundsException();
        }

        return ($n !== 1)
            ? array_values(array_intersect_key($array, array_flip(array_rand($array, $n))))
            : array($array[array_rand($array)]);
    }
}
