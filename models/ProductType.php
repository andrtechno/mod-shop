<?php

namespace panix\mod\shop\models;

use panix\engine\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class ProductType
 *
 * @property string $name
 * @package panix\mod\shop\models
 */
class ProductType extends ActiveRecord
{

    const MODULE_ID = 'shop';

    public static function getCSort()
    {
        $sort = new \yii\data\Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
        ]);
        return $sort;
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__product_type}}';
    }


    /*public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }*/

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'trim'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['product_description', 'product_title'], 'string'],
            [['name', 'categories_preset'], 'safe'],
        ];
    }

    public function getProductsCount()
    {
        return $this->hasOne(Product::class, ['id' => 'type_id'])->count();
    }

    public function getAttributeRelation()
    {
        return $this->hasMany(TypeAttribute::class, ['type_id' => 'id']);
    }

    public function getShopAttributes()
    {
        return $this->hasMany(Attribute::class, ['id' => 'attribute_id'])
            ->via('attributeRelation');
    }

    public function getShopConfigurableAttributes()
    {
        return $this->hasMany(Attribute::class, ['id' => 'attribute_id'])
            ->andWhere('use_in_variants=1')
            ->via('attributeRelation');
    }


    /**
     * Clear and set type attributes
     * @param $attributes array of attributes id. array(1,3,5)
     * @return mixed
     */
    public function useAttributes($attributes)
    {
        // Clear all relations
        TypeAttribute::deleteAll(['type_id' => $this->id]);

        if (empty($attributes))
            return false;

        foreach ($attributes as $attribute_id) {
            if ($attribute_id) {
                $record = new TypeAttribute;
                $record->type_id = $this->id;
                $record->attribute_id = $attribute_id;
                $record->save(false);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        // Clear type attribute relations
        TypeAttribute::deleteAll(['type_id' => $this->id]);
        return parent::afterDelete();
    }

    public function __toString()
    {
        return $this->name;
    }

}
