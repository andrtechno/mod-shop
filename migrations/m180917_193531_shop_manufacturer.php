<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * 
 * Class m180917_193531_shop_manufacturer
 */
use yii\db\Schema;
use panix\engine\db\Migration;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\translate\ManufacturerTranslate;

class m180917_193531_shop_manufacturer extends Migration {

    public function up()
    {
        $this->createTable(Manufacturer::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'cat_id' => $this->integer()->null(),
            'image' => $this->string()->null()->defaultValue(null),
            'seo_alias' => $this->string(11)->notNull()->defaultValue(null),
            'switch' => $this->boolean(11)->notNull()->defaultValue(null),
            'ordern' => $this->integer(),
        ], $this->tableOptions);



        $this->createTable(ManufacturerTranslate::tableName(), [
            'id' => $this->primaryKey(),
            'object_id' => $this->integer(),
            'language_id' => $this->integer(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text()->null()->defaultValue(null)
        ], $this->tableOptions);


        $this->createIndex('switch', Manufacturer::tableName(), 'switch');
        $this->createIndex('ordern', Manufacturer::tableName(), 'ordern');
        $this->createIndex('seo_alias', Manufacturer::tableName(), 'seo_alias');

        $this->createIndex('object_id', ManufacturerTranslate::tableName(), 'object_id');
        $this->createIndex('language_id', ManufacturerTranslate::tableName(), 'language_id');

    }

    public function down()
    {
        $this->dropTable(Manufacturer::tableName());
        $this->dropTable(ManufacturerTranslate::tableName());
    }
}
