<?php

namespace panix\mod\shop\models;

use Yii;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\translate\AttributeGroupTranslate;
use panix\mod\shop\models\query\AttributeQuery;
use panix\engine\db\ActiveRecord;


class AttributeGroup extends ActiveRecord
{

    const MODULE_ID = 'shop';

    public static function find2()
    {
        return new AttributeGroupQuery(get_called_class());
    }

    public function getGridColumns()
    {
        return [
            [
                'attribute' => 'title',
                'contentOptions' => ['class' => 'text-left'],
            ],
            'name',
            'DEFAULT_CONTROL' => [
                'class' => 'panix\engine\grid\columns\ActionColumn',
            ],
            'DEFAULT_COLUMNS' => [
                [
                    'class' => \panix\engine\grid\sortable\Column::class,
                    'url' => ['/admin/shop/attribute/sortable']
                ],
                ['class' => 'panix\engine\grid\columns\CheckboxColumn'],
            ],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%shop__attribute_group}}';
    }

    public function getTranslation()
    {
        return $this->hasMany(AttributeGroupTranslate::class, ['object_id' => 'id']);
    }


    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'trim'],
            [['switch'], 'boolean'],
            [['id', 'name', 'switch'], 'safe'],
        ];
    }

    public function behaviors()
    {
        return ArrayHelper::merge([
            'translate' => [
                'class' => \panix\engine\behaviors\TranslateBehavior::class,
                'translationRelation' => 'translation',
                'translationAttributes' => [
                    'name',
                ]
            ],
        ], parent::behaviors());
    }



    public static function getSort()
    {
        return new \yii\data\Sort([
            'attributes' => [
                'name' => [
                    'asc' => ['name' => SORT_ASC],
                    'desc' => ['name' => SORT_DESC],
                ],
            ],
        ]);
    }



}
