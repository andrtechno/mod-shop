<?php

namespace panix\mod\shop\migrations;
/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193313_shop_product_prices
 */
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductPrices;
use panix\engine\db\Migration;

class m180917_193313_shop_product_prices extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable(ProductPrices::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'product_id' => $this->integer()->unsigned(),
            'value' => $this->money(10, 2),
            'from' => $this->tinyInteger()->unsigned(),
        ]);

        $this->createIndex('product_id', ProductPrices::tableName(), 'product_id');

        $this->createTable('{{%shop__product_price_history}}', [
            'id' => $this->primaryKey()->unsigned(),
            'product_id' => $this->integer()->null(),
            'currency_id' => $this->integer()->null(),
            'currency_rate' => $this->money(10,2),
            'discount' => $this->string(10)->null(),
            'price' => $this->money(10,2),
            'price_purchase' => $this->money(10,2),
            'created_at' => $this->integer(),
            'type' => $this->tinyInteger(1)->null()->defaultValue(0)->comment('1=up, 0=down'),
        ]);

        $this->createIndex('product_id', '{{%shop__product_price_history}}', 'product_id');
        $this->createIndex('currency_id', '{{%shop__product_price_history}}', 'currency_id');


        //if ($this->db->driverName != "sqlite") {
        //    $this->addForeignKey('{{%fk_product_price_history_product_id}}', '{{%shop__product_price_history}}', 'product_id', Product::tableName(), 'id', "CASCADE", "CASCADE");
       // }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        //if ($this->db->driverName != "sqlite") {
        //    $this->dropForeignKey('{{%fk_product_price_history_product_id}}', '{{%shop__product_price_history}}');
       // }
        $this->dropTable(ProductPrices::tableName());
        $this->dropTable('{{%shop__product_price_history}}');
    }

}
