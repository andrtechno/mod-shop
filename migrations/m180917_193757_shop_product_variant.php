<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193757_shop_product_variant
 */
use yii\db\Schema;
use panix\engine\db\Migration;

class m180917_193757_shop_product_variant extends Migration
{

    public function up()
    {
        $this->createTable('{{%shop_product_variant}}', [
            'id' => $this->primaryKey()->unsigned(),
            'attribute_id' => $this->integer(11)->null()->unsigned(),
            'option_id' => $this->integer(11)->null()->unsigned(),
            'product_id' => $this->integer(11)->null()->unsigned(),
            'price' => $this->float('10,2')->null(),
            'price_type' => $this->boolean()->null(),
            'sku' => $this->string(255)->null(),
        ], $this->tableOptions);

        $this->createIndex('attribute_id', '{{%shop_product_variant}}', 'attribute_id', 0);
        $this->createIndex('option_id', '{{%shop_product_variant}}', 'option_id', 0);
        $this->createIndex('product_id', '{{%shop_product_variant}}', 'product_id', 0);
    }

    public function down()
    {
        $this->dropTable('{{%shop_product_variant}}');
    }

}