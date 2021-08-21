<?php

namespace panix\mod\shop\models;


use panix\mod\shop\models\query\ProductQuery;
use Yii;
use panix\engine\db\ActiveRecord;

/**
 * Class ProductFilter IN DEV
 * @package panix\mod\shop\models
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $option_id
 */
class ProductFilter extends ActiveRecord
{

    const MODULE_ID = 'shop';
    const route = '/admin/shop/product';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__product_filter}}';
    }

    public static function find()
    {
        return new ProductQuery(get_called_class());
    }


}
