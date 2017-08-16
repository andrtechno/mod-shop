<?php

namespace panix\mod\shop\components;

use Yii;
use yii\base\Component;
use panix\mod\shop\models\ShopCurrency;

/**
 * Class to work with currencies
 */
class CurrencyManager extends Component {

    /**
     * @var array available currencies
     */
    private $_currencies = array();

    /**
     * @var ShopCurrency main currency
     */
    private $_main;

    /**
     * @var ShopCurrency current active currency
     */
    private $_active;

    /**
     * @var ShopCurrency default currency
     */
    private $_default;

    /**
     * @var string
     */
    public $cacheKey = 'currency_manager';

    public function init() {
        foreach ($this->loadCurrencies() as $currency) {
            $this->_currencies[$currency->id] = $currency;
            if ($currency->is_main)
                $this->_main = $currency;
            if ($currency->is_default)
                $this->_default = $currency;
        }

        $this->setActive($this->detectActive()->id);
    }

    /**
     * @return array
     */
    public function getCurrencies() {
        return $this->_currencies;
    }
    
    /**
     * @return array
     */
    public function getSymbol($id) {
        return $this->_currencies[$id]->symbol;
    }
    /**
     * Detect user active currency
     * @return ShopCurrency
     */
    public function detectActive() {
        // Detect currency from session
        $sessCurrency = Yii::$app->session['currency'];

        if ($sessCurrency && isset($this->_currencies[$sessCurrency]))
            return $this->_currencies[$sessCurrency];
        return $this->_default;
    }

    /**
     * @param int $id currency id
     */
    public function setActive($id) {
        if (isset($this->_currencies[$id]))
            $this->_active = $this->_currencies[$id];
        else
            $this->_active = $this->_default;

        Yii::$app->session['currency'] = $this->_active->id;
    }

    /**
     * get active currency
     * @return ShopCurrency
     */
    public function getActive() {
        return $this->_active;
    }

    /**
     * @return ShopCurrency main currency
     */
    public function getMain() {
        return $this->_main;
    }

    /**
     * Convert sum from main currency to selected currency
     * @param mixed $sum
     * @param mixed $id ShopCurrency. If not set, sum will be converted to active currency
     * @return float converted sum
     */
    public function convert($sum, $id = null) {
        if ($id !== null && isset($this->_currencies[$id]))
            $currency = $this->_currencies[$id];
        else
            $currency = $this->_active;

        return $currency->rate * $sum;
    }

    
    /**
     * Convert from active currency to main
     * @param $sum
     * @return float
     */
    public function activeToMain($sum) {
        return $sum / $this->getActive()->rate;
    }

    /**
     * @return array
     */
    public function loadCurrencies() {
        $currencies = Yii::$app->cache->get($this->cacheKey);

        if (!$currencies) {
            $currencies = ShopCurrency::find()
                    //->asArray()
                    ->all();
            Yii::$app->cache->set($this->cacheKey, $currencies,Yii::$app->settings->get('app','cache_time'));
        }

        return $currencies;
    }

}
