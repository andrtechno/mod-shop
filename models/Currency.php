<?php

namespace panix\mod\shop\models;

//use panix\shop\models\query\ShopManufacturerQuery;
use yii\helpers\ArrayHelper;


class Currency extends \panix\engine\db\ActiveRecord {

    const MODULE_ID = 'shop';

    //public static function find() {
       // return new ShopManufacturerQuery(get_called_class());
    //}



    public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%shop_currency}}';
    }


    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'rate','symbol','iso'], 'required'],
            [['name'], 'trim'],
            [['is_main','is_default'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['ordern'], 'integer'],
            [['rate'], 'number'],
            [['name', 'rate','symbol','iso'], 'safe'],
        ];
    }



}
