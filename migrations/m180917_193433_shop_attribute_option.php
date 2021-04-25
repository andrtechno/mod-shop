<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193433_shop_attribute_option
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\AttributeOption;


class m180917_193433_shop_attribute_option extends Migration
{

    public function up()
    {
        $this->createTable(AttributeOption::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'attribute_id' => $this->integer()->null(),
            'data' => $this->text()->null(),
            'value' => $this->string(255)->null(),
            'value_uk' => $this->string(255)->null(),
            'value_en' => $this->string(255)->null(),
            'ordern' => $this->integer()->unsigned(),
        ]);


        $this->createIndex('attribute_id', AttributeOption::tableName(), 'attribute_id');
        $this->createIndex('ordern', AttributeOption::tableName(), 'ordern', false);

    }

    public function down()
    {
        $this->dropTable(AttributeOption::tableName());
    }

}
