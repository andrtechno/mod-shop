<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193506_shop_category_filter
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\CategoryFilter;


class m180917_193506_shop_category_filter extends Migration
{

    public function up()
    {
        $this->createTable(CategoryFilter::tableName(), [
            'category_id' => $this->integer()->unsigned()->null(),
            'option_id' => $this->integer()->unsigned()->null(),
        ]);

        $this->addPrimaryKey('{{%shop__category_filter_pk}}', CategoryFilter::tableName(), ['category_id', 'option_id']);
    }

    public function down()
    {
        $this->dropTable(CategoryFilter::tableName());
    }

}
