<?php

namespace panix\mod\shop\commands;

use panix\engine\CMS;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductAttributesEav;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\ProductCategoryRef;
use panix\mod\shop\models\ProductImage;
use yii\console\Controller;
use Yii;
use yii\helpers\BaseFileHelper;

/**
 * Class FakerController
 * @property $faker \Faker\Factory
 * @package panix\mod\shop\commands
 */
class FakerController extends Controller
{
    /**
     * @var Factory $faker
     */
    public $faker;

    public function init()
    {
        parent::init();
        if (class_exists('Faker\Factory')) {
            $this->faker = \Faker\Factory::create('en_US');
        }
        //$faker = \Faker\Factory::create('uk_UA');
    }

    public function actionIndex($count = 10)
    {

        $mainCategoryId = 6;
        for ($i = 0; $i <= $count; $i++) {
            $text = $this->faker->sentence(rand(100, 500), true);

            $model = new Product();
            $model->user_id = 1;
            $model->type_id = 1;
            $model->brand_id = rand(1, 3);
            $model->main_category_id = $mainCategoryId;
            $model->price = rand(100, 5000);
            $model->unit = 1;
            $model->name = $this->faker->sentence(5, true);
            $model->slug = CMS::slug($model->name);


            $model->short_description = $this->faker->sentences(rand(5, 10), true);
            $model->full_description = '<blockquote>' . $this->faker->sentences(rand(5, 20), true) . '</blockquote>' . $this->faker->sentences(rand(50, 70), true);

            $gbList = [8, 16, 32, 64, 128, 256, 512];
            $product = [
                'attributes' => [
                    'Диагональ экрана' => '12.6" (2304x1440) Retina',
                    'Количество ядер процессора' => rand(1,8),
                    'Базовая частота процессора' => [
                        'type' => Attribute::TYPE_DROPDOWN,
                        'abbreviation' => 'ГГц',
                        'value' => '1,2'
                    ],
                    'Тип оперативной памяти' => 'LPDDR3',
                    'Объем оперативной памяти' => $this->random($gbList) . ' Гб',
                    'Операционная система' => 'macOS High Sierra',
                    'Объём накопителя' => $this->random($gbList) . ' Гб',
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
                        'value' => rand(1,5)
                    ]
                ]
            ];


            if ($model->validate()) {
                $model->save();
                $model->setCategories([6], $mainCategoryId);
                $model->attachImage('https://content2.rozetka.com.ua/goods/images/big/231490106.jpg', 1);
                //$model->attachImage(CMS::fakeImage(Yii::getAlias('@uploads/campaigns'),'640x480','campaign'), 1);

                $this->applyAttributes($model, $product);
            } else {
                print_r($model->errors);
            }

            echo 'Add product: ' . $model->name . PHP_EOL;
        }

    }

    private function random($list)
    {
        return ($list[rand(0, count($list) - 1)]);
    }

    public function applyAttributes($model, $product)
    {


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

    public function actionDel()
    {
        echo 'Truncate products' . PHP_EOL;
        $files = glob(Yii::getAlias('@uploads/store/product/*'));
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                BaseFileHelper::removeDirectory($file);
            }
        }
        Product::deleteAll();
        ProductImage::deleteAll();
        ProductAttributesEav::deleteAll();
        ProductCategoryRef::deleteAll();
        Attribute::deleteAll();
        AttributeOption::deleteAll();
        // Yii::$app->db->createCommand()->truncateTable(Product::tableName())->execute();
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
