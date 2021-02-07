<?php

namespace panix\mod\shop\components;

use Yii;
use yii\base\Component;
use panix\mod\shop\models\Currency;
use yii\caching\DbDependency;

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
        if(Yii::$app->id != 'console') {
            $detectActive = $this->detectActive();
            if ($detectActive) {
                $this->setActive($detectActive['id']);
            }
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

        //Todo need set session
        //Yii::$app->session['currency'] = $this->_active['id'];
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
        $result = $sum;
        if ($id !== null && isset($this->_currencies[$id])) {
            $currency = $this->_currencies[$id];
            if ($id != $this->_active['id'] && $id != $this->_main['id']) {
                $result = $sum * $currency['rate'];
            } elseif ($id != $this->_active['id']) {
                $result = $sum / $this->_active['rate'];
            }
        } else {
            $currency = $this->_active;
            if ($id != $currency['id']) {
                $result = $sum / $currency['rate'];
            }
        }

        return $result;
    }

    /**
     * @param float|int|string $sum
     * @param int $decimals
     * @param bool|string $thousandth
     * @param bool|string $hundredth
     * @return string
     */
    public function number_format($sum, $decimals = 0, $thousandth = false, $hundredth = false)
    {
        if (!$thousandth)
            $thousandth = $this->_active['separator_thousandth'];

        if (!$hundredth)
            $hundredth = $this->_active['separator_hundredth'];

        if (!$decimals)
            $decimals = $this->_active['penny'];


        if (preg_match('/^(\d+\.00|\d+)$/', $sum, $match) && !$decimals) {
            $format = number_format($sum, 0, $hundredth, $thousandth);
        } else {
            $format = number_format($sum, $decimals, $hundredth, $thousandth);
        }
        return $format;
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
        $tableName = Currency::tableName();

        return Currency::find()->published()
            ->asArray()
            //->cache($this->cacheTime, new DbDependency(['sql' => "SELECT MAX(updated_at) FROM {$tableName}"]))
            ->all();
    }

}
