<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193913_shop_product_notify
 */
use panix\mod\shop\models\ProductNotifications;
use panix\engine\db\Migration;

class m180917_193913_shop_product_notify extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        // create table order product notify
        $this->createTable(ProductNotifications::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->null(),
            'product_id' => $this->integer()->unsigned()->notNull(),
            'email' => $this->string(100),
        ], $tableOptions);


        // order product notify indexes
        $this->createIndex('product_id', ProductNotifications::tableName(), 'product_id');
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable(ProductNotifications::tableName());

    }

}
