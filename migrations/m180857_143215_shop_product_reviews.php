<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180857_143215_shop_product_reviews
 */

use panix\mod\shop\models\ProductReviews;
use panix\engine\db\Migration;

class m180857_143215_shop_product_reviews extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable(ProductReviews::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'tree' => $this->smallInteger()->null()->unsigned(),
            'lft' => $this->smallInteger()->null()->unsigned(),
            'rgt' => $this->smallInteger()->null()->unsigned(),
            'depth' => $this->smallInteger()->null()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'product_id' => $this->integer()->unsigned(),
            'user_name' => $this->string(50),
            'user_email' => $this->string(50),
            'user_agent' => $this->string(255),
            'text' => $this->text()->null(),
            'rate' => $this->smallInteger()->null(),
            'status' => $this->tinyInteger(1)->defaultValue(0)->null(),
            'ip_create' => $this->string(100),
            'created_at' => $this->integer(11)->null(),
            'updated_at' => $this->integer(11)->null(),
        ]);
        $this->createIndex('lft', ProductReviews::tableName(), 'lft');
        $this->createIndex('tree', ProductReviews::tableName(), 'tree');
        $this->createIndex('rgt', ProductReviews::tableName(), 'rgt');
        $this->createIndex('depth', ProductReviews::tableName(), 'depth');
        $this->createIndex('product_id', ProductReviews::tableName(), 'product_id');
        $this->createIndex('user_id', ProductReviews::tableName(), 'user_id');
        $this->createIndex('status', ProductReviews::tableName(), 'status');




    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable(ProductReviews::tableName());
    }

}
