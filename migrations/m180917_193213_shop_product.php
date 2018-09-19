<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193213_shop_product
 */
use panix\mod\shop\models\Product;
use panix\mod\shop\models\translate\ProductTranslate;
use yii\db\Schema;
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
            'type_id' => $this->smallInteger()->unsigned(),
            'supplier_id' => $this->integer()->unsigned(),
            'currency_id' => $this->smallInteger()->unsigned(),
            'use_configurations' => $this->boolean()->defaultValue(0),
            'seo_alias' => $this->string(255)->notNull(),
            'price' => $this->money(10,2),
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
            'date_create' => $this->timestamp()->null(),
            'date_update' => $this->timestamp(),
            'switch' => $this->boolean()->defaultValue(1),
            'ordern' => $this->integer(),
        ], $this->tableOptions);

        $this->createTable(ProductTranslate::tableName(), [
            'id' => $this->primaryKey(),
            'object_id' => $this->integer(),
            'language_id' => $this->integer(),
            'name' => $this->string(255)->notNull(),
            'short_description' => $this->text()->null(),
            'full_description' => $this->text()->null(),
        ], $this->tableOptions);

        $this->addCommentOnColumn(Product::tableName(), 'price_purchase', 'Цена закупки');

        $this->createIndex('user_id', Product::tableName(), 'user_id', 0);
        $this->createIndex('manufacturer_id', Product::tableName(), 'manufacturer_id', 0);
        $this->createIndex('category_id', Product::tableName(), 'category_id', 0);
        $this->createIndex('type_id', Product::tableName(), 'type_id', 0);
        $this->createIndex('supplier_id', Product::tableName(), 'supplier_id', 0);
        $this->createIndex('currency_id', Product::tableName(), 'currency_id', 0);
        $this->createIndex('seo_alias', Product::tableName(), 'seo_alias', 0);
        $this->createIndex('price', Product::tableName(), 'price', 0);
        $this->createIndex('max_price', Product::tableName(), 'max_price', 0);
        $this->createIndex('switch', Product::tableName(), 'switch', 0);
        $this->createIndex('date_create', Product::tableName(), 'date_create', 0);
        $this->createIndex('views', Product::tableName(), 'views', 0);
        $this->createIndex('ordern', Product::tableName(), 'ordern', 0);


        $this->createIndex('object_id', ProductTranslate::tableName(), 'object_id', 0);
        $this->createIndex('language_id', ProductTranslate::tableName(), 'language_id', 0);

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
