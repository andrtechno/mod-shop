<?php

namespace panix\mod\shop\models;

use Yii;
use panix\engine\CMS;
use panix\engine\behaviors\TranslateBehavior;
use panix\mod\shop\models\ShopCategory;
use panix\mod\shop\models\ShopManufacturer;
use panix\mod\shop\models\query\ShopProductQuery;
use panix\mod\shop\models\translate\ShopProductTranslate;
use panix\mod\shop\models\ShopRelatedProduct;
use panix\mod\shop\models\ShopProductCategoryRef;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ProductType extends \panix\engine\db\ActiveRecord {


    const MODULE_ID = 'shop';

 // public static function find() {
     //   return new ShopProductQuery(get_called_class());
   // }

    public static function getCSort() {
        $sort = new \yii\data\Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
        ]);
        return $sort;
    }

 

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%shop_product_type}}';
    }


    /*public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }*/

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name'], 'trim'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name','categories_preset'], 'safe'],
        ];
    }
    public function relations2222() {
        return array(
          //  'attributeRelation' => array(self::HAS_MANY, 'ShopTypeAttribute', 'type_id'),
            'shopAttributes' => array(self::HAS_MANY, 'ShopAttribute', array('attribute_id' => 'id'), 'through' => 'attributeRelation', 'scopes' => 'applyTranslateCriteria'),
            'shopConfigurableAttributes' => array(self::HAS_MANY, 'ShopAttribute', array('attribute_id' => 'id'), 'through' => 'attributeRelation', 'condition' => 'use_in_variants=1'),
         //   'productsCount' => array(self::STAT, 'ShopProduct', 'type_id'),
        );
    }
    public function getProductsCount() {
        return $this->hasOne(ShopProduct::className(), ['id' => 'type_id'])->count();
    }

    public function getAttributeRelation() {
        return $this->hasMany(TypeAttribute::className(), ['type_id' => 'id']);
    }

    public function getShopAttributes() {
        return $this->hasMany(Attribute::className(), ['id' => 'attribute_id'])->via('attributeRelation');
    }
    public function getShopConfigurableAttributes() {
        return $this->hasMany(Attribute::className(), ['id' => 'attribute_id'])->andWhere('use_in_variants=1')->via('attributeRelation');
    }






    /**
     * Clear and set type attributes
     * @param $attributes array of attributes id. array(1,3,5)
     * @return mixed
     */
    public function useAttributes($attributes) {
        // Clear all relations
        TypeAttribute::deleteAll(['type_id' => $this->id]);

        if (empty($attributes))
            return false;

        foreach ($attributes as $attribute_id) {
            if ($attribute_id) {
                $record = new TypeAttribute;
                $record->type_id = $this->id;
                $record->attribute_id = $attribute_id;
                $record->save(false);
            }
        }
    }

    public function afterDelete() {
        // Clear type attribute relations
        TypeAttribute::deleteAll(['type_id' => $this->id]);
        return parent::afterDelete();
    }

    public function __toString() {
        return $this->name;
    }

}
