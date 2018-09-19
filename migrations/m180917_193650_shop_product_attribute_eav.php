<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * 
 * Class m180917_193650_shop_product_attribute_eav
 */
use yii\db\Schema;
use panix\engine\db\Migration;

class m180917_193650_shop_product_attribute_eav extends Migration {

    public function up()
    {
        $this->createTable('{{%shop_product_attribute_eav}}', [
            'entity' => $this->integer()->unsigned(),
            'attribute' => $this->string(255)->null(),
            'value' => $this->string(255)->null(),
        ], $this->tableOptions);

        $this->createIndex('entity', '{{%shop_product_attribute_eav}}', 'entity', 0);
        $this->createIndex('attribute', '{{%shop_product_attribute_eav}}', 'attribute', 0);
        $this->createIndex('value', '{{%shop_product_attribute_eav}}', 'value', 0);
    }

    public function down()
    {
        $this->dropTable('{{%shop_product_attribute_eav}}');
    }

}