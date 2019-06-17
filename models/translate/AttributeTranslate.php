<?php

namespace panix\mod\shop\models\translate;

use yii\db\ActiveRecord;

/**
 * Class to access attribute translations
 *
 * @property int $id
 * @property int $object_id
 * @property int $language_id
 * @property string $title
 * @property string $hint
 * @property string $abbreviation
 */
class AttributeTranslate extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__attribute_translate}}';
    }


}