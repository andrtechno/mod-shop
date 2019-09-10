<?php

namespace panix\mod\shop\components;

use Yii;
use yii\base\Component;
use panix\mod\shop\models\Currency;

/**
 * Class to work with currencies
 */
class CurrencyManager extends Component
{

    /**
     * @var array available currencies
     */
    private $_currencies = [];

    /**
     * @var Currency main currency
     */
    private $_main;

    /**
     * @var Currency current active currency
     */
    private $_active;

    /**
     * @var Currency default currency
     */
    private $_default;

    /**
     * @var string
     */
    public $cacheKey = __CLASS__;

    /**
     * @var int Cache time
     */
    public $cacheTime = 3600;

    public function init()
    {
        foreach ($this->loadCurrencies() as $currency) {
            $this->_currencies[$currency['id']] = $currency;
            if ($currency['is_main'])
                $this->_main = $currency;
            if ($currency['is_default'])
                $this->_default = $currency;
        }
        $detectActive = $this->detectActive();
        if ($detectActive) {
            $this->setActive($detectActive['id']);
        }
    }

    /**
     * @return array
     */
    public function getCurrencies()
    {
        return $this->_currencies;
    }

    /**
     * @return array
     */
    public function getSymbol($id)
    {
        return $this->_currencies[$id]['symbol'];
    }

    /**
     * Detect user active currency
     * @return Currency
     */
    public function detectActive()
    {
        // Detect currency from session
        $sessionCurrency = Yii::$app->session['currency'];

        if ($sessionCurrency && isset($this->_currencies[$sessionCurrency]))
            return $this->_currencies[$sessionCurrency];
        return $this->_default;
    }

    /**
     * @param int $id currency id
     */
    public function setActive($id)
    {
        if (isset($this->_currencies[$id]))
            $this->_active = $this->_currencies[$id];
        else
            $this->_active = $this->_default;

        Yii::$app->session['currency'] = $this->_active['id'];
    }

    /**
     * get active currency
     * @return Currency
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * @return Currency main currency
     */
    public function getMain()
    {
        return $this->_main;
    }

    /**
     * Convert sum from main currency to selected currency
     * @param mixed $sum
     * @param mixed $id Currency. If not set, sum will be converted to active currency
     * @return float converted sum
     */
    public function convert($sum, $id = null)
    {
        if ($id !== null && isset($this->_currencies[$id]))
            $currency = $this->_currencies[$id];
        else
            $currency = $this->_active;

        return $currency['rate'] * $sum;
    }

    public function number_format($sum)
    {
        $format = number_format($sum, $this->_active['penny'], $this->_active['separator_thousandth'], $this->_active['separator_hundredth']);
        return iconv("windows-1251", "UTF-8", $format);
    }

    /**
     * Convert from active currency to main
     * @param $sum
     * @return float
     */
    public function activeToMain($sum)
    {
        return $sum / $this->active['rate'];
    }

    /**
     * @return array
     */
    private function loadCurrencies()
    {
        $currencies = Yii::$app->cache->get($this->cacheKey);

        if (!$currencies) {
            $currencies = Currency::find()
                ->asArray()
                ->all();
            Yii::$app->cache->set($this->cacheKey, $currencies, $this->cacheTime);
        }

        return $currencies;
    }

}
