<?php

namespace panix\mod\shop\models;

use \panix\engine\db\ActiveRecord;

class Currency extends ActiveRecord
{

    const MODULE_ID = 'shop';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__currency}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //array('separator_hundredth, separator_thousandth', 'type', 'type' => 'string'),
            [['separator_hundredth', 'separator_thousandth'], 'string', 'max' => 5],
            [['name', 'rate', 'symbol', 'iso'], 'required'],
            [['name'], 'trim'],
            [['is_main', 'is_default', 'penny'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['ordern'], 'integer'],
            [['rate'], 'number'],
            [['name', 'rate', 'symbol', 'iso'], 'safe'],
        ];
    }

    public static function fpSeparator()
    {
        return array(
            ' ' => self::t('SPACE'),
            ',' => self::t('COMMA'),
            '.' => self::t('DOT')
        );
    }

}
