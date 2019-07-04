<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193746_shop_product_type
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\ProductType;

class m180917_193746_shop_product_type extends Migration
{

    public function up()
    {
        $this->createTable(ProductType::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->null(),
            'categories_preset' => $this->text()->null()->defaultValue(null),
            'main_category' => $this->integer(11)->null()->defaultValue(0),
        ], $this->tableOptions);

    }

    public function down()
    {
        $this->dropTable(ProductType::tableName());
    }

}
