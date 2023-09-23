<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193213_shop_product
 */

use panix\mod\shop\models\Product;
use panix\mod\shop\models\translate\ProductTranslate;
use panix\engine\db\Migration;

class m180917_193213_shop_product extends Migration
{
    public $settingsForm = 'panix\mod\shop\models\forms\SettingsForm';

    /**
     * {@inheritdoc}
     */
    public function up()
    {

        $fields = [];
        $fields['id'] = $this->primaryKey()->unsigned();
        $fields['user_id'] = $this->integer()->unsigned();
        $fields['brand_id'] = $this->integer()->unsigned();
        $fields['category_id'] = $this->integer()->unsigned();
        $fields['main_category_id'] = $this->integer()->unsigned();
        $fields['type_id'] = $this->smallInteger()->unsigned();
        $fields['supplier_id'] = $this->integer()->unsigned();
        $fields['currency_id'] = $this->smallInteger()->unsigned();
        $fields['weight_class_id'] = $this->integer()->null();
        $fields['length_class_id'] = $this->integer()->null();

        if($this->db->getDriverName() == 'pgsql'){
            $fields['name'] = $this->string(255)->null();
        }else{
            $fields['name_ru'] = $this->string(255)->null();
            $fields['name_uk'] = $this->string(255)->null();
        }
        if($this->db->getDriverName() == 'pgsql'){
            $fields['short_description'] = $this->text()->null();
            $fields['full_description'] = $this->text()->null();
        }else{
            $fields['short_description_ru'] = $this->text()->null();
            $fields['short_description_uk'] = $this->text()->null();
            $fields['full_description_ru'] = $this->text()->null();
            $fields['full_description_uk'] = $this->text()->null();
        }

        $fields['image'] = $this->string(50)->null();
        $fields['use_configurations'] = $this->boolean()->defaultValue(false);
        $fields['slug'] = $this->string(255)->null();
        $fields['price'] = $this->money(10, 2);
        $fields['unit'] = $this->tinyInteger(1)->unsigned()->defaultValue(1);
        $fields['max_price'] = $this->money(10, 2);
        $fields['price_purchase'] = $this->money(10, 2)->comment('Цена закупки');
        $fields['is_condition'] = $this->tinyInteger(1)->defaultValue(0)->comment('Состояние');
        $fields['label'] = $this->string(50)->null();
        $fields['sku'] = $this->string(50);
        $fields['weight'] = $this->decimal(15, 4);
        $fields['length'] = $this->decimal(15, 4);
        $fields['width'] = $this->decimal(15, 4);
        $fields['height'] = $this->decimal(15, 4);
        $fields['quantity'] = $this->smallInteger(2)->unsigned()->defaultValue(1);
        $fields['quantity_min'] = $this->smallInteger(2)->unsigned()->defaultValue(1);
        $fields['archive'] = $this->boolean()->defaultValue(false);
        $fields['availability'] = $this->tinyInteger(1)->unsigned()->defaultValue(1);
        $fields['auto_decrease_quantity'] = $this->smallInteger(2)->unsigned()->defaultValue(0);
        $fields['views'] = $this->integer()->unsigned()->defaultValue(0);
        $fields['added_to_cart_count'] = $this->integer()->defaultValue(0);
        $fields['added_to_cart_date'] = $this->integer()->null();
        $fields['votes'] = $this->integer()->unsigned()->defaultValue(0);
        $fields['rating'] = $this->integer()->unsigned()->defaultValue(0);
        $fields['discount'] = $this->string(5)->comment('Скидка');
        $fields['markup'] = $this->string(5)->comment('Наценка');
        $fields['video'] = $this->text();
        $fields['in_box'] = $this->integer()->unsigned()->defaultValue(1);
        $fields['enable_comments'] = $this->tinyInteger(1)->defaultValue(1)->unsigned();
        $fields['created_at'] = $this->integer();
        $fields['updated_at'] = $this->integer();
        $fields['switch'] = $this->boolean()->defaultValue(true)->notNull();
        $fields['ordern'] = $this->integer()->unsigned();
        if($this->db->getDriverName() == 'pgsql'){
            $fields['options'] = $this->json();
        }
        $this->createTable(Product::tableName(), $fields);


        $this->createIndex('user_id', Product::tableName(), 'user_id');
        $this->createIndex('availability', Product::tableName(), 'availability');
        $this->createIndex('brand_id', Product::tableName(), 'brand_id');
        $this->createIndex('category_id', Product::tableName(), 'category_id');
        $this->createIndex('type_id', Product::tableName(), 'type_id');
        $this->createIndex('supplier_id', Product::tableName(), 'supplier_id');
        $this->createIndex('currency_id', Product::tableName(), 'currency_id');
        //$this->createIndex('slug', Product::tableName(), 'slug'); //не используем вроде.
        $this->createIndex('price', Product::tableName(), 'price');
        $this->createIndex('max_price', Product::tableName(), 'max_price');
        $this->createIndex('switch', Product::tableName(), 'switch');
        $this->createIndex('created_at', Product::tableName(), 'created_at');
        $this->createIndex('ordern', Product::tableName(), 'ordern');
        $this->createIndex('main_category_id', Product::tableName(), 'main_category_id');
        $this->loadSettings();
        $this->loadColumns('grid-product', Product::class, ['image', 'name', 'price', 'created_at']);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable(Product::tableName());
    }

}
