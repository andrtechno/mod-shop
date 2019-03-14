<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * 
 * Class m180917_193517_shop_currency
 */
use yii\db\Schema;
use panix\engine\db\Migration;
use panix\mod\shop\models\Currency;

class m180917_193517_shop_currency extends Migration {

    public function up()
    {
        $this->createTable(Currency::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->null(),
            'iso' => $this->string(10)->null()->defaultValue(null),
            'symbol' => $this->string(10)->notNull()->defaultValue(null),
            'rate' => $this->money(10,2)->notNull()->defaultValue(null),
            'rate_old' => $this->money(10,2),
            'penny' => $this->string(5)->null()->defaultValue(null),
            'separator_hundredth' => $this->string(5)->null()->defaultValue(null),
            'separator_thousandth' => $this->string(5)->null()->defaultValue(null),
            'is_main' => $this->boolean()->defaultValue(0),
            'is_default' => $this->boolean()->defaultValue(0),
            'ordern' => $this->integer(),
        ], $this->tableOptions);




        $this->createIndex('is_main', Currency::tableName(), 'is_main');
        $this->createIndex('is_default', Currency::tableName(), 'is_default');
        $this->createIndex('ordern', Currency::tableName(), 'ordern');


    }

    public function down()
    {
        $this->dropTable(Currency::tableName());

    }

}
