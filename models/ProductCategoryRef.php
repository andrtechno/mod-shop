<?php

namespace panix\mod\shop\models;


/**
 * This is the model class for table "ProductCategoryRef".
 *
 * The followings are the available columns in table 'ProductCategoryRef':
 * @property integer $id
 * @property integer $category
 * @property integer $product
 * @property boolean $is_main
 */
class ProductCategoryRef extends \yii\db\ActiveRecord {



    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return '{{%shop_product_category_ref}}';
    }
       // public function getCountProducts() {
       // return $this->hasMany(ProductCategoryRef::className(), ['category' => 'product'])->count();
   // }
  /*  public function relations() {
        return array(
            'active' => array(self::STAT, 'Product', 'id', 'condition'=>'`products`.`switch`=1'),
            'countProducts' => array(self::HAS_MANY, 'ProductCategoryRef', 'category'),
        );
    }*/
}