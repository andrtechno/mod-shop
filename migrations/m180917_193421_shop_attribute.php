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
use panix\mod\shop\models\AttributeTranslate;

class m180917_193421_shop_attribute extends Migration
{

    public function up()
    {

        $this->createTable(Attribute::tableName(), [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer(11),
            'name' => $this->string(255)->notNull(),
            'type' => $this->string(10)->notNull(),
            'display_on_front' => $this->boolean()->defaultValue(1),
            'use_in_filter' => $this->boolean()->defaultValue(null),
            'use_in_variants' => $this->boolean()->defaultValue(null),
            'use_in_compare' => $this->boolean()->defaultValue(0),
            'select_many' => $this->boolean()->defaultValue(null),
            'ordern' => $this->integer(11)->defaultValue(null),
            'required' => $this->boolean()->defaultValue(null),
        ], $this->tableOptions);


        $this->createTable(AttributeTranslate::tableName(), [
            'id' => $this->primaryKey(),
            'object_id' => $this->integer(),
            'language_id' => $this->integer(),
            'title' => $this->string(255)->notNull(),
            'abbreviation' => $this->string(25),
            'hint' => $this->text()->notNull(),
        ], $this->tableOptions);


        $this->createIndex('name', Attribute::tableName(), 'name', 0);
        $this->createIndex('use_in_filter', Attribute::tableName(), 'use_in_filter', 0);
        $this->createIndex('display_on_front', Attribute::tableName(), 'display_on_front', 0);
        $this->createIndex('ordern', Attribute::tableName(), 'ordern', 0);
        $this->createIndex('use_in_variants', Attribute::tableName(), 'use_in_variants', 0);
        $this->createIndex('use_in_compare', Attribute::tableName(), 'use_in_compare', 0);

        $this->createIndex('object_id', AttributeTranslate::tableName(), 'object_id', 0);
        $this->createIndex('language_id', AttributeTranslate::tableName(), 'language_id', 0);
    }

    public function down()
    {
        $this->dropTable(Attribute::tableName());
        $this->dropTable(AttributeTranslate::tableName());
    }

}
