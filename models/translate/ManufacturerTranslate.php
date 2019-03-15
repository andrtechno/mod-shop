<?php

namespace panix\mod\shop\models\translate;

use yii\db\ActiveRecord;

/**
 * Class to access manufacturer translations
 *
 * @property int $id
 * @property int $object_id
 * @property int $language_id
 */
class ManufacturerTranslate extends ActiveRecord
{


    public static function tableName()
    {
        return '{{%shop__manufacturer_translate}}';
    }

}
