<?php

namespace panix\mod\shop\models;


use Yii;
use yii\helpers\ArrayHelper;
use panix\engine\behaviors\nestedsets\NestedSetsBehavior;
use panix\mod\shop\models\translate\CategoryTranslate;
use panix\mod\shop\models\query\CategoryQuery;
use panix\engine\CMS;
use panix\engine\db\ActiveRecord;
use panix\engine\behaviors\UploadFileBehavior;

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
 * @property string $full_path
 * @property integer $switch
 * @property integer $countItems Relation of getCountItems()
 * @property string getMetaDescription()
 * @property string getMetaTitle()
 */
class Category extends ActiveRecord
{

    const MODULE_ID = 'shop';
    const route = '/shop/admin/category';
    public $translationClass = CategoryTranslate::class;
    public $parent_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__category}}';
    }

    public static function find()
    {
        return new CategoryQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function getUrl()
    {
        return ['/shop/catalog/view', 'slug' => $this->full_path];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => ['png', 'jpg']],
            ['slug', '\panix\engine\validators\UrlValidator', 'attributeCompare' => 'name'],
            ['slug', 'fullPathValidator'],
            ['slug', 'match',
                'pattern' => '/^([a-z0-9-])+$/i',
                'message' => Yii::t('app', 'PATTERN_URL')
            ],
            [['name', 'slug'], 'trim'],
            [['name', 'slug'], 'required'],
            [['description','image'], 'default'],
            [['name'], 'string', 'max' => 255],
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

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        if (Yii::$app->getModule('seo'))
            $a['seo'] = [
                'class' => '\panix\mod\seo\components\SeoBehavior',
                'url' => $this->getUrl()
            ];

        $a['uploadFile'] = [
            'class' => UploadFileBehavior::class,
            'files' => [
                'image' => '@uploads/categories'
            ]
        ];
        $a['tree'] = [
            'class' => NestedSetsBehavior::class,
            'hasManyRoots' => false
        ];
        return ArrayHelper::merge($a, parent::behaviors());
    }

    /**
     * Relation ProductCategoryRef
     * @return int
     */
    public function getCountItems()
    {
        return (int)$this->hasMany(ProductCategoryRef::class, ['category' => 'id'])->count();
    }

    public static function flatTree()
    {
        $result = [];
        $categories = Category::find()->orderBy(['lft' => SORT_ASC])->all();
        array_shift($categories);

        foreach ($categories as $c) {
            /**
             * @var self $c
             */
            if ($c->depth > 2) {
                $result[$c->id] = str_repeat(html_entity_decode('&mdash;'), $c->depth - 2) . ' ' . $c->name;
            } else {
                $result[$c->id] = ' ' . $c->name;
            }
        }

        return $result;
    }


    public function test($item)
    {
        $childCounter = 0;
        $categories = [];
        $children = $item->children()->all();
        if ($children) {
            foreach ($children as $child) {
                /** @var static|\panix\engine\behaviors\nestedsets\NestedSetsBehavior $child * */
                $categories[] = [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'url' => $child->getUrl(),
                    'productsCount' => $child->countItems,
                    //'child' => $this->test($child)
                ];
                $categories[]['child'][] = $this->test($child);
                $childCounter += $child->countItems;
            }
            CMS::dump($categories);
            die;
        }

        return [
            'children' => $categories,
            'counter' => $childCounter
        ];
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


        $childrens = $this->descendants()->all();
        if ($childrens) {
            foreach ($childrens as $children) {
                $children->full_path = $this->slug . '/' . $children->full_path;
                $children->saveNode(false);
            }
        }
        Yii::$app->cache->delete('CategoryUrlRule');
        return parent::afterSave($insert, $changedAttributes);
    }

    public function rebuildFullPath()
    {
        // Create category full path.
        $ancestors = $this->ancestors()
            //->orderBy('depth')
            ->all();
        if ($ancestors) {
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

    /**
     * @return string
     */
    public function title()
    {
        $value = $this->name;
        return $value;
    }

    public function replaceMeta($text, $parentCategory)
    {
        $replace = [
            "{category_name}" => $this->name,
            "{sub_category_name}" => ($parentCategory->name == 'root') ? '' : $parentCategory->name,
            "{currency.symbol}" => Yii::$app->currency->active['symbol'],
        ];
        return CMS::textReplace($text, $replace);
    }

}
