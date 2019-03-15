<?php

namespace panix\mod\shop\models\translate;

use yii\db\ActiveRecord;

/**
 * Class to access product translations
 *
 * @property int $id
 * @property int $object_id
 * @property int $language_id
 */
class ProductTranslate extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%shop__product_translate}}';
    }

}
