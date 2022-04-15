<?php

namespace panix\mod\shop\api\models;

use \yii\db\ActiveRecord;

/**
 * Class Country
 * @package panix\mod\shop\api\v1\models
 */
class Country extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * Define rules for validation
     */
    public function rules()
    {
        return [
            [['id', 'email'], 'required']
        ];
    }
}
