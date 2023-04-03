<?php

namespace panix\mod\shop\models;

use yii\db\ActiveRecord;

/**
 * Class ProductTypeTranslate
 * @package panix\mod\shop\models
 *
 * @property array $translationAttributes
 */
class ProductTypeTranslate extends ActiveRecord
{

    public static $translationAttributes = ['product_title', 'product_description'];

    public static function tableName()
    {
        return '{{%shop__product_type_translate}}';
    }

}
