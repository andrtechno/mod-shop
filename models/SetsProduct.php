<?php

namespace panix\mod\shop\models;

use Yii;
use panix\engine\db\ActiveRecord;
use panix\mod\shop\models\query\ManufacturerQuery;

/**
 * Class Manufacturer
 * @property integer $id
 */
class SetsProduct extends ActiveRecord
{

    const MODULE_ID = 'shop';

    /**
     * @inheritdoc
     * @return ManufacturerQuery
     */
    public static function find()
    {
        return new ManufacturerQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__sets_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'slug'], 'required'],
            [['name', 'slug'], 'trim'],
            [['description'], 'string'],
            [['description'], 'default', 'value' => null],
            [['name', 'slug'], 'string', 'max' => 255],
            [['ordern'], 'integer'],
            [['name', 'slug'], 'safe'],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
        ];
    }



    public function getProduct2()
    {
        return $this->hasOne(Product::class, ['id' => 'product']);
    }

    public function getProducts2()
    {
        return $this->hasMany(Product::class, ['product_id' => 'id']);
    }

}
