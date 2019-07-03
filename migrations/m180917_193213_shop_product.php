<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193213_shop_product
 */
use panix\mod\shop\models\Product;
use panix\mod\shop\models\translate\ProductTranslate;
use panix\engine\db\Migration;

class m180917_193213_shop_product extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable(Product::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'manufacturer_id' => $this->integer()->unsigned(),
            'category_id' => $this->integer()->unsigned(),
            'main_category_id' => $this->integer()->unsigned(),
            'type_id' => $this->smallInteger()->unsigned(),
            'supplier_id' => $this->integer()->unsigned(),
            'currency_id' => $this->smallInteger()->unsigned(),
            'use_configurations' => $this->boolean()->defaultValue(0),
            'slug' => $this->string(255)->notNull(),
            'price' => $this->money(10,2),
            'unit' => $this->tinyInteger(1)->unsigned()->defaultValue(1),
            'max_price' => $this->money(10,2),
            'price_purchase' => $this->money(10,2),
            'lbl' => $this->boolean()->null(),
            'sku' => $this->string(50),
            'quantity' => $this->integer()->defaultValue(1),
            'archive' => $this->boolean()->defaultValue(0),
            'availability' => $this->smallInteger(2)->defaultValue(0),
            'auto_decrease_quantity' => $this->smallInteger(2)->defaultValue(0),
            'views' => $this->integer()->defaultValue(0),
            'added_to_cart_count' => $this->integer()->defaultValue(0),
            'votes' => $this->integer()->defaultValue(0),
            'rating' => $this->integer()->defaultValue(0),
            'discount' => $this->string(50),
            'markup' => $this->string(50),
            'video' => $this->text(),
            'enable_comments' => $this->tinyInteger(1)->defaultValue(1)->unsigned(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'switch' => $this->boolean()->defaultValue(1),
            'ordern' => $this->integer(),
        ], $this->tableOptions);

        $this->createTable(ProductTranslate::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'object_id' => $this->integer()->unsigned(),
            'language_id' => $this->tinyInteger()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'short_description' => $this->text()->null(),
            'full_description' => $this->text()->null(),
        ], $this->tableOptions);

        $this->addCommentOnColumn(Product::tableName(), 'price_purchase', 'Цена закупки');

        $this->createIndex('user_id', Product::tableName(), 'user_id');
        $this->createIndex('manufacturer_id', Product::tableName(), 'manufacturer_id');
        $this->createIndex('category_id', Product::tableName(), 'category_id');
        $this->createIndex('type_id', Product::tableName(), 'type_id');
        $this->createIndex('supplier_id', Product::tableName(), 'supplier_id');
        $this->createIndex('currency_id', Product::tableName(), 'currency_id');
        $this->createIndex('slug', Product::tableName(), 'slug');
        $this->createIndex('price', Product::tableName(), 'price');
        $this->createIndex('max_price', Product::tableName(), 'max_price');
        $this->createIndex('switch', Product::tableName(), 'switch');
        $this->createIndex('created_at', Product::tableName(), 'created_at');
        $this->createIndex('views', Product::tableName(), 'views', 0);
        $this->createIndex('ordern', Product::tableName(), 'ordern', 0);
        $this->createIndex('main_category_id', Product::tableName(), 'main_category_id');


        $this->createIndex('object_id', ProductTranslate::tableName(), 'object_id');
        $this->createIndex('language_id', ProductTranslate::tableName(), 'language_id');

    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable(Product::tableName());
        $this->dropTable(ProductTranslate::tableName());
    }

}
