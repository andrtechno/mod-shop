<?php

namespace panix\mod\shop\models;

//use panix\shop\models\query\ShopManufacturerQuery;
use \panix\engine\db\ActiveRecord;


class Currency extends ActiveRecord {

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
        return '{{%shop__currency}}';
    }

    public static function fpSeparator()
    {
        return array(
            ' ' => self::t('SPACE'),
            ',' => self::t('COMMA'),
            '.' => self::t('DOT')
        );
    }
    /**
     * @inheritdoc
     */
    public function rules() {
        return [

            //array('separator_hundredth, separator_thousandth', 'type', 'type' => 'string'),
            [['separator_hundredth','separator_thousandth'], 'string', 'max' => 5],
            [['name', 'rate','symbol','iso'], 'required'],
            [['name'], 'trim'],
            [['is_main','is_default','penny'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['ordern'], 'integer'],
            [['rate'], 'number'],
            [['name', 'rate','symbol','iso'], 'safe'],
        ];
    }



}
