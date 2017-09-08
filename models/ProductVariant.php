<?php
namespace panix\mod\shop\models;

use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\AttributeOption;
/**
 * This is the model class for table "ProductVariant".
 *
 * The followings are the available columns in table 'ProductVariant':
 * @property integer $id
 * @property integer $attribute_id
 * @property integer $option_id
 * @property integer $product_id
 * @property float $price
 * @property integer $price_type
 * @property string $sku
 */
class ProductVariant extends \yii\db\ActiveRecord {


    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return '{{%shop_product_variant}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return [
            [['attribute_id', 'option_id', 'product_id', 'price', 'price_type'], 'required'],
            //[['attribute_id', 'option_id', 'product_id', 'price_type'], 'numerical', 'integerOnly' => true],
            //['price', 'numerical'],
            ['sku', 'string', 'max' => 255],
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            [['id', 'attribute_id', 'option_id', 'product_id', 'price', 'price_type', 'sku'], 'safe'],
        ];
    }

    /**
     * @return array relational rules.
     
    public function relations() {
        return array(
            'attribute' => array(self::BELONGS_TO, 'Attribute', 'attribute_id'),
            'option' => array(self::BELONGS_TO, 'AttributeOption', 'option_id'),
        );
    }*/
    public function getAttribute() {
        return $this->hasOne(Attribute::className(), ['id' => 'attribute_id']);
    }
    
    public function getOption() {
        return $this->hasOne(AttributeOption::className(), ['id' => 'option_id']);
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'attribute_id' => 'Attribute',
            'option_id' => 'Option',
            'product_id' => 'Product',
            'price' => 'Price',
            'price_type' => 'Price Type',
            'sku' => 'Sku',
        );
    }

}