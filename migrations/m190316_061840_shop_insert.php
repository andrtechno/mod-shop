<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 *
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * @link http://pixelion.com.ua PIXELION CMS
 *
 * Class m190316_061840_shop_insert
 */


use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductCategoryRef;
use panix\mod\shop\models\translate\CategoryTranslate;
use panix\mod\shop\models\translate\ProductTranslate;
use panix\mod\shop\models\TypeAttribute;
use Yii;
use panix\engine\CMS;
use panix\engine\db\Migration;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\translate\AttributeTranslate;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\translate\AttributeOptionTranslate;

class m190316_061840_shop_insert extends Migration
{

    public function up()
    {


        $list = [
            'Размер' => [
                'type' => Attribute::TYPE_DROPDOWN,
                'display_on_front' => true,
                'use_in_filter' => true,
                'use_in_variants' => true,
                'use_in_compare' => true,
                'select_many' => true,
                'required' => true,
                'options' => ['S', 'M', 'L']
            ],
        ];
        $i = 1;
        foreach ($list as $name => $data) {
            $this->batchInsert(Attribute::tableName(), ['name', 'type', 'display_on_front', 'use_in_filter', 'use_in_variants', 'use_in_compare', 'select_many', 'required'], [
                [CMS::slug($name), $data['type'], $data['display_on_front'], $data['use_in_filter'], $data['use_in_variants'], $data['use_in_compare'], $data['select_many'], $data['required']]
            ]);

            foreach (Yii::$app->languageManager->getLanguages(false) as $lang) {
                $this->batchInsert(AttributeTranslate::tableName(), ['object_id', 'language_id', 'title', 'abbreviation', 'hint'], [
                    [$i, $lang['id'], $name, '', '']
                ]);
            }


            if (isset($data['options'])) {
                $o = 1;
                foreach ($data['options'] as $option) {
                    $this->batchInsert(AttributeOption::tableName(), ['attribute_id', 'ordern'], [
                        [$i, $o]
                    ]);
                    foreach (Yii::$app->languageManager->getLanguages(false) as $lang) {
                        $this->batchInsert(AttributeOptionTranslate::tableName(), ['object_id', 'language_id', 'value'], [
                            [$o, $lang['id'], $option]
                        ]);
                    }
                    $o++;
                }
            }

            $i++;
        }


        $this->batchInsert(ProductType::tableName(), ['name'], [
            ['Основной']
        ]);
        $i = 1;
        foreach ($list as $name => $data) {
            $this->batchInsert(TypeAttribute::tableName(), ['type_id', 'attribute_id'], [
                [1, $i]
            ]);
            $i++;
        }


        //Add Root Category
        $this->batchInsert(Category::tableName(), ['lft', 'rgt', 'depth', 'slug', 'full_path'], [
            [1, 2, 1, 'root', '']
        ]);

        foreach (Yii::$app->languageManager->getLanguages(false) as $lang) {
            $this->batchInsert(CategoryTranslate::tableName(), ['object_id', 'language_id', 'name'], [
                [1, $lang['id'], 'Каталог продукции']
            ]);
        }


        $categories = [
            'Обувь' => ['Женская', 'Мужская', 'Детская'],
            'Смартфоны, ТВ и электроника' => [
                'Телефоны',
                'Телевизоры',
                'Планшеты',
                'AV-ресиверы',
                'Акустика Hi-Fi'
            ]
        ];

        foreach ($categories as $cat_name => $cat) {
            $parent_id = Category::findModel(1);
            $s = new Category();
            $s->name = $cat_name;
            $s->slug = CMS::slug($s->name);
            $catParent = $s->appendTo($parent_id);
            if (is_array($cat)) {
                foreach ($cat as $c) {
                    $subCategory = new Category();
                    $subCategory->name = $c;
                    $subCategory->slug = CMS::slug($subCategory->name);
                    $subCategory->appendTo($s);
                }
            }
        }


        $products = [
            [
                'name' => 'test',
                'price' => '100.00',
                'type_id' => 1,
                'manufacturer_id' => 4,
                'main_category' => 2
            ],
            [
                'name' => 'test2',
                'price' => '100.00',
                'type_id' => 1,
                'manufacturer_id' => 4,
                'main_category' => 2
            ]
        ];
        foreach ($products as $product_key => $product) {
            $id = $product_key + 1;
            $this->batchInsert(Product::tableName(), ['price', 'slug', 'manufacturer_id', 'main_category_id', 'created_at', 'updated_at', 'ordern'], [
                [$product['price'], CMS::slug($product['name']), $product['manufacturer_id'], $product['main_category'], time(), time(), $id]
            ]);
            foreach (Yii::$app->languageManager->getLanguages(false) as $lang) {
                $this->batchInsert(ProductTranslate::tableName(), ['object_id', 'language_id', 'name'], [
                    [$id, $lang['id'], $product['name']]
                ]);
            }
            $this->batchInsert(ProductCategoryRef::tableName(), ['product', 'category', 'is_main'], [
                [$id, $product['main_category'], 1]
            ]);

            /*$this->batchInsert('{{%shop__product_attribute_eav}}', ['entity', 'attribute', 'value'], [
                [$id, $product['main_category'], 1]
            ]);*/
        }
    }

    public function down()
    {

    }

}
