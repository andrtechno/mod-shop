<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193811_shop_related_product
 */
use yii\db\Schema;
use panix\engine\db\Migration;

class m180917_193811_shop_related_product extends Migration
{

    public function up()
    {
        $this->createTable('{{%shop__related_product}}', [
            'id' => $this->primaryKey()->unsigned(),
            'product_id' => $this->integer(11)->null()->unsigned(),
            'related_id' => $this->integer(11)->null()->unsigned(),
        ], $this->tableOptions);

        $this->createIndex('product_id', '{{%shop__related_product}}', 'product_id');
        $this->createIndex('related_id', '{{%shop__related_product}}', 'related_id');
    }

    public function down()
    {
        $this->dropTable('{{%shop__related_product}}');
    }
}
