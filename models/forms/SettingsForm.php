<?php

namespace panix\mod\shop\models\forms;

use panix\engine\SettingsModel;

class SettingsForm extends SettingsModel {

    protected $category = 'shop';
    protected $module = 'shop';
    public $per_page;

    public $product_related_bilateral;
    public $group_attribute;

    public $seo_categories;
    public $seo_categories_title;
    public $seo_categories_description;

    public function rules() {
        return [
            [['per_page'], "required"],
            [['product_related_bilateral', 'seo_categories','group_attribute'], 'boolean'],
            [['seo_categories_title'], 'string', 'max' => 255],
            [['seo_categories_description'], 'string'],
        ];
    }


}
