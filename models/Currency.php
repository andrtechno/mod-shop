<?php

namespace panix\mod\shop\models;

use panix\engine\CMS;
use panix\mod\shop\components\ProductPriceHistoryQueue;
use Yii;
use panix\engine\db\ActiveRecord;
use panix\mod\shop\models\query\CurrencyQuery;
use yii\db\Query;

/**
 * Class Currency
 * @property integer $id
 * @property boolean $is_main
 * @property string $name
 * @property float $rate
 * @property string $iso
 * @property string $symbol
 * @property string $is_default
 * @property boolean $penny
 * @property string $separator_thousandth
 * @property string $separator_hundredth
 */
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

    public static function find()
    {
        return new CurrencyQuery(get_called_class());
    }

    public static function currenciesList()
    {
        return [
            ['name' => 'Доллар', 'iso' => 'USD', 'symbol' => '&#36;'],
            ['name' => 'Гривна', 'iso' => 'UAH', 'symbol' => '&#8372;'],
            ['name' => 'Рубль', 'iso' => 'RUB', 'symbol' => '&x584;'],
            ['name' => 'Евро', 'iso' => 'EUR', 'symbol' => '&euro;'],
            ['name' => 'Фунт', 'iso' => 'GBP', 'symbol' => '&pound;'],
            ['name' => 'Юань', 'iso' => 'CNY', 'symbol' => '&yen;'],
            ['name' => 'Рубль (белорусский рубль)', 'iso' => 'BYN', 'symbol' => 'Br.'],
            ['name' => 'Тенге', 'iso' => 'KZT', 'symbol' => '&#8376;']
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //array('separator_hundredth, separator_thousandth', 'type', 'type' => 'string'),
            [['separator_hundredth', 'separator_thousandth'], 'string', 'max' => 5],
            [['name', 'rate', 'symbol', 'iso', 'penny'], 'required'],
            [['name'], 'trim'],
            [['is_main', 'is_default'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['ordern'], 'integer'],
            [['rate'], 'number'],
            [['name', 'rate', 'symbol', 'iso'], 'safe'],
        ];
    }

    public static function fpSeparator()
    {
        return [
            ' ' => self::t('SPACE'),
            ',' => self::t('COMMA'),
            '.' => self::t('DOT')
        ];
    }


    public function beforeSave($insert)
    {

        if ($this->attributes['rate'] <> $this->oldAttributes['rate']) {
            static::getDb()->createCommand()->insert('{{%shop__currency_history}}', [
                'currency_id' => $this->id,
                'rate' => $this->rate,
                'rate_old' => $this->rate_old,
                'created_at' => time(),
                'type' => ($this->oldAttributes['rate'] < $this->attributes['rate']) ? 1 : 0
            ])->execute();
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (!$insert && Yii::$app->queue && Yii::$app->id != 'console') {
           // Yii::$app->queue->channel = 'currency';

            if (isset($changedAttributes['rate'])) {

                if ($changedAttributes['rate'] <> $this->attributes['rate']) {

                    $query = (new Query())
                        ->select(['id', 'price', 'price_purchase'])
                        ->where(['currency_id' => $this->id])
                        ->from(Product::tableName());

                    foreach ($query->batch(500) as $items) {
                        Yii::$app->queue->push(new ProductPriceHistoryQueue([
                            'items' => $items,
                            'currency_id' => $this->id,
                            'currency_rate' => $this->rate,
                            'type' => ($changedAttributes['rate'] < $this->attributes['rate']) ? 1 : 0,
                        ]));
                    }
                }
            }

        }
        parent::afterSave($insert, $changedAttributes);
    }

}
