<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180857_193215_shop_kit
 */

use panix\mod\shop\models\Kit;
use panix\engine\db\Migration;

class m180857_193215_shop_kit extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable(Kit::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'owner_id' => $this->integer()->unsigned(),
            'product_id' => $this->integer()->unsigned(),
            'price' => $this->money(10, 2),
            'from' => $this->tinyInteger()->unsigned(),
        ]);
        $this->createIndex('owner_id', Kit::tableName(), 'owner_id');
        $this->createIndex('product_id', Kit::tableName(), 'product_id');






    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable(Kit::tableName());
    }

}
