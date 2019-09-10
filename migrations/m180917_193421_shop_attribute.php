<?php

namespace panix\mod\shop\migrations;
/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193421_shop_attribute
 */

use yii\db\Schema;
use panix\engine\db\Migration;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\translate\AttributeTranslate;

class m180917_193421_shop_attribute extends Migration
{

    public function up()
    {

        $this->createTable(Attribute::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'group_id' => $this->integer(11)->null()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'type' => $this->string(10)->notNull(),
            'display_on_front' => $this->boolean()->defaultValue(1),
            'use_in_filter' => $this->boolean()->null(),
            'use_in_variants' => $this->boolean()->null(),
            'use_in_compare' => $this->boolean()->defaultValue(0),
            'select_many' => $this->boolean()->null(),
            'ordern' => $this->integer(11)->null(),
            'required' => $this->boolean()->null(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);


        $this->createTable(AttributeTranslate::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'object_id' => $this->integer()->unsigned(),
            'language_id' => $this->tinyInteger()->unsigned(),
            'title' => $this->string(255)->notNull(),
            'abbreviation' => $this->string(25),
            'hint' => $this->text()->notNull(),
        ], $this->tableOptions);


        $this->createIndex('name', Attribute::tableName(), 'name');
        $this->createIndex('use_in_filter', Attribute::tableName(), 'use_in_filter');
        $this->createIndex('display_on_front', Attribute::tableName(), 'display_on_front');
        $this->createIndex('ordern', Attribute::tableName(), 'ordern');
        $this->createIndex('use_in_variants', Attribute::tableName(), 'use_in_variants');
        $this->createIndex('use_in_compare', Attribute::tableName(), 'use_in_compare');

        $this->createIndex('object_id', AttributeTranslate::tableName(), 'object_id');
        $this->createIndex('language_id', AttributeTranslate::tableName(), 'language_id');
    }

    public function down()
    {
        $this->dropTable(Attribute::tableName());
        $this->dropTable(AttributeTranslate::tableName());
    }

}
