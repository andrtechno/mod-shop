<?php

namespace panix\mod\shop\models\translate;

use yii\db\ActiveRecord;

/**
 * Class to access category translations
 *
 * @property int $id
 * @property int $object_id
 * @property int $language_id
 */
class CategoryTranslate extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%shop__category_translate}}';
    }

}
