<?php

namespace panix\mod\shop\models;

/**
 * This is the model class for table "ShopRelatedProduct".
 *
 * The followings are the available columns in table 'ShopRelatedProduct':
 * @property integer $id
 * @property integer $product_id
 * @property integer $related_id
 */
class ShopRelatedProduct extends \yii\db\ActiveRecord {

    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return '{{%shop_related_product}}';
    }

}
