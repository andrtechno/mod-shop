<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193421_shop_attribute
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\Attribute;

class m180917_193421_shop_attribute extends Migration
{

    public function up()
    {

        $this->createTable(Attribute::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'group_id' => $this->integer(11)->null()->unsigned(),
            'name' => $this->string(50)->notNull(),
            'type' => $this->smallInteger(2)->notNull(),
            'display_on_front' => $this->boolean()->defaultValue(true),
            'display_on_list' => $this->boolean()->defaultValue(false),
            'display_on_cart' => $this->boolean()->defaultValue(false),
            'display_on_grid' => $this->boolean()->defaultValue(false),
            'display_on_pdf' => $this->boolean()->defaultValue(false),
            'use_in_filter' => $this->boolean()->defaultValue(false),
            'use_in_variants' => $this->boolean()->defaultValue(false),
            'use_in_compare' => $this->boolean()->defaultValue(false),
            'select_many' => $this->boolean()->defaultValue(false),
            'required' => $this->boolean()->defaultValue(false),
            //'title' => $this->string(255)->null(),
            'title_uk' => $this->string(255)->null(),
            'title_ru' => $this->string(255)->null(),
            //'abbreviation' => $this->string(25)->null(),
            'abbreviation_uk' => $this->string(25)->null(),
            'abbreviation_ru' => $this->string(25)->null(),
            //'hint' => $this->text()->null(),
            'hint_uk' => $this->text()->null(),
            'hint_ru' => $this->text()->null(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'sort' => $this->tinyInteger(1)->defaultValue(NULL),
            'switch' => $this->boolean()->defaultValue(true),
            'ordern' => $this->integer(11)->unsigned(),
        ]);


        $this->createIndex('name', Attribute::tableName(), 'name');
        $this->createIndex('use_in_filter', Attribute::tableName(), 'use_in_filter');
        $this->createIndex('display_on_list', Attribute::tableName(), 'display_on_list');
        $this->createIndex('display_on_front', Attribute::tableName(), 'display_on_front');
        $this->createIndex('display_on_cart', Attribute::tableName(), 'display_on_cart');
        $this->createIndex('display_on_grid', Attribute::tableName(), 'display_on_grid');
        $this->createIndex('display_on_pdf', Attribute::tableName(), 'display_on_pdf');
        $this->createIndex('ordern', Attribute::tableName(), 'ordern');
        $this->createIndex('use_in_variants', Attribute::tableName(), 'use_in_variants');
        $this->createIndex('use_in_compare', Attribute::tableName(), 'use_in_compare');
        $this->createIndex('group_id', Attribute::tableName(), 'group_id');



    }

    public function down()
    {
        $this->dropTable(Attribute::tableName());
    }

}
