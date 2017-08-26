<?php

namespace panix\mod\shop\models\forms;

use panix\engine\SettingsModel;

class SettingsForm extends SettingsModel {

    protected $category = 'shop';
    protected $module = 'shop';
    public $pagenum;
    public $price_decimal;
    public $price_thousand;
    public $price_penny;
    public $product_related_bilateral;

    public function rules() {
        return [
            [['pagenum','price_decimal','price_thousand'], "required"],
            [['product_related_bilateral','price_penny'],'boolean']
        ];
    }

    public static function priceSeparator(){
        return [0 => 'нечего', 32 => 'пробел', 44 => 'запятая', 46 => 'точка'];
    }
}