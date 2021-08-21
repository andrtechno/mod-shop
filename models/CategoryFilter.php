<?php

namespace panix\mod\shop\models;

use Yii;
use panix\mod\shop\models\query\CategoryQuery;
use panix\engine\CMS;
use panix\engine\db\ActiveRecord;

/**
 * Class CategoryFilter IN DEV
 * @package panix\mod\shop\models
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $option_id
 */
class CategoryFilter extends ActiveRecord
{

    const MODULE_ID = 'shop';
    const route = '/admin/shop/category';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__category_filter}}';
    }

    public static function find()
    {
        return new CategoryQuery(get_called_class());
    }


    /**
     * @return int
     */
    public function getCountItems()
    {
        return (int)$this->hasMany(ProductCategoryRef::class, ['category' => 'id'])->count();
    }


}
