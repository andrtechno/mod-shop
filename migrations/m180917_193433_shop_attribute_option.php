<?php

namespace panix\mod\shop\migrations;
/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193433_shop_attribute_option
 */
use yii\db\Schema;
use panix\engine\db\Migration;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\translate\AttributeOptionTranslate;

class m180917_193433_shop_attribute_option extends Migration
{

    public function up()
    {
        $this->createTable(AttributeOption::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'attribute_id' => $this->integer()->null(),
            'data' => $this->text()->null(),
            'value' => $this->string(255)->notNull(),
            'value_ua' => $this->string(255)->notNull(),
            'ordern' => $this->integer()->unsigned(),
        ]);


        $this->createIndex('attribute_id', AttributeOption::tableName(), 'attribute_id');
        $this->createIndex('ordern', AttributeOption::tableName(), 'ordern', 0);

    }

    public function down()
    {
        $this->dropTable(AttributeOption::tableName());
    }

}
