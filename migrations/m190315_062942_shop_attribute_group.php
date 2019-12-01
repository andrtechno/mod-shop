<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 *
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * @link http://pixelion.com.ua PIXELION CMS
 *
 * Class m190315_062942_shop_attribute_group
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\AttributeGroup;
use panix\mod\shop\models\translate\AttributeGroupTranslate;

class m190315_062942_shop_attribute_group extends Migration
{

    public function up()
    {
        $this->createTable(AttributeGroup::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->null()->defaultValue(null),
            'switch' => $this->boolean()->notNull()->defaultValue(null),
            'ordern' => $this->integer()->unsigned(),
        ]);


        $this->createTable(AttributeGroupTranslate::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'object_id' => $this->integer()->unsigned(),
            'language_id' => $this->tinyInteger()->unsigned(),
            'name' => $this->string(255)->notNull(),
        ]);


        $this->createIndex('switch', AttributeGroup::tableName(), 'switch');
        $this->createIndex('ordern', AttributeGroup::tableName(), 'ordern');

        $this->createIndex('object_id', AttributeGroupTranslate::tableName(), 'object_id');
        $this->createIndex('language_id', AttributeGroupTranslate::tableName(), 'language_id');

    }

    public function down()
    {
        $this->dropTable(AttributeGroup::tableName());
        $this->dropTable(AttributeGroupTranslate::tableName());
    }

}
