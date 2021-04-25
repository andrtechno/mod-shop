<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193720_shop_product_configurable_attributes
 */

use panix\engine\db\Migration;

class m180917_193720_shop_product_configurable_attributes extends Migration
{

    public function up()
    {
        $this->createTable('{{%shop__product_configurable_attributes}}', [
            'product_id' => $this->integer(11)->notNull()->unsigned(),
            'attribute_id' => $this->integer(11)->notNull()->unsigned(),
        ]);

        $this->createIndex('product_attribute', '{{%shop__product_configurable_attributes}}', ['product_id', 'attribute_id'], true);
    }

    public function down()
    {
        $this->dropTable('{{%shop__product_configurable_attributes}}');
    }

}
