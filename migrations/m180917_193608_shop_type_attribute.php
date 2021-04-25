<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193608_shop_type_attribute
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\TypeAttribute;

class m180917_193608_shop_type_attribute extends Migration
{

    public function up()
    {
        $this->createTable(TypeAttribute::tableName(), [
            'type_id' => $this->smallInteger()->notNull()->unsigned(),
            'attribute_id' => $this->integer()->notNull()->unsigned(),
        ]);

        if ($this->db->driverName != "sqlite" || $this->db->driverName != 'pgsql') {
            $this->addPrimaryKey('{{%pk_shop__type_attribute}}', TypeAttribute::tableName(), ['type_id', 'attribute_id']);
        }
        $this->createIndex('attribute_id', TypeAttribute::tableName(), 'attribute_id');

    }

    public function down()
    {
        $this->dropTable(TypeAttribute::tableName());
    }

}
