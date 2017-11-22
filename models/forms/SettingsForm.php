<?php

namespace panix\mod\shop\models\forms;

use panix\engine\SettingsModel;

class SettingsForm extends SettingsModel {

    protected $category = 'shop';
    protected $module = 'shop';
    public $per_page;
    public $price_decimal;
    public $price_thousand;
    public $price_penny;
    public $product_related_bilateral;
    
    /**
     * Seo params
     * @var type 
     */
    public $seo_products;
    public $seo_products_title;
    public $seo_products_keywords;
    public $seo_products_description;
    public $seo_categories;
    public $seo_categories_title;
    public $seo_categories_keywords;
    public $seo_categories_description;

    public function rules() {
        return [
            [['per_page', 'price_decimal', 'price_thousand'], "required"],
            [['product_related_bilateral', 'price_penny', 'seo_products', 'seo_categories'], 'boolean'],
            [['seo_products_title', 'seo_categories_title'], 'string', 'max' => 255],
            [['seo_categories_keywords', 'seo_categories_description', 'seo_products_keywords', 'seo_products_description'], 'string'],
        ];
    }

    public static function priceSeparator() {
        return [0 => 'нечего', 32 => 'пробел', 44 => 'запятая', 46 => 'точка'];
    }

}
