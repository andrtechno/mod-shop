<?php

namespace panix\mod\shop\models;

use panix\engine\behaviors\UploadFileBehavior;
use Yii;
use panix\engine\behaviors\TranslateBehavior;
use panix\engine\behaviors\nestedsets\NestedSetsBehavior;
use panix\mod\shop\models\translate\CategoryTranslate;
use panix\mod\shop\models\query\CategoryQuery;
use panix\engine\CMS;
use panix\engine\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class Category
 * @package panix\mod\shop\models
 *
 * @property integer $id
 * @property integer $tree
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property string $slug
 * @property string $image
 * @property string $name
 * @property string $description
 * @property string $seo_product_title
 * @property string $seo_product_description
 * @property string $full_path
 * @property integer $switch
 */
class Category extends ActiveRecord
{

    const MODULE_ID = 'shop';
    const route = '/shop/admin/category';

    /**
     * Translate options
     * @var array
     */
    public $translationOptions = [
        'model'=>CategoryTranslate::class,
        'translationAttributes' => [
            'name',
            'description',
            'seo_product_title',
            'seo_product_description'
        ]
    ];

    public $parent_id;

    public static function tableName()
    {
        return '{{%shop__category}}';
    }

    public static function find()
    {
        return new CategoryQuery(get_called_class());
    }

    public function getUrl()
    {
        return ['/shop/category/view', 'slug' => $this->full_path];
    }

    public function rules()
    {
        return [
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            // ['slug', '\panix\engine\validators\UrlValidator', 'attributeCompare' => 'name'],

            ['slug', '\panix\engine\validators\UrlValidator', 'attributeCompare' => 'name'],
            ['slug', 'fullPathValidator'],
            ['slug', 'match',
                'pattern' => '/^([a-z0-9-])+$/i',
                'message' => Yii::t('app', 'PATTERN_URL')
            ],
            [['name', 'slug', 'seo_product_title'], 'trim'],
            [['image'], 'default'],
            [['name', 'slug'], 'required'],
            [['name', 'seo_product_title', 'seo_product_description'], 'string', 'max' => 255],
            ['description', 'safe']
        ];
    }


    public function fullPathValidator($attribute)
    {
        if ($this->parent_id) {
            $count = Category::find()->where(['full_path' => $this->parent_id->full_path . '/' . $this->{$attribute}])->count();
            if ($count) {
                $this->addError($attribute, 'Такой URL уже есть!');
            }
        }
    }

    public function behaviors()
    {
        $a['uploadFile'] = [
            'class' => UploadFileBehavior::class,
            'files' => [
                'image' => '@uploads/categories'
            ]
        ];
        $a['tree'] = [
            'class' => NestedSetsBehavior::class,
            'hasManyRoots' => false
            // 'treeAttribute' => 'tree',
            // 'leftAttribute' => 'lft',
            // 'rightAttribute' => 'rgt',
            //'levelAttribute' => 'level',
        ];
        return ArrayHelper::merge(parent::behaviors(), $a);
    }

    public function getCountItems()
    {
        return $this->hasMany(ProductCategoryRef::class, ['category' => 'id'])->count();
    }


    //public function getTranslations()
    //{
   //     return $this->hasMany($this->translationClass, ['object_id' => 'id']);
    //}

    public static function flatTree()
    {
        $result = [];
        $categories = Category::find()->orderBy(['lft' => SORT_ASC])->all();
        array_shift($categories);

        foreach ($categories as $c) {
            /**
             * @var Category $c
             */
            if ($c->depth > 1) {
                $result[$c->id] = str_repeat('--', $c->depth - 1) . ' ' . $c->name;
            } else {
                $result[$c->id] = ' ' . $c->name;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->rebuildFullPath();
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        \Yii::$app->cache->delete('CategoryUrlRule');
        return parent::afterSave($insert, $changedAttributes);
    }

    public function rebuildFullPath()
    {
        // Create category full path.
        $ancestors = $this->ancestors()
            //->orderBy('depth')
            ->all();
        if (sizeof($ancestors)) {
            // Remove root category from path
            unset($ancestors[0]);

            $parts = [];
            foreach ($ancestors as $ancestor)
                $parts[] = $ancestor->slug;

            $parts[] = $this->slug;
            $this->full_path = implode('/', array_filter($parts));
        }

        return $this;
    }

    public function description()
    {
        if ($this->seo_product_description) {
            $value = $this->seo_product_description;
        } else {
            $value = Yii::$app->settings->get('shop', 'seo_categories_description');
        }
        return $this->replaceMeta($value);
    }

    public function title()
    {
        if ($this->seo_product_title) {
            $value = $this->seo_product_title;
        } else {
            $value = Yii::$app->settings->get('shop', 'seo_categories_title');
        }
        return $this->replaceMeta($value);
    }

    public function replaceMeta($text)
    {
        $replace = [
            "{category_name}" => $this->name,
            "{sub_category_name}" => ($this->parent()->one()->name == 'root') ? '' : $this->parent()->one()->name,
            "{current_currency}" => Yii::$app->currency->active->symbol,
        ];
        return CMS::textReplace($text, $replace);
    }
}
