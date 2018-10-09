<?php

namespace panix\mod\shop\models;

use panix\mod\shop\models\Attribute;

/**
 * Shop type attributes
 * This is the model class for table "shop_type_attribute".
 *
 * The followings are the available columns in table 'shop_type_attribute':
 * @property integer $id
 * @property integer $type_id
 * @property integer $attribute_id
 */
class TypeAttribute extends \yii\db\ActiveRecord {

    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return '{{%shop__type_attribute}}';
    }

    public function getMyAttribute() {
        return $this->hasOne(Attribute::class, ['attribute_id' => 'id']);
    }

}
