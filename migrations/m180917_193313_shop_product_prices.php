<?php

namespace panix\mod\shop\migrations;
/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193313_shop_product_prices
 */
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
        ], $this->tableOptions);

        $this->createIndex('product_id', ProductPrices::tableName(), 'product_id');
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable(ProductPrices::tableName());
    }

}
