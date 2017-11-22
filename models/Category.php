<?php

namespace panix\mod\shop\models;

use Yii;
use panix\engine\behaviors\TranslateBehavior;
use panix\engine\behaviors\nestedsets\NestedSetsBehavior;
use panix\engine\behaviors\MenuArrayBehavior;
use panix\mod\shop\models\translate\CategoryTranslate;
use panix\mod\shop\models\query\CategoryQuery;
use panix\mod\shop\models\ProductCategoryRef;
use panix\engine\CMS;
class Category extends \panix\engine\db\ActiveRecord {

    const MODULE_ID = 'shop';
    const route = '/shop/admin/category';

    public $parent_id;

    public static function tableName() {
        return '{{%shop_category}}';
    }

    public static function find() {
        return new CategoryQuery(get_called_class());
    }

    public function getUrl() {
        return ['/shop/category/view', 'seo_alias' => $this->full_path];
    }

    public function rules() {
        return [
            [['name', 'seo_alias'], 'required'],
            [['name'], 'string', 'max' => 255],
            ['description', 'safe']
        ];
    }

    public function behaviors() {
        return [
            'TranslateBehavior' => [ // name it the way you want
                'class' => TranslateBehavior::className(),
                'translationAttributes' => [
                    'name',
                    'description'
                ]
            ],
            'MenuArrayBehavior' => array(
                'class' => MenuArrayBehavior::className(),
                'labelAttr' => 'name',
                // 'countProduct'=>false,
                'urlExpression' => '["/shop/category/view", "seo_alias"=>$model->full_path]',
            ),
            'tree' => [
                'class' => NestedSetsBehavior::className(),
            // 'treeAttribute' => 'tree',
            // 'leftAttribute' => 'lft',
            // 'rightAttribute' => 'rgt',
            //'levelAttribute' => 'level',
            ],
        ];
    }

    public function getCountProducts() {
        return $this->hasMany(ProductCategoryRef::className(), ['category' => 'id'])->count();
    }

    public function getTranslations() {
        return $this->hasMany(CategoryTranslate::className(), ['object_id' => 'id']);
    }

    public static function flatTree() {
        $result = [];
        $categories = Category::find()->orderBy(['lft' => SORT_ASC])->all();
        array_shift($categories);

        foreach ($categories as $c) {

            if ($c->depth > 1) {
                $result[$c->id] = str_repeat('--', $c->depth - 1) . ' ' . $c->name;
            } else {
                $result[$c->id] = ' ' . $c->name;
            }
        }

        return $result;
    }

    public function beforeSave($insert) {
        $this->rebuildFullPath();
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes) {
        \Yii::$app->cache->delete('CategoryUrlRule');
        return parent::afterSave($insert, $changedAttributes);
    }

    public function rebuildFullPath() {

        //test=   ShopCategory::findOne($this->id);
        $ancestors = $this->ancestors()->addOrderBy('depth')->all();
        // $ancestors = $this->find()->leaves()->all();
        // $ancestors= $this->parent_id->getLeaves()->all();
        // if($this->parent_id > 1){
        //     $test = ShopCategory::findOne($this->parent_id);
        //     $ancestors =  $test->leaves()->all();
        // }
        // 
        //   print_r($ancestors);


        if (sizeof($ancestors)) {
            // Remove root category from path
            //  if($this->parent_id == 1){
            unset($ancestors[0]);
            // }
            $parts = [];
            foreach ($ancestors as $ancestor)
                $parts[] = $ancestor->seo_alias;

            $parts[] = $this->seo_alias;
            $this->full_path = implode('/', array_filter($parts));
        }

//print_r($this->full_path);die;
        // return $this;
    }
    public function keywords() {
        return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_categories_keywords'));
    }

    public function description() {
        return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_categories_description'));
    }

    public function title() {
        return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_categories_title'));
    }

    public function replaceMeta($text) {
        $replace = array(
            "{category_name}" => $this->name,
            "{sub_category_name}" => ($this->parent()->one()->name == 'root') ? '' : $this->parent()->one()->name,
            "{current_currency}" => Yii::$app->currency->active->symbol,
        );
        return CMS::textReplace($text, $replace);
    }
}
