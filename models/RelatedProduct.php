<?php

namespace panix\mod\shop\models;

/**
 * This is the model class for table "RelatedProduct".
 *
 * The followings are the available columns in table 'RelatedProduct':
 * @property integer $id
 * @property integer $product_id
 * @property integer $related_id
 */
class RelatedProduct extends \yii\db\ActiveRecord {

    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return '{{%shop__related_product}}';
    }

}
