<?php

/**
 * Generation migrate by PIXELION CMS
 *
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * @link http://pixelion.com.ua PIXELION CMS
 *
 * Class m999999_999999_shop_insert
 */

use panix\engine\CMS;
use panix\engine\db\Migration;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use panix\mod\images\models\Image;
use panix\mod\shop\models\AttributeGroup;
use panix\mod\shop\models\ProductAttributesEav;
use panix\mod\shop\models\ProductCategoryRef;
use panix\mod\shop\models\Kit;

/**
 * Class m999999_999999_shop_insert
 * @package panix\mod\shop\migrations
 */
class m999999_999999_shop_insert extends Migration
{

    public function up()
    {
        $this->truncateTable(ProductCategoryRef::tableName());
        $this->truncateTable(ProductAttributesEav::tableName());
        $this->truncateTable(Category::tableName());

        $this->truncateTable(AttributeOption::tableName());
        $this->truncateTable(ProductType::tableName());

        $this->db->createCommand()->checkIntegrity(false)->execute();
        $this->truncateTable(Attribute::tableName());

        $this->db->createCommand()->checkIntegrity(false)->execute();
        $this->truncateTable(Product::tableName());


        $typesList = [1 => 'Основной', 2 => 'Ноутбук'];
        foreach ($typesList as $id => $name) {
            $this->batchInsert(ProductType::tableName(), ['id', 'name'], [
                [$id, $name]
            ]);
        }

        //Add Root Category
        $this->batchInsert(Category::tableName(), ['lft', 'rgt', 'depth', 'slug', 'full_path','name_ru'], [
            [1, 2, 1, 'root', '', 'Каталог продукции']
        ]);

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
                ],
                'images' => [
                    'https://i1.foxtrot.com.ua/product/MediumImages/6404192_0.jpg',
                    'https://i1.foxtrot.com.ua/product/MediumImages/6404192_1.jpg',
                    'https://i1.foxtrot.com.ua/product/MediumImages/6404192_2.jpg',
                    'https://i1.foxtrot.com.ua/product/MediumImages/6404192_4.jpg',
                ]
            ],
            [
                'id' => 2,
                'name' => 'Ноутбук Lenovo IdeaPad 330-15ICH',
                'price' => '17999',
                'type_id' => 2,
                'manufacturer_id' => 6,
                'main_category' => 12,
                'images' => [
                    'https://i.citrus.ua/uploads/shop/c/2/c27e2c410abf6f7b4221980e5dc4e4d3.jpg',
                    'https://i.citrus.ua/uploads/shop/2/2/224f8ae519e350c1d62ff57b4c7f8470.jpg',
                    'https://i.citrus.ua/uploads/shop/9/5/95056e20cc0632bf9cbd99e2e75fea89.jpg',
                    'https://i.citrus.ua/uploads/shop/9/e/9e4441f8560518753466b115d85c64b3.jpg',
                ],
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
            [
                'id' => 3,
                'name' => 'Ноутбук Lenovo Ideapad S340-15IWL Platinum Grey',
                'price' => '9499',
                'type_id' => 2,
                'manufacturer_id' => 6,
                'main_category' => 12,
                'images' => [
                    'https://i.citrus.ua/uploads/shop/7/9/79e1105ecd90d96b37e65f1cd72e88fd.jpg',
                    'https://i.citrus.ua/uploads/shop/e/6/e6ed6b3552243283fb510a3967e3c547.jpg',
                    'https://i.citrus.ua/uploads/shop/f/6/f6b03cf06d2b86b72337bb5f50914c2c.jpg',
                    'https://i.citrus.ua/uploads/shop/e/c/ec5e66a1363235b95aac89bd6577f554.jpg',
                ],
                'attributes' => [
                    'Диагональ экрана' => '15.6" (1920x1080) Full HD',
                    'HDMI' => [
                        'type' => Attribute::TYPE_DROPDOWN,
                        'abbreviation' => 'шт',
                        'value' => '1'
                    ],
                    'Количество ядер процессора' => '2',
                    'Базовая частота процессора' => [
                        'type' => Attribute::TYPE_DROPDOWN,
                        'abbreviation' => 'ГГц',
                        'value' => '2,3'
                    ],
                    'Тип оперативной памяти' => 'DDR4',
                    'Объем оперативной памяти' => '4 Гб',
                    'Операционная система' => 'Without OS',
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
            [
                'id' => 4,
                'name' => 'Apple MacBook 12" 256Gb Space Gray (MNYF2) 2017',
                'price' => '27499',
                'type_id' => 2,
                'manufacturer_id' => 1,
                'main_category' => 12,
                'discount' => '5%',
                'images' => [
                    'https://i.citrus.ua/uploads/shop/c/c/cc9baa280332c8033813803a79be2b32.jpg',
                    'https://i.citrus.ua/uploads/shop/8/3/83656813626aa2d51fef71a1d0425c93.jpg',
                    'https://i.citrus.ua/uploads/shop/f/1/f1a32cc47114d0a368accfe4d5ac17fb.jpg',
                    'https://i.citrus.ua/uploads/shop/d/3/d3213e330652ed680735de55e8c51ca8.jpg',
                ],
                'attributes' => [
                    'Диагональ экрана' => '12.6" (2304x1440) Retina',
                    'Количество ядер процессора' => '2',
                    'Базовая частота процессора' => [
                        'type' => Attribute::TYPE_DROPDOWN,
                        'abbreviation' => 'ГГц',
                        'value' => '1,2'
                    ],
                    'Тип оперативной памяти' => 'LPDDR3',
                    'Объем оперативной памяти' => '8 Гб',
                    'Операционная система' => 'macOS High Sierra',
                    'Объём накопителя' => '256 Гб',
                    'Комплект поставки' => [
                        'type' => Attribute::TYPE_CHECKBOX_LIST,
                        'items' => [
                            'MacBook',
                            'Адаптер питания USB‑C мощностью 29 Вт',
                            'Кабель USB‑C для зарядки (2 м)'
                        ]
                    ],
                    'HDMI' => [
                        'type' => Attribute::TYPE_DROPDOWN,
                        'abbreviation' => 'шт',
                        'value' => '2'
                    ]
                ]
            ],
        ];


        foreach ($products as $product_key => $product) {
            /** @var Product|\panix\mod\images\behaviors\ImageBehavior $model */
            $model = new Product;
            $model->id = $product['id'];
            $model->type_id = $product['type_id'];
            $model->name = $product['name'];
            $model->slug = CMS::slug($model->name);
            $model->price = $product['price'];
            $model->manufacturer_id = $product['manufacturer_id'];
            $model->main_category_id = $product['main_category'];
            if (isset($product['discount']))
                $model->discount = $product['discount'];
            $model->save(false);
            $model->setCategories([], $product['main_category']);
            if (isset($product['images'])) {
                echo $model->name.PHP_EOL;
                foreach ($product['images'] as $image) {
                    $model->attachImage($image);
                }
            }

            if (isset($product['attributes'])) {

                foreach ($product['attributes'] as $attribute_name => $attribute_value) {

                    $attribute = Attribute::find()
                        ->where(['title_ru' => $attribute_name])
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
                        $attribute->abbreviation = (isset($attribute_value['abbreviation'])) ? $attribute_value['abbreviation'] : null;
                        $attribute->save(false);
                    }
                    if ($attribute) {
                        /** @var \panix\mod\shop\components\EavBehavior $model */
                        if (is_array($attribute_value)) {
                            if (isset($attribute_value['items'])) {
                                foreach ($attribute_value['items'] as $item) {
                                    $attributes = [];
                                    $attributeOption = $this->writeAttribute($attribute->id, $item);

                                    $attributes[CMS::slug($attribute_name)] = $attributeOption->id;
                                    $model->setEavAttributes($attributes, true);
                                }
                            } elseif ($attribute_value['value']) {
                                $attributes = [];
                                $attributeOption = $this->writeAttribute($attribute->id, (isset($attribute_value['value'])) ? $attribute_value['value'] : $attribute_value);
                                $attributes[CMS::slug($attribute_name)] = $attributeOption->id;
                                $model->setEavAttributes($attributes, true);
                            }
                        } else {
                            $attributes = [];
                            $attributeOption = $this->writeAttribute($attribute->id, (isset($attribute_value['value'])) ? $attribute_value['value'] : $attribute_value);

                            $attributes[CMS::slug($attribute_name)] = $attributeOption->id;
                            $model->setEavAttributes($attributes, true);
                        }

                    }

                }

            }
        }
        /*$this->batchInsert(Kit::tableName(), ['owner_id', 'product_id', 'price', 'from'], [
            [4, 2, '', '']
        ]);
        $this->batchInsert(Kit::tableName(), ['owner_id', 'product_id', 'price', 'from'], [
            [4, 1, '', '']
        ]);
        $this->batchInsert(Kit::tableName(), ['owner_id', 'product_id', 'price', 'from'], [
            [4, 3, '', '']
        ]);*/

    }

    public function down()
    {
        $this->truncateTable(Attribute::tableName());
        $this->truncateTable(AttributeOption::tableName());
        $this->truncateTable(AttributeGroup::tableName());
        $this->truncateTable(Product::tableName());
        $this->truncateTable(Category::tableName());
        $this->truncateTable(ProductType::tableName());
        $this->truncateTable(ProductCategoryRef::tableName());
        $this->truncateTable(ProductAttributesEav::tableName());

        $this->truncateTable(Image::tableName());

    }

    private function writeAttribute($attribute_id, $value)
    {
        $attributeOption = AttributeOption::find()
            ->where(['value' => $value])
            ->one();
        if (!$attributeOption) {
            $attributeOption = new AttributeOption;
            $attributeOption->attribute_id = $attribute_id;
            $attributeOption->value = $value;
            $attributeOption->value_uk = $value;
            $attributeOption->value_en = $value;
            $attributeOption->save(false);
        }
        return $attributeOption;
    }
}
