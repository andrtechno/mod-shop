<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193531_shop_manufacturer
 */
use Yii;
use panix\engine\CMS;
use panix\engine\db\Migration;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\translate\ManufacturerTranslate;

class m180917_193531_shop_manufacturer extends Migration
{

    public function up()
    {
        $this->createTable(Manufacturer::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'cat_id' => $this->integer()->null(),
            'image' => $this->string()->null()->defaultValue(null),
            'slug' => $this->string(11)->notNull()->defaultValue(null),
            'switch' => $this->boolean()->defaultValue(1),
            'ordern' => $this->integer()->unsigned(),
        ], $this->tableOptions);


        $this->createTable(ManufacturerTranslate::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'object_id' => $this->integer()->unsigned(),
            'language_id' => $this->tinyInteger()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text()->null()->defaultValue(null)
        ], $this->tableOptions);


        $this->createIndex('switch', Manufacturer::tableName(), 'switch');
        $this->createIndex('ordern', Manufacturer::tableName(), 'ordern');
        $this->createIndex('slug', Manufacturer::tableName(), 'slug');
        $this->createIndex('cat_id', Manufacturer::tableName(), 'cat_id');

        $this->createIndex('object_id', ManufacturerTranslate::tableName(), 'object_id');
        $this->createIndex('language_id', ManufacturerTranslate::tableName(), 'language_id');

        $brands = ['Apple', 'Nokia', 'Samsung', 'LG', 'Philips'];
        foreach ($brands as $key => $brand) {
            $id = $key + 1;
            $this->batchInsert(Manufacturer::tableName(), ['cat_id', 'slug', 'switch', 'ordern'], [
                [NULL, CMS::slug($brand), 1, $id]
            ]);

            foreach (Yii::$app->languageManager->getLanguages(false) as $lang) {
                $this->batchInsert(ManufacturerTranslate::tableName(), ['object_id', 'language_id', 'name', 'description'], [
                    [$id, $lang['id'], $brand, '']
                ]);
            }
        }


    }

    public function down()
    {
        $this->dropTable(Manufacturer::tableName());
        $this->dropTable(ManufacturerTranslate::tableName());
    }
}
