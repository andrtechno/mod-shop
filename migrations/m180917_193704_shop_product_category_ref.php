<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193704_shop_product_category_ref
 */
use yii\db\Schema;
use panix\engine\db\Migration;
use panix\mod\shop\models\ProductCategoryRef;

class m180917_193704_shop_product_category_ref extends Migration
{

    public function up()
    {
        $this->createTable(ProductCategoryRef::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'product' => $this->integer()->notNull()->unsigned(),
            'category' => $this->integer()->notNull()->unsigned(),
            'is_main' => $this->boolean()->defaultValue(false)->notNull(),
            'switch' => $this->boolean()->defaultValue(true)->notNull(),
        ]);

        $this->createIndex('product', ProductCategoryRef::tableName(), 'product');
        $this->createIndex('category', ProductCategoryRef::tableName(), 'category');
        $this->createIndex('switch', ProductCategoryRef::tableName(), 'switch');
        $this->createIndex('is_main', ProductCategoryRef::tableName(), 'is_main');
    }

    public function down()
    {
        $this->dropTable(ProductCategoryRef::tableName());
    }

}
