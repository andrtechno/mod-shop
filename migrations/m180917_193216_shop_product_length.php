<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193216_shop_product_length
 */

use Yii;
use panix\engine\db\Migration;

class m180917_193216_shop_product_length extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('{{%shop_product_length}}', [
            'id' => $this->primaryKey()->unsigned(),
            'value' => $this->decimal(15, 4),
        ]);
        $this->createTable('{{%shop_product_length_translate}}', [
            'id' => $this->primaryKey()->unsigned(),
            'object_id' => $this->integer()->unsigned(),
            'language_id' => $this->tinyInteger()->unsigned(),
            'title' => $this->string(32)->notNull(),
            'unit' => $this->string(4)->notNull(),
        ]);

        $this->createIndex('object_id', '{{%shop_product_length_translate}}', 'object_id');
        $this->createIndex('language_id', '{{%shop_product_length_translate}}', 'language_id');


        $list = [
            ['name' => 'Centimeter', 'unit' => 'cm', 'value' => 1],
            ['name' => 'Millimeter', 'unit' => 'mm', 'value' => 10],
            ['name' => 'Inch', 'unit' => 'in', 'value' => 0.3937],
        ];
        $id=1;
        foreach ($list as $key => $data) {
            $this->batchInsert('{{%shop_product_length}}', ['value'], [
                [$data['value']]
            ]);

            foreach (Yii::$app->languageManager->getLanguages(false) as $lang) {
                $this->batchInsert('{{%shop_product_length_translate}}', ['object_id', 'language_id', 'title', 'unit'], [
                    [$id, $lang['id'], $data['name'], $data['unit']]
                ]);
            }
            $id++;
        }


    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%shop_product_length}}');
        $this->dropTable('{{%shop_product_length_translate}}');
    }

}
