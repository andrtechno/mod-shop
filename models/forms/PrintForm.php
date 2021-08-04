<?php

namespace panix\mod\shop\models\forms;

use panix\engine\base\Model;

class PrintForm extends Model
{

    public static $category = 'shop';
    protected $module = 'shop';

    public $type;
    public $items = 1000;
    public $size;

    public function rules()
    {
        return [
            [['type', 'items'], "required"],
            // [['product_related_bilateral', 'group_attribute', 'smart_bc', 'smart_title'], 'boolean'],
            [['items'], 'integer'],

            [['size'], 'string'],
        ];
    }

    public static function getSizes()
    {
        return [
            '40x25x100' => '40x25mm x100',
            '30x20x1000' => '30x20mm x1000',
            '58х30x700' => '58х30mm x700',
            '58х40x700' => '58х40mm x700',
            '58х60x460' => '58х60mm x460',
            '58х81' => '58х81mm',
            '100х100x460' => '100х100mm x500',
        ];
    }
}
