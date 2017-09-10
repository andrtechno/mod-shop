<?php

namespace panix\mod\shop\models;

use panix\mod\shop\models\query\ShopManufacturerQuery;
use panix\engine\behaviors\TranslateBehavior;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\translate\ShopManufacturerTranslate;

class ShopManufacturer extends \panix\engine\db\ActiveRecord {

    const MODULE_ID = 'shop';
    const route = '/admin/shop/manufacturer';
    public static function find() {
        return new ShopManufacturerQuery(get_called_class());
    }

    public static function dropdown() {
        // get and cache data
        static $dropdown;
        if ($dropdown === null) {

            // get all records from database and generate
            $models = static::find()->all();
            foreach ($models as $model) {
                $dropdown[$model->id] = $model->name;
            }
        }

        return $dropdown;
    }

    public function getUrl() {
        return ['/shop/manufacturer/index', 'url' => $this->seo_alias];
    }

    public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%shop_manufacturer}}';
    }

    public function getTranslations() {
        return $this->hasMany(ShopManufacturerTranslate::className(), ['object_id' => 'id']);
    }
    public function getProductsCount() {
        return $this->hasOne(ShopProduct::className(), ['manufacturer_id' => 'id'])->count();
    }


    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'seo_alias'], 'required'],
            [['name', 'seo_alias'], 'trim'],
            [['description'], 'string'],
            [['name', 'seo_alias'], 'string', 'max' => 255],
            [['ordern'], 'integer'],
            [['name', 'seo_alias'], 'safe'],
        ];
    }

    public function behaviors() {
        return ArrayHelper::merge([
                    'translate' => [
                        'class' => TranslateBehavior::className(),
                        'translationAttributes' => [
                            'name',
                            'description'
                        ]
                    ],
                        ], parent::behaviors());
    }

}
