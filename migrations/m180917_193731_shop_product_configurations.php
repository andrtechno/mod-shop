<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * 
 * Class m180917_193731_shop_product_configurations
 */
use yii\db\Schema;
use panix\engine\db\Migration;

class m180917_193731_shop_product_configurations extends Migration {

    public function up()
    {
        $this->createTable('{{%shop__product_configurations}}', [
            'product_id' => $this->integer(11)->notNull()->unsigned(),
            'configurable_id' => $this->integer(11)->notNull()->unsigned(),
        ], $this->tableOptions);

        $this->addCommentOnColumn('{{%shop__product_configurations}}', 'product_id', 'Saves relations beetwen product and configurations');

        $this->createIndex('idsunique', '{{%shop__product_configurations}}', ['product_id','configurable_id'], 1);
    }

    public function down()
    {
        $this->dropTable('{{%shop__product_configurations}}');
    }

}
