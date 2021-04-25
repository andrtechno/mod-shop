<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193650_shop_product_attribute_eav
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductAttributesEav;
use panix\mod\shop\models\Attribute;

class m180917_193650_shop_product_attribute_eav extends Migration
{

    public function up()
    {
        $this->createTable(ProductAttributesEav::tableName(), [
            'entity' => $this->integer()->unsigned(),
            'attribute' => $this->string(255)->null(),
            'value' => $this->string(255),
        ]);

        $this->createIndex('entity', ProductAttributesEav::tableName(), 'entity');
        $this->createIndex('attribute', ProductAttributesEav::tableName(), 'attribute');
        $this->createIndex('value', ProductAttributesEav::tableName(), 'value');

        if (!in_array($this->db->driverName, ['sqlite', 'pgsql'])) {
            $this->addForeignKey('{{%fk_product_eav_attribute}}', ProductAttributesEav::tableName(), 'attribute', Attribute::tableName(), 'name', "CASCADE", "CASCADE");
            $this->addForeignKey('{{%fk_product_eav_entity}}', ProductAttributesEav::tableName(), 'entity', Product::tableName(), 'id', "CASCADE", "CASCADE");
        }
    }

    public function down()
    {
        $this->dropTable(ProductAttributesEav::tableName());
    }

}
