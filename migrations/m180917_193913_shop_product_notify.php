<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193313_shop_product_prices
 */
use panix\mod\shop\models\ProductNotifications;
use panix\engine\db\Migration;

class m180917_193313_shop_product_prices extends Migration
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
            'product_id' => $this->integer()->notNull()->unsigned(),
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
