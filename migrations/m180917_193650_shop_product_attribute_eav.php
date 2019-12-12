<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193650_shop_product_attribute_eav
 */
use panix\engine\db\Migration;
use panix\mod\shop\models\ProductAttributesEav;

class m180917_193650_shop_product_attribute_eav extends Migration
{

    public function up()
    {
        $this->createTable(ProductAttributesEav::tableName(), [
            'entity' => $this->integer()->unsigned(),
            'attribute' => $this->string(255)->null(),
            'value' => $this->text(),
        ]);

        $this->createIndex('entity', ProductAttributesEav::tableName(), 'entity');
        $this->createIndex('attribute', ProductAttributesEav::tableName(), 'attribute');
    }

    public function down()
    {
        $this->dropTable(ProductAttributesEav::tableName());
    }

}
