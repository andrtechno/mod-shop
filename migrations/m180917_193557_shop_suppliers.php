<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193557_shop_suppliers
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\Supplier;

class m180917_193557_shop_suppliers extends Migration
{

    public function up()
    {

        $this->createTable(Supplier::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->null()->defaultValue(null),
            'phone' => $this->string(255)->null()->defaultValue(null),
            'email' => $this->string(255)->null()->defaultValue(null),
            'address' => $this->text()->null()->defaultValue(null),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    public function down()
    {
        $this->dropTable(Supplier::tableName());

    }

}
