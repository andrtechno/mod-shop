<?php

namespace panix\mod\shop\models;

//use Yii;
use panix\engine\WebModel;
use panix\engine\behaviors\TranslateBehavior;
use panix\engine\behaviors\NestedSetsBehavior;
use panix\engine\behaviors\MenuArrayBehavior;
use panix\mod\shop\models\translate\ShopCategoryTranslate;
use panix\mod\shop\models\query\ShopCategoryQuery;

class ShopCategory extends WebModel {

    const MODULE_ID = 'shop';

    public static function tableName() {
        return '{{%shop_category}}';
    }

    public static function find() {
        return new ShopCategoryQuery(get_called_class());
    }

    public function rules() {
        return [
            [['seo_alias'], 'required'],
            [['name'], 'string', 'max' => 255]
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
            // 'depthAttribute' => 'depth',
            ],
        ];
    }

    public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }

    public function getTranslations() {
        return $this->hasMany(ShopCategoryTranslate::className(), ['object_id' => 'id']);
    }

    public static function flatTree() {
        $result = [];
        $categories = ShopCategory::find()->orderBy(['lft' => SORT_ASC])->all();
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

    public function rebuildFullPath() {
        // Create category full path.
        $ancestors = $this->find()->leaves()->all();
        if (sizeof($ancestors)) {
            // Remove root category from path
            unset($ancestors[0]);

            $parts = array();
            foreach ($ancestors as $ancestor)
                $parts[] = $ancestor->seo_alias;

            $parts[] = $this->seo_alias;
            $this->full_path = implode('/', array_filter($parts));
        }

        return $this;
    }

}
