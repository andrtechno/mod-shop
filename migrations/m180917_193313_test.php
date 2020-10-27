<?php

namespace panix\mod\shop\migrations;
/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193313_test
 */
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductPrices;
use panix\engine\db\Migration;

class m180917_193313_test extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('{{%shop__product_price_history_test}}', [
            'id' => $this->primaryKey()->unsigned(),
            'product_id' => $this->integer()->null(),
            'currency_id' => $this->integer()->null(),
            'discount' => $this->string(10)->null(),
            'price' => $this->money(10,2),
            'price_purchase' => $this->money(10,2),
            'created_at' => $this->integer(),
            'type' => $this->tinyInteger(1)->null()->defaultValue(0)->comment('1=up, 0=down'),
        ]);

        $this->createIndex('product_id', '{{%shop__product_price_history_test}}', 'product_id');
        $this->createIndex('currency_id', '{{%shop__product_price_history_test}}', 'currency_id');


        if ($this->db->driverName != "sqlite") {
         //   $this->addForeignKey('{{%fk_product_price_history_test_product_id}}', '{{%shop__product_price_history_test}}', 'product_id', Product::tableName(), 'id', "CASCADE", "CASCADE");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        if ($this->db->driverName != "sqlite") {
        //    $this->dropForeignKey('{{%fk_product_price_history_test_product_id}}', '{{%shop__product_price_history_test}}');

        }
        $this->dropTable('{{%shop__product_price_history_test}}');
    }

}
