<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180857_193215_shop_sets
 */
use panix\mod\rbac\migrations\MigrationTrait;
use panix\mod\shop\models\Sets;
use panix\engine\db\Migration;
use panix\mod\shop\models\SetsProduct;

class m180857_193215_shop_sets extends Migration
{

    use MigrationTrait;

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable(Sets::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'product_id' => $this->integer()->unsigned(),
            'value' => $this->money(10, 2),
            'from' => $this->tinyInteger()->unsigned(),
        ], $this->tableOptions);
        $this->createIndex('product_id', Sets::tableName(), 'product_id');


        $this->createTable(SetsProduct::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'set_id' => $this->integer()->unsigned(),
            'product_id' => $this->integer()->unsigned(),
        ], $this->tableOptions);

        $this->createIndex('set_id', SetsProduct::tableName(), 'set_id');
        $this->createIndex('product_id', SetsProduct::tableName(), 'product_id');


    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable(Sets::tableName());
        $this->dropTable(SetsProduct::tableName());
    }

}
