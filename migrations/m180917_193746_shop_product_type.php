<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193746_shop_product_type
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\ProductTypeTranslate;

class m180917_193746_shop_product_type extends Migration
{

    public function up()
    {
        $this->createTable(ProductType::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->null(),
            'product_name' => $this->text()->null(),
            'categories_preset' => $this->text()->null()->defaultValue(null),
            'main_category' => $this->integer(11)->null()->defaultValue(0),
        ]);
        $this->createIndex('name', ProductType::tableName(), 'name');

        $this->createTable(ProductTypeTranslate::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'object_id' => $this->integer()->unsigned(),
            'language_id' => $this->tinyInteger()->unsigned(),
            'product_title' => $this->string(255)->null(),
            'product_description' => $this->string(255)->null()
        ]);

        $this->createIndex('object_id', ProductTypeTranslate::tableName(), 'object_id');
        $this->createIndex('language_id', ProductTypeTranslate::tableName(), 'language_id');

        if ($this->db->driverName != "sqlite") {
            $this->addForeignKey('{{%fk_producttype_translate}}', ProductTypeTranslate::tableName(), 'object_id', ProductType::tableName(), 'id', "CASCADE", "NO ACTION");
        }
    }

    public function down()
    {
        if ($this->db->driverName != "sqlite") {
            $this->dropForeignKey('{{%fk_producttype_translate}}', ProductTypeTranslate::tableName());
        }
        $this->dropTable(ProductType::tableName());
        $this->dropTable(ProductTypeTranslate::tableName());

    }

}
