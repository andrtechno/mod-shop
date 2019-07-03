<?php

namespace panix\mod\shop\models\translate;

use yii\db\ActiveRecord;

/**
 * Class to access attribute options translations
 *
 * @property int $id
 * @property int $object_id
 * @property int $language_id
 * @property string $value
 */
class AttributeOptionTranslate extends ActiveRecord
{
    public static $translationAttributes = ['value'];

    public static function tableName()
    {
        return '{{%shop__attribute_option_translate}}';
    }

}