<?php

namespace panix\mod\shop\commands;


use yii\console\Controller;
use yii\db\QueryBuilder;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\components\forsage\ForsageProductsImport;
use panix\mod\shop\components\forsage\ForsageExternalFinder;


/**
 * Sync "Forsage studio" API
 * @package panix\mod\shop\commands
 */
class ForsageController extends Controller
{

    public function beforeAction($action)
    {
        $forsage = new ForsageProductsImport();
        if (!file_exists(\Yii::getAlias($forsage->tempDirectory))) {
            FileHelper::createDirectory(\Yii::getAlias($forsage->tempDirectory));
        }

        return parent::beforeAction($action);
    }

    /**
     * asdasdas
     */
    public function actionChanges2()
    {
       // Yii::import('app.php-multi-curl.*');

        $forsage = new ForsageProductsImport;
        // $products = $forsage->getSupplierProductIds(505);

        $products = $forsage->getChanges();

        $options = [
            CURLOPT_TIMEOUT => 1000,
            CURLOPT_CONNECTTIMEOUT => 5000,
            CURLOPT_USERAGENT => 'Multi-cURL client v1.5.0',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => true,
        ];

        if ($products) {
            $c = array();
            echo count($products) . PHP_EOL;
            $chunkProducts = array_chunk($products, 100, true);
            //$chunkProducts = array_chunk($products, 100, true);

            $i = 0;

            foreach ($chunkProducts as $k2 => $products1) {
                echo 'Page: ' . $k2 . PHP_EOL;
                $c = array();
                foreach ($products1 as $k => $product_id) {
                    $c[$k] = new Curl($options);
                    $c[$k]->makeGet("https://forsage-studio.com/api/get_product/{$product_id}?token={$forsage->apikey}");
                }
                $mc = new MultiCurl();

                $mc->addCurls($c);
                $allSuccess = $mc->exec();
                if ($allSuccess) {
                    foreach ($c as $resp) {
                        $i++;
                        $data = CJSON::decode($resp->getResponse()->getBody(), false);
                        //$forsage->insert_update($data->product);
                        $forsage->insert_update($data->product, 1);
                    }
                } else {
                    foreach ($c as $resp) {
                        var_dump($resp->getResponse()->getError());
                    }
                }
                $mc->reset();

            }
            echo 'Total items: ' . $i . PHP_EOL;
        }
    }

    public function actionChanges()
    {
        $forsage = new ForsageProductsImport;
        $forsage->change();
        \Yii::debug('ForsageCommand actionChanges start', 'info');
    }

    public function actionSupplierProducts($id = false)
    {
        if ((int)$id) {
            \Yii::debug('ForsageCommand actionSupplierProducts start', 'info');
            $forsage = new ForsageProductsImport;
            $products = $forsage->getSupplierProductIds((int)$id);
            if ($products) {
                $log = "Products count: " . count($products);
                echo $log . PHP_EOL;
                \Yii::debug($log, 'info');
                foreach ($products as $product_id) {
                    $product = $forsage->getProduct((int)$product_id);
                    $forsage->insert_update($product);
                }
            }
            \Yii::debug('ForsageCommand actionSupplierProducts end', 'info');

        }
    }


    /**
     * Добавляем товар по ID
     *
     * @param int $id
     * @param bool $insert
     */
    public function actionAddProduct(int $id = 0, $insert = true)
    {
        if ($id) {
            $forsage = new ForsageProductsImport;
            $product = $forsage->getProduct($id);
            if ($insert)
                $forsage->insert_update($product);
            else
                print_r($product);
            \Yii::debug('ForsageCommand actionAddProduct start', 'info');
        }

    }

    public function actionAddSuppliers()
    {
        $forsage = new ForsageProductsImport;
        $suppliers = $forsage->getSuppliers();
        print_r($suppliers);
        if ($suppliers) {

        }
        \Yii::debug('ForsageCommand actionAddSuppliers start', 'info');
    }

    /**
     * Добавляем новые товары за сегодня.
     */
    public function actionProducts()
    {
        $forsage = new ForsageProductsImport;
        $forsage->products();
        \Yii::debug('ForsageCommand actionProducts start', 'info');
    }

    /**
     * Добавляем весь ассортимент, используются для начальной стадии запуска.
     *
     * @param int $offset Отступ
     * @param int $limit Лимит
     */
    public function actionImportAll(int $offset = 0, int $limit = 10)
    {
        $forsage = new ForsageProductsImport;
        $forsage->importAll($offset, $limit);
        \Yii::debug('ForsageCommand actionImportAll start', 'info');

    }


    public function actionSetSql()
    {

        $command = \Yii::$app->db->createCommand();


        //Clear table
        // $command->truncateTable('{{%exchange_forsage}}');

        $start = microtime(true);
        $i = 0;
        $sqlrows = [];
        //Size
        $attributes = Attribute::find()->all();
        foreach ($attributes as $a) {
            $i++;
            $sqlrows[] = [
                $a->id,
                ForsageExternalFinder::OBJECT_TYPE_ATTRIBUTE,
                $a->title,
            ];
            foreach ($a->options as $o) {
                $i++;
                $sqlrows[] = [
                    $o->id,
                    ForsageExternalFinder::OBJECT_TYPE_ATTRIBUTE_OPTION,
                    $o->value,
                ];

            }

            // ->execute();
        }


        //Manufacturer
        $manufacturers = Manufacturer::find()->all();
        foreach ($manufacturers as $a) {
            $i++;
            $sqlrows[] = [
                $a->id,
                ForsageExternalFinder::OBJECT_TYPE_MANUFACTURER,
                $a->name,
            ];
        }


        //Categories
        $categories = Category::findOne(1);
        $results = $categories->menuArray();
        foreach ($results['items'] as $a) {
            $i++;

            $sqlrows[] = [
                $a['id'],
                ForsageExternalFinder::OBJECT_TYPE_MAIN_CATEGORY,
                $a['label'],
            ];

            if (isset($a['items'])) {
                foreach ($a['items'] as $b) {
                    $i++;

                    $sqlrows[] = [
                        $b['id'],
                        ForsageExternalFinder::OBJECT_TYPE_CATEGORY,
                        $b['label'],
                    ];

                }
            }
        }
        $sql = \Yii::$app->db
            ->createCommand()
            ->batchInsert('{{%exchange_forsage}}', ['object_id', 'object_type', 'external_id'], $sqlrows);
        echo $sql->rawSql;
        $sql->execute();


    }


    public function actionGetSql()
    {

        $start = microtime(true);
        $i = 0;
        echo "INSERT INTO `" . \Yii::$app->db->tablePrefix . "exchange_forsage` (`id`, `object_id`, `object_type`, `external_id`) VALUES" . PHP_EOL;

        //Size
        $attributes = Attribute::find()->all();
        foreach ($attributes as $a) {
            $i++;
            echo "(NULL," . $a->id . "," . ForsageExternalFinder::OBJECT_TYPE_ATTRIBUTE . ",'" . $a->title . "')," . PHP_EOL;
            foreach ($a->options as $o) {
                $i++;
                echo "(NULL," . $o->id . "," . ForsageExternalFinder::OBJECT_TYPE_ATTRIBUTE_OPTION . ",'" . $o->value . "')," . PHP_EOL;
            }
        }


        //Manufacturer
        $manufacturers = Manufacturer::find()->all();
        foreach ($manufacturers as $a) {
            $i++;
            echo "(NULL," . $a->id . "," . ForsageExternalFinder::OBJECT_TYPE_MANUFACTURER . ",'" . $a->name . "')," . PHP_EOL;
        }


        //Categories
        $categories = Category::findOne(1);
        $results = $categories->menuArray();

        $this->recursiveGetSql($results['items']);

        echo 'PageLoad: ' . (microtime(true) - $start) . ' sec.';
        \Yii::debug('ForsageCommand get sql', 'info', 'console');

    }

    private function recursiveGetSql(array $items)
    {
        if (isset($items)) {
            foreach ($items as $item) {
//$item['url']['seo_alias']
                echo "(NULL," . $item['id'] . "," . ForsageExternalFinder::OBJECT_TYPE_CATEGORY . ",'" . $item['label'] . "')," . PHP_EOL;
                if (isset($item['items']))
                    $this->recursiveGetSql($item['items']);
            }
        }
    }

    public function addSupplier()
    {

        $forsage = new ForsageProductsImport;


        foreach ($forsage->supplierList as $supplier_id => $supplier_name) {

            $model = Manufacturer::model()->findByAttributes(array('name' => $supplier_name));
            if ($model) {
                Yii::app()->db->createCommand()->insert('{{exchange_forsage}}', array(
                    'object_type' => ForsageExternalFinder::OBJECT_TYPE_MANUFACTURER,
                    'object_id' => $model->id,
                    'external_id' => $model->name
                ));
            } else {
                $manufacturer = ForsageExternalFinder::getObject(ForsageExternalFinder::OBJECT_TYPE_MANUFACTURER, $supplier_id);
                if (!$manufacturer) { //new
                    $manufacturer = new Manufacturer;
                    $manufacturer->name = $supplier_name;
                    $manufacturer->seo_alias = CMS::translit($manufacturer->name);
                    $manufacturer->save(false);
                    Yii::app()->db->createCommand()->insert('{{exchange_forsage}}', array(
                        'object_type' => ForsageExternalFinder::OBJECT_TYPE_MANUFACTURER,
                        'object_id' => $manufacturer->id,
                        'external_id' => $supplier_name
                    ));
                }
            }
        }
    }

    /**
     * Создаем базу данных
     */
    public function actionCreateTable()
    {
        $tableSchema = \Yii::$app->db->schema->getTableSchema('{{%exchange_forsage}}');
        if (!$tableSchema === null) {
            $queryBuilder = new QueryBuilder(\Yii::$app->db);
            $create = $queryBuilder->createTable('{{%exchange_forsage}}', [
                'id' => 'int(11) unsigned PRIMARY KEY NOT NULL AUTO_INCREMENT',
                'object_id' => 'int(11) unsigned DEFAULT NULL',
                'object_type' => 'int(11) unsigned DEFAULT NULL',
                'external_id' => 'varchar(255)'
            ]);
            $queryBuilder->db->createCommand($create)->execute();
            $queryBuilder->db->createCommand($queryBuilder->createIndex('object_id', '{{%exchange_forsage}}', 'object_id'))->execute();
            $queryBuilder->db->createCommand($queryBuilder->createIndex('object_type', '{{%exchange_forsage}}', 'object_type'))->execute();
            echo $this->ansiFormat(' Table create success', Console::FG_GREEN);
        } else {
            echo $this->ansiFormat(' Table already exists', Console::FG_RED);
        }
    }
}
