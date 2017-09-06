<?php
namespace panix\mod\shop\models;
/**
 * Class to access product translations
 *
 * @property int $id
 * @property int $object_id
 * @property int $language_id
 */
class AttributeOptionTranslate extends \yii\db\ActiveRecord {

    public static function tableName() {
        return '{{%shop_attribute_option_translate}}';
    }

}