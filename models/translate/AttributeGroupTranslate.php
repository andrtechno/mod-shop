<?php

namespace panix\mod\shop\models\translate;

use yii\db\ActiveRecord;

/**
 * Class to access attribute group translations
 *
 * @property int $id
 * @property int $object_id
 * @property int $language_id
 * @property string $name
 */
class AttributeGroupTranslate extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%shop__attribute_group_translate}}';
    }

}