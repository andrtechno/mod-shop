<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * 
 * Class m180917_193505_shop_category
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\Category;


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
            'path_hash' => $this->string(32)->null(),
            'image' => $this->string(50)->null(),
            'name_ru' => $this->string(255)->null(),
            'name_uk' => $this->string(255)->null(),
            'description_ru' => $this->text()->null(),
            'description_uk' => $this->text()->null(),
            'meta_title_ru' => $this->string(255)->null(),
            'meta_title_uk' => $this->string(255)->null(),
            'meta_description_ru' => $this->text()->null(),
            'meta_description_uk' => $this->text()->null(),
            'h1_ru' => $this->string(255)->null(),
            'h1_uk' => $this->string(255)->null(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'use_seo_parents'=>$this->boolean()->defaultValue(false),
            'switch' => $this->boolean()->defaultValue(true),
        ]);


        $this->createIndex('lft', Category::tableName(), 'lft');
        $this->createIndex('rgt', Category::tableName(), 'rgt');
        $this->createIndex('depth', Category::tableName(), 'depth');
        $this->createIndex('full_path', Category::tableName(), 'full_path');
        $this->createIndex('switch', Category::tableName(), 'switch');

    }

    public function down()
    {
        $this->dropTable(Category::tableName());
    }

}
