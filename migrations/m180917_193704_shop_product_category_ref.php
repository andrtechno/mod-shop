<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * 
 * Class m180917_193704_shop_product_category_ref
 */
use yii\db\Schema;
use panix\engine\db\Migration;

class m180917_193704_shop_product_category_ref extends Migration {

    public function up()
    {
        $this->createTable('{{%shop__product_category_ref}}', [
            'id' => $this->primaryKey()->unsigned(),
            'product' => $this->integer()->notNull(),
            'category' => $this->integer()->notNull(),
            'is_main' => $this->boolean()->null(),
            'switch' => $this->boolean()->defaultValue(1),
        ], $this->tableOptions);

        $this->createIndex('product', '{{%shop__product_category_ref}}', 'product', 0);
        $this->createIndex('category', '{{%shop__product_category_ref}}', 'category', 0);
        $this->createIndex('switch', '{{%shop__product_category_ref}}', 'switch', 0);
        $this->createIndex('is_main', '{{%shop__product_category_ref}}', 'is_main', 0);
    }

    public function down()
    {
        $this->dropTable('{{%shop__product_category_ref}}');
    }

}
