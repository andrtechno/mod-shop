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



use Yii;
use panix\engine\CMS;
use panix\engine\db\Migration;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\translate\AttributeTranslate;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\translate\AttributeOptionTranslate;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\translate\CategoryTranslate;
use panix\mod\shop\models\TypeAttribute;

/**
 * Class m190316_061840_shop_insert
 * @package panix\mod\shop\migrations
 */
class m190316_061840_shop_insert extends Migration
{

    public function up()
    {
        $typesList = [1 => 'Основной', 2 => 'Ноутбук'];
        foreach ($typesList as $id => $name) {
            $this->batchInsert(ProductType::tableName(), ['id', 'name'], [
                [$id, $name]
            ]);
        }

        /*$i = 1;
        foreach ($products[1]['attributes'] as $name => $data) {
            $this->batchInsert(TypeAttribute::tableName(), ['type_id', 'attribute_id'], [
                [1, $i]
            ]);
            $this->batchInsert(TypeAttribute::tableName(), ['type_id', 'attribute_id'], [
                [2, $i]
            ]);
            $i++;
        }*/


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
            [
                'id' => 2,
                'name' => 'Обувь',
                'children' => [
                    ['id' => 4, 'name' => 'Женская'],
                    ['id' => 5, 'name' => 'Мужская'],
                    ['id' => 6, 'name' => 'Детская']
                ]
            ],
            [
                'id' => 3,
                'name' => 'Смартфоны, ТВ и электроника',
                'children' => [
                    ['id' => 7, 'name' => 'Телефоны'],
                    ['id' => 8, 'name' => 'Телевизоры'],
                    ['id' => 9, 'name' => 'Планшеты'],
                    ['id' => 10, 'name' => 'AV-ресиверы'],
                    ['id' => 11, 'name' => 'Акустика Hi-Fi'],
                    ['id' => 12, 'name' => 'Ноутбуки'],
                ]
            ],
        ];

        foreach ($categories as $cat) {
            $parent_id = Category::findModel(1);
            $s = new Category();
            if (isset($cat['id']))
                $s->id = $cat['id'];
            $s->name = $cat['name'];
            $s->slug = CMS::slug($s->name);
            $s->appendTo($parent_id);
            if (isset($cat['children'])) {
                foreach ($cat['children'] as $child) {
                    $subCategory = new Category();
                    if (isset($child['id']))
                        $subCategory->id = $child['id'];
                    $subCategory->name = $child['name'];
                    $subCategory->slug = CMS::slug($subCategory->name);
                    $subCategory->appendTo($s);
                }
            }
        }

        $products = [
            [
                'id' => 1,
                'name' => 'Ноутбук Lenovo IdeaPad 330-15AST',
                'price' => '5999',
                'type_id' => 2,
                'manufacturer_id' => 6,
                'main_category' => 12,
                'attributes' => [
                    'Диагональ экрана' => '15.6" (1366x768) WXGA HD',
                    'Частота обновления экрана' => '60 Гц',
                    'Объем оперативной памяти' => '4 ГБ',
                    'Операционная система' => 'DOS',
                    'Объём накопителя' => '500 ГБ'
                ]
            ],
            [
                'id' => 2,
                'name' => 'Ноутбук Lenovo IdeaPad 330-15ICH',
                'price' => '17999',
                'type_id' => 2,
                'manufacturer_id' => 6,
                'main_category' => 12,
                'attributes' => [
                    'Диагональ экрана' => '15.6" (1920x1080) Full HD',
                    'Частота обновления экрана' => '60 Гц',
                    'Объем оперативной памяти' => '8 ГБ',
                    'Операционная система' => 'DOS',
                    'Объём накопителя' => '1 ТБ',
                    'Комплект поставки' => [
                        'type' => Attribute::TYPE_CHECKBOX_LIST,
                        'items' => [
                            'Ноутбук',
                            'Адаптер питания',
                            'Документация'
                        ]
                    ]
                ]
            ],

        ];

        foreach ($products as $product_key => $product) {
            $model = new Product;
            $model->id = $product['id'];
            $model->type_id = $product['type_id'];
            $model->name = $product['name'];
            $model->slug = CMS::slug($model->name);
            $model->price = $product['price'];
            $model->manufacturer_id = $product['manufacturer_id'];
            $model->main_category_id = $product['main_category'];
            $model->save(false);
            $model->setCategories([], $product['main_category']);

            if (isset($product['attributes'])) {

                foreach ($product['attributes'] as $attribute_name => $attribute_value) {

                    $attribute = Attribute::find()
                        ->joinWith('translations as translate')
                        ->where(['translate.title' => $attribute_name])
                        ->one();
                    if (!$attribute) {
                        $attribute = new Attribute;
                        $attribute->title = $attribute_name;
                        $attribute->name = CMS::slug($attribute->title);
                        $attribute->type = (isset($attribute_value['type'])) ? $attribute_value['type'] : Attribute::TYPE_DROPDOWN;
                        $attribute->display_on_front = (isset($attribute_value['display_on_front'])) ? $attribute_value['display_on_front'] : true;
                        $attribute->use_in_filter = (isset($attribute_value['use_in_filter'])) ? $attribute_value['use_in_filter'] : true;
                        $attribute->use_in_variants = (isset($attribute_value['use_in_variants'])) ? $attribute_value['use_in_variants'] : true;
                        $attribute->use_in_compare = (isset($attribute_value['use_in_compare'])) ? $attribute_value['use_in_compare'] : true;
                        $attribute->select_many = (isset($attribute_value['select_many'])) ? $attribute_value['select_many'] : true;
                        $attribute->required = (isset($attribute_value['required'])) ? $attribute_value['required'] : false;
                        $attribute->save(false);
                    }
                    if ($attribute) {
                        /** @var \panix\mod\shop\components\EavBehavior $model  */
                        if (is_array($attribute_value) && isset($attribute_value['items'])) {
                            foreach ($attribute_value['items'] as $item) {
                                $attributes = [];
                                $attributeOption = $this->writeAttribute($attribute->id,$item);

                                $attributes[CMS::slug($attribute_name)] = $attributeOption->id;
                                $model->setEavAttributes($attributes, true);
                            }

                        } else {
                            $attributes = [];
                            $attributeOption = $this->writeAttribute($attribute->id,$attribute_value);

                            $attributes[CMS::slug($attribute_name)] = $attributeOption->id;
                            $model->setEavAttributes($attributes, true);
                        }

                    }

                }

            }
        }


        /*$this->batchInsert('{{%shop__product_attribute_eav}}', ['entity', 'attribute', 'value'], [
            [1, CMS::slug(array_keys($attributesList)[0]), 3]
        ]);
        $this->batchInsert('{{%shop__product_attribute_eav}}', ['entity', 'attribute', 'value'], [
            [2, CMS::slug(array_keys($attributesList)[0]), 2]
        ]);
        $this->batchInsert('{{%shop__product_attribute_eav}}', ['entity', 'attribute', 'value'], [
            [3, CMS::slug(array_keys($attributesList)[0]), 2]
        ]);
        $this->batchInsert('{{%shop__product_attribute_eav}}', ['entity', 'attribute', 'value'], [
            [4, CMS::slug(array_keys($attributesList)[0]), 2]
        ]);
        $this->batchInsert('{{%shop__product_attribute_eav}}', ['entity', 'attribute', 'value'], [
            [5, CMS::slug(array_keys($attributesList)[0]), 2]
        ]);*/
    }

    public function down()
    {

    }

    private function writeAttribute($attribute_id, $value)
    {
        $attributeOption = AttributeOption::find()
            ->joinWith('translations as translate')
            ->where(['translate.value' => $value])
            ->one();
        if (!$attributeOption) {
            $attributeOption = new AttributeOption;
            $attributeOption->attribute_id = $attribute_id;
            $attributeOption->value = $value;
            $attributeOption->save(false);
        }
        return $attributeOption;
    }
}
