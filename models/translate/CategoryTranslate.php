<?php

namespace panix\mod\shop\models\translate;

use yii\db\ActiveRecord;

/**
 * Class CategoryTranslate category translations
 *
 * @property int $id
 * @property int $object_id
 * @property int $language_id
 * @property string $name
 * @property string $description
 */
class CategoryTranslate extends ActiveRecord
{
    public static $translationAttributes = ['name', 'description'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__category_translate}}';
    }

}
