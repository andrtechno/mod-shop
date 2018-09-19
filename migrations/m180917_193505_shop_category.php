<?php

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
            'tree' => $this->integer()->null(),
            'lft' => $this->integer()->notNull(),
            'rgt' => $this->integer()->notNull(),
            'depth' => $this->integer()->notNull(),
            'seo_alias' => $this->string(255)->null()->defaultValue(null),
            'full_path' => $this->string(255)->null(),
            'description' => $this->text()->null()->defaultValue(null),
            'image' => $this->string(50),
            'switch' => $this->boolean()->defaultValue(1),
        ], $this->tableOptions);


        $this->createTable(CategoryTranslate::tableName(), [
            'id' => $this->primaryKey(),
            'object_id' => $this->integer(),
            'language_id' => $this->integer(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text()->null()->defaultValue(null),
        ], $this->tableOptions);




        $this->createIndex('lft', Category::tableName(), 'lft', 0);
        $this->createIndex('rgt', Category::tableName(), 'rgt', 0);
        $this->createIndex('depth', Category::tableName(), 'depth', 0);
        $this->createIndex('full_path', Category::tableName(), 'full_path', 0);


        $this->createIndex('object_id', CategoryTranslate::tableName(), 'object_id', 0);
        $this->createIndex('language_id', CategoryTranslate::tableName(), 'language_id', 0);

    }

    public function down()
    {
        $this->dropTable(Category::tableName());
        $this->dropTable(CategoryTranslate::tableName());
    }

}