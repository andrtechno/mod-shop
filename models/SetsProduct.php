<?php

namespace panix\mod\shop\models;

use Yii;
use panix\engine\db\ActiveRecord;
use panix\mod\shop\models\query\SetsProductQuery;

/**
 * Class SetsProduct
 * @property integer $id
 */
class SetsProduct extends ActiveRecord
{

    const MODULE_ID = 'shop';

    /**
     * @inheritdoc
     * @return SetsProductQuery
     */
    public static function find()
    {
        return new SetsProductQuery(get_called_class());
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



    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getProducts2()
    {
        return $this->hasMany(Product::class, ['product_id' => 'id']);
    }

}
