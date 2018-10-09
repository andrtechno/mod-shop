<?php
namespace panix\mod\shop\models;
/**
 * Class to access product translations
 *
 * @property int $id
 * @property int $object_id
 * @property int $language_id
 */
class AttributeTranslate extends \yii\db\ActiveRecord {

    public static function tableName() {
        return '{{%shop__attribute_translate}}';
    }


}