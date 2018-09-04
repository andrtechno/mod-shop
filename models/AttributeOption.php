<?php

namespace panix\mod\shop\models;

use yii\helpers\ArrayHelper;
use panix\mod\shop\models\AttributeOptionTranslate;
use panix\mod\shop\models\query\AttributeOptionsQuery;

/**
 * Shop options for dropdown and multiple select
 * This is the model class for table "AttributeOptions".
 *
 * The followings are the available columns in table 'AttributeOptions':
 * @property integer $id
 * @property integer $attribute_id
 * @property string $value
 * @property integer $position
 */
class AttributeOption extends \panix\engine\db\ActiveRecord {

    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return '{{%shop_attribute_option}}';
    }

    public static function find() {
        return new AttributeOptionsQuery(get_called_class());
    }

    public function rules() {
        return [
            [['id', 'value', 'attribute_id', 'ordern'], 'safe'],
        ];
    }

    public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }

    public function getOptionTranslate() {
        return $this->hasMany(AttributeOptionTranslate::class, ['object_id' => 'id']);
    }

 
    public function behaviors() {
        return ArrayHelper::merge([
                    'translate' => [
                        'class' => \panix\engine\behaviors\TranslateBehavior::class,
                        'translationRelation' => 'optionTranslate',
                        'translationAttributes' => [
                            'value',
                        ]
                    ],
                        ], parent::behaviors());
    }

}
