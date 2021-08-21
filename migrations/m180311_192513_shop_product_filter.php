<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180311_192513_shop_product_filter
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\ProductFilter;

class m180311_192513_shop_product_filter extends Migration
{

    public function up()
    {
        $this->createTable(ProductFilter::tableName(), [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->unsigned()->null(),
            'option_id' => $this->integer()->unsigned()->null(),
        ]);

       // $this->addPrimaryKey('{{%shop__product_filter_pk}}', ProductFilter::tableName(), ['product_id', 'option_id']);
        /*$this->addForeignKey(
            '{{%shop__product_filter_product_id_fk}}',
            ProductFilter::tableName(),
            'product_id',
            \panix\mod\shop\models\Product::tableName(),
            'id',
            'CASCADE'
        );



        $this->addForeignKey(
            '{{%shop__product_filter_option_id_fk}}',
            ProductFilter::tableName(),
            'option_id',
            \panix\mod\shop\models\AttributeOption::tableName(),
            'id',
            'CASCADE'
        );*/
    }

    public function down()
    {
        $this->dropTable(ProductFilter::tableName());
    }

}
