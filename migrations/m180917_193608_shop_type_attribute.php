<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193608_shop_type_attribute
 */
use yii\db\Schema;
use panix\engine\db\Migration;
use panix\mod\shop\models\TypeAttribute;

class m180917_193608_shop_type_attribute extends Migration
{

    public function up()
    {
        $this->createTable(TypeAttribute::tableName(), [
            'type_id' => $this->smallInteger()->notNull()->unsigned(),
            'attribute_id' => $this->integer()->notNull()->unsigned(),
        ], $this->tableOptions);


        $this->addPrimaryKey('{{%pk_shop_type_attribute}}', TypeAttribute::tableName(), ['type_id', 'attribute_id']);
        $this->createIndex('attribute_id', TypeAttribute::tableName(), 'attribute_id', 0);

    }

    public function down()
    {
        $this->dropTable(TypeAttribute::tableName());
    }

}
