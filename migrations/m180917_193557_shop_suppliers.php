<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193557_shop_suppliers
 */
use yii\db\Schema;
use panix\engine\db\Migration;

class m180917_193557_shop_suppliers extends Migration
{

    public function up()
    {

        $this->createTable('{{%shop_suppliers}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->null()->defaultValue(null),
            'phone' => $this->string(255)->null()->defaultValue(null),
            'email' => $this->string(255)->null()->defaultValue(null),
            'address' => $this->text()->null()->defaultValue(null),
        ], $this->tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%shop_suppliers}}');

    }

}
