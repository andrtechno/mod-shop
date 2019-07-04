<?php

namespace panix\mod\shop\migrations;
/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * 
 * Class m180917_193505_shop_category
 */
use yii\db\Schema;
use panix\engine\db\Migration;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\translate\CategoryTranslate;

class m180917_193505_shop_category extends Migration {

    public function up()
    {
        $this->createTable(Category::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'tree' => $this->integer()->unsigned()->null(),
            'lft' => $this->integer()->unsigned()->notNull(),
            'rgt' => $this->integer()->unsigned()->notNull(),
            'depth' => $this->smallInteger(5)->unsigned()->notNull(),
            'slug' => $this->string(255)->null()->defaultValue(null),
            'full_path' => $this->string(255)->null(),
            'image' => $this->string(50)->null(),
            'switch' => $this->boolean()->defaultValue(1),
        ], $this->tableOptions);


        $this->createTable(CategoryTranslate::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'object_id' => $this->integer()->unsigned(),
            'language_id' => $this->tinyInteger()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text()->null()->defaultValue(null),
        ], $this->tableOptions);




        $this->createIndex('lft', Category::tableName(), 'lft');
        $this->createIndex('rgt', Category::tableName(), 'rgt');
        $this->createIndex('depth', Category::tableName(), 'depth');
        $this->createIndex('full_path', Category::tableName(), 'full_path');
        $this->createIndex('switch', Category::tableName(), 'switch');


        $this->createIndex('object_id', CategoryTranslate::tableName(), 'object_id');
        $this->createIndex('language_id', CategoryTranslate::tableName(), 'language_id');

    }

    public function down()
    {
        $this->dropTable(Category::tableName());
        $this->dropTable(CategoryTranslate::tableName());
    }

}
