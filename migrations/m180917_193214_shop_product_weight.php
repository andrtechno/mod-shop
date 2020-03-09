<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193214_shop_product_weight
 */

use panix\mod\shop\models\Product;
use panix\mod\shop\models\translate\ProductTranslate;
use panix\engine\db\Migration;

class m180917_193214_shop_product_weight extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('{{%shop_product_weight}}', [
            'id' => $this->primaryKey()->unsigned(),
            'value' => $this->decimal(15,8),
        ]);
        $this->createTable('{{%shop_product_weight_translate}}', [
            'id' => $this->primaryKey()->unsigned(),
            'object_id' => $this->integer()->unsigned(),
            'language_id' => $this->tinyInteger()->unsigned(),
            'title' => $this->string(32)->notNull(),
            'unit' => $this->string(4)->notNull(),
        ]);

        $this->createIndex('object_id', '{{%shop_product_weight_translate}}', 'object_id');
        $this->createIndex('language_id', '{{%shop_product_weight_translate}}', 'language_id');
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%shop_product_weight}}');
        $this->dropTable('{{%shop_product_weight_translate}}');
    }

}
