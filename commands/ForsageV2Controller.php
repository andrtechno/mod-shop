<?php

namespace panix\mod\shop\commands;


use Yii;
use yii\db\QueryBuilder;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use panix\engine\console\controllers\ConsoleController;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\components\forsagev2\ForsageProductsImport;
use panix\mod\shop\components\forsagev2\ForsageExternalFinder;


/**
 * Sync "Forsage studio" API
 * @package panix\mod\shop\commands
 */
class ForsageV2Controller extends ConsoleController
{

    public function actionChanges()
    {
        $starttime = microtime(true);
        $forsage = new ForsageProductsImport;
        $forsage->change();

        $this->log(sprintf("[Time: %f sec]", microtime(true) - $starttime));
    }

    public function actionSupplierProducts($id = false)
    {
        if ((int)$id) {
            Yii::debug('ForsageCommand actionSupplierProducts start', 'info');
            $forsage = new ForsageProductsImport;
            $products = $forsage->getSupplierProductIds((int)$id);
            if ($products) {
                $log = "Products count: " . count($products);
                echo $log . PHP_EOL;
                Yii::debug($log, 'info');
                foreach ($products as $product_id) {
                    $product = $forsage->getProduct((int)$product_id);
                    $forsage->insert_update($product);
                }
            }
            Yii::debug('ForsageCommand actionSupplierProducts end', 'info');

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
            $this->log('ForsageCommand actionAddProduct start');
            $forsage = new ForsageProductsImport;
            $product = $forsage->getProduct($id);
            if ($insert)
                $forsage->insert_update($product);
            else
                print_r($product);


        }

    }

    public function actionAddSuppliers()
    {
        $forsage = new ForsageProductsImport;
        $suppliers = $forsage->getSuppliers();
        print_r($suppliers);
        if ($suppliers) {

        }
        $this->log('ForsageCommand actionAddSuppliers start');
    }

    /**
     * Добавляем новые товары за сегодня.
     */
    public function actionProducts()
    {
        $starttime = microtime(true);
        $forsage = new ForsageProductsImport;
        $forsage->products();

        $this->log('ForsageCommand actionProducts start');
        $this->log(sprintf("[Time: %f sec]", microtime(true) - $starttime));
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
        $this->log('ForsageCommand actionImportAll start');

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
//$item['url']['slug']
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
                    $manufacturer->slug = CMS::translit($manufacturer->name);
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


    public function beforeAction($action)
    {
        $forsage = new ForsageProductsImport();
        if (!file_exists(\Yii::getAlias($forsage->tempDirectory))) {
            FileHelper::createDirectory(\Yii::getAlias($forsage->tempDirectory));
        }

        return parent::beforeAction($action);
    }

    /**
     * @param string $message
     */
    private function log($message)
    {
        $this->stdout($message, Console::FG_YELLOW);
        Yii::debug($message, 'info');
    }
}
