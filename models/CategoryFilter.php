<?php

namespace panix\mod\shop\models;


use panix\mod\shop\components\ExternalFinder;
use panix\mod\sitemap\behaviors\SitemapBehavior;
use Yii;
use yii\helpers\ArrayHelper;
use panix\engine\behaviors\nestedsets\NestedSetsBehavior;
use panix\mod\shop\models\translate\CategoryTranslate;
use panix\mod\shop\models\query\CategoryQuery;
use panix\engine\CMS;
use panix\engine\db\ActiveRecord;
use panix\engine\behaviors\UploadFileBehavior;

/**
 * Class CategoryFilter
 * @package panix\mod\shop\models
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $option_id
 * @property string getMetaTitle()
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
