<?php

namespace panix\mod\shop\components;

use panix\engine\Html;
use Yii;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\TypeAttribute;
use panix\engine\controllers\WebController;
use yii\base\Exception;
use yii\helpers\Url;

class FilterController extends WebController
{
    /**
     * Sets page limits
     * @var array
     */
    public $allowedPageLimit;

    public $query, $currentQuery, $prices;

    private $_eavAttributes;
    /**
     * @var string min price in the query
     */
    private $_currentMinPrice = null;

    /**
     * @var string max price in the query
     */
    private $_currentMaxPrice = null;

    /**
     * @var string
     */
    public $_maxPrice, $_minPrice;

    public $currentUrl;
    public $itemView = '_view_grid';
    public $per_page;

    public function beforeAction($action)
    {

        Url::remember();
        if (Yii::$app->request->get('view')) {
            if (in_array(Yii::$app->request->get('view'), ['list', 'grid'])) {
                $this->itemView = '_view_' . Yii::$app->request->get('view');
            }
        }


        $this->allowedPageLimit = explode(',', Yii::$app->settings->get('shop', 'per_page'));


        $this->per_page = (int)$this->allowedPageLimit[0];
        if (Yii::$app->request->get('per_page') && in_array($_GET['per_page'], $this->allowedPageLimit)) {
            $this->per_page = (int)Yii::$app->request->get('per_page');
        }


        if (Yii::$app->request->get('price')) {
            $this->prices = explode(',',Yii::$app->request->get('price'));
            //foreach ($this->prices as $key=>$price) {
            // $this->prices[]=$price;
            //}
        }
        //print_r(Yii::$app->request->get('price'));die;
        $this->view->registerJs("
        var penny = '" . Yii::$app->currency->active['penny'] . "';
        var separator_thousandth = '" . Yii::$app->currency->active['separator_thousandth'] . "';
        var separator_hundredth = '" . Yii::$app->currency->active['separator_hundredth'] . "';
     ", yii\web\View::POS_HEAD, 'numberformat');
        return parent::beforeAction($action);
    }


    /**
     * @return string min price
     */
    public function getMinPrice()
    {
        if ($this->_minPrice !== null)
            return $this->_minPrice;

        // if ($this->currentQuery) {
        $this->_minPrice = $this->currentQuery->aggregatePrice('MIN');
        // }

        return $this->_minPrice;
    }

    /**
     * @return string max price
     */
    public function getMaxPrice()
    {
        $this->_maxPrice = $this->currentQuery->aggregatePrice('MAX');
        return $this->_maxPrice;
    }


    /**
     * @return mixed
     */
    public function getCurrentMinPrice()
    {
        if ($this->_currentMinPrice !== null)
            return $this->_currentMinPrice;


        if (isset($this->prices[0])) { //if (Yii::$app->request->get('min_price'))
            $this->_currentMinPrice = $this->prices[0];

        }else {

            $this->_currentMinPrice = Yii::$app->currency->convert($this->getMinPrice());
        }

        return $this->_currentMinPrice;
    }

    /**
     * @return mixed
     */
    public function getCurrentMaxPrice()
    {
        if ($this->_currentMaxPrice !== null)
            return $this->_currentMaxPrice;

        if (isset($this->prices[1])) //if (Yii::$app->request->get('max_price'))
            $this->_currentMaxPrice = $this->prices[1];//Yii::$app->request->get('max_price');
        else
            $this->_currentMaxPrice = Yii::$app->currency->convert($this->getMaxPrice());

        return $this->_currentMaxPrice;
    }

    public function getEavAttributes()
    {
        if (is_array($this->_eavAttributes))
            return $this->_eavAttributes;

        // Find category types

        $model = Product::find();
        $query = $model
            //->applyCategories($this->dataModel)
            ->published();

        unset($model);


        $query->addSelect(['type_id']);
        $query->addGroupBy(['type_id']);
        $query->distinct(true);

        //$typesIds = $query->createCommand()->queryColumn();
        $typesIds = Attribute::getDb()->cache(function () use ($query) {
            return $query->createCommand()->queryColumn();
        }, 3600);

        // Find attributes by type
        $query = Attribute::getDb()->cache(function () use ($typesIds) {
            return Attribute::find()
                ->andWhere(['IN', TypeAttribute::tableName() . '.type_id', $typesIds])
                ->useInFilter()
                ->addOrderBy(['ordern' => SORT_DESC])
                ->joinWith(['types', 'options'])
                ->all();
        }, 3600);
        /*$query = Attribute::find(['IN', '`types`.type_id', $typesIds])
            ->useInFilter()
            ->orderBy(['ordern' => SORT_DESC])
            ->joinWith(['types', 'options'])
            ->all();*/


        $this->_eavAttributes = [];
        foreach ($query as $attr)
            $this->_eavAttributes[$attr->name] = $attr;
        return $this->_eavAttributes;
    }

    public function getActiveAttributes()
    {
        $data = [];

        foreach (array_keys($_GET) as $key) {
            if (array_key_exists($key, $this->eavAttributes)) {

                if (empty($_GET[$key]) && isset($_GET[$key])) {
                    //	 throw new CHttpException(404, Yii::t('ShopModule.default', 'NOFIND_CATEGORY'));
                }

                if ((boolean)$this->eavAttributes[$key]->select_many === true) {
                    $data[$key] = explode(',', $_GET[$key]);
                } else {
                    $data[$key] = [$_GET[$key]];
                }
            }
        }
        return $data;
    }

    /**
     * Get active/applied filters to make easier to cancel them.
     */
    public function getActiveFilters()
    {
        $request = Yii::$app->request;
        // Render links to cancel applied filters like prices, manufacturers, attributes.
        $menuItems = [];


        if ($this->route == 'shop/catalog/view' || $this->route == 'shop/search/index') {
            $manufacturers = array_filter(explode(',', $request->getQueryParam('manufacturer')));
            $manufacturers = Manufacturer::getDb()->cache(function ($db) use ($manufacturers) {
                return Manufacturer::findAll($manufacturers);
            }, 3600);
        }

        //$manufacturersIds = array_filter(explode(',', $request->getQueryParam('manufacturer')));


        if ($request->getQueryParam('price')) {
            $menuItems['price'] = [
                'name' => 'price',
                'label' => Yii::t('shop/default', 'FILTER_BY_PRICE') . ':',
                'itemOptions' => ['id' => 'current-filter-prices']
            ];
        }
        if (isset(Yii::$app->controller->prices[0])) {
            if ($this->getCurrentMinPrice() > 0) {
                $menuItems['price']['items'][] = [
                    // 'name'=>'min_price',
                    'value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()),
                    'label' => Html::decode(Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()), 'currency' => Yii::$app->currency->active['symbol']])),
                    'linkOptions' => ['class' => 'remove', 'data-price' => 'min_price'],
                    'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'price', Yii::$app->controller->prices[0])
                ];
            }
        }

        if (isset(Yii::$app->controller->prices[1])) {
            if ($this->getCurrentMaxPrice() > 0) {
                $menuItems['price']['items'][] = [
                    // 'name'=>'max_price',
                    'value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()),
                    'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MAX', ['value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active['symbol']]),
                    'linkOptions' => array('class' => 'remove', 'data-price' => 'max_price'),
                    'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'price', Yii::$app->controller->prices[1])
                ];
            }
        }


        /*if ($request->getQueryParam('min_price') || $request->getQueryParam('min_price')) {
            $menuItems['price'] = [
                'name' => 'price',
                'label' => Yii::t('shop/default', 'FILTER_BY_PRICE') . ':',
                'itemOptions' => ['id' => 'current-filter-prices']
            ];
        }
        if ($request->getQueryParam('min_price')) {
            $menuItems['price']['items'][] = [
                // 'name'=>'min_price',
                'value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()),
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()), 'currency' => Yii::$app->currency->active['symbol']]),
                'linkOptions' => ['class' => 'remove', 'data-price' => 'min_price'],
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'min_price')
            ];
        }

        if ($request->getQueryParam('max_price')) {
            $menuItems['price']['items'][] = [
                // 'name'=>'max_price',
                'value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()),
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MAX', ['value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active['symbol']]),
                'linkOptions' => array('class' => 'remove', 'data-price' => 'max_price'),
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'max_price')
            ];
        }*/

        if ($this->route == 'shop/catalog/view') {
            if (!empty($manufacturers)) {
                $menuItems['manufacturer'] = [
                    'name' => 'manufacturer',
                    'label' => Yii::t('shop/default', 'FILTER_BY_MANUFACTURER') . ':',
                    'itemOptions' => ['id' => 'current-filter-manufacturer']
                ];
                foreach ($manufacturers as $id => $manufacturer) {
                    $menuItems['manufacturer']['items'][] = [
                        'value' => $manufacturer->id,
                        'label' => $manufacturer->name,
                        'linkOptions' => array(
                            'class' => 'remove',
                            'data-name' => 'manufacturer',
                            'data-target' => '#filter_manufacturer_' . $manufacturer->id
                        ),
                        'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'manufacturer', $manufacturer->id)
                    ];
                }
            }
        }

        // Process eav attributes
        $activeAttributes = $this->activeAttributes;
        if (!empty($activeAttributes)) {
            foreach ($activeAttributes as $attributeName => $value) {
                if (isset($this->eavAttributes[$attributeName])) {
                    $attribute = $this->eavAttributes[$attributeName];
                    $menuItems[$attributeName] = [
                        'name' => $attribute->name,
                        'label' => $attribute->title . ':',
                        'itemOptions' => array('id' => 'current-filter-' . $attribute->name)
                    ];
                    foreach ($attribute->options as $option) {
                        if (isset($activeAttributes[$attribute->name]) && in_array($option->id, $activeAttributes[$attribute->name])) {
                            $menuItems[$attributeName]['items'][] = [
                                'value' => $option->id,
                                'label' => $option->value . (($attribute->abbreviation) ? ' ' . $attribute->abbreviation : ''),
                                'linkOptions' => [
                                    'class' => 'remove',
                                    'data-name' => $attribute->name,
                                    'data-target' => "#filter_{$attribute->name}_{$option->id}"
                                ],
                                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, $attribute->name, $option->id)
                            ];
                            sort($menuItems[$attributeName]['items']);
                        }
                    }
                }
            }
        }

        return $menuItems;
    }

    public function applyPricesFilter()
    {
        $minPrice = (isset($this->prices[0])) ? $this->prices[0] : 0;
        $maxPrice = (isset($this->prices[1])) ? $this->prices[1] : 0;

        $cm = Yii::$app->currency;
        if ($cm->active['id'] !== $cm->main['id'] && ($minPrice > 0 || $maxPrice > 0)) {
            $minPrice = $cm->activeToMain($minPrice);
            $maxPrice = $cm->activeToMain($maxPrice);
        }

        if ($minPrice > 0)
            $this->query->applyPrice($minPrice, '>=');
        if ($maxPrice > 0)
            $this->query->applyPrice($maxPrice, '<=');
    }


    public function applyPricesFilter_OLD()
    {
        $minPrice = Yii::$app->request->get('min_price');
        $maxPrice = Yii::$app->request->get('max_price');

        $cm = Yii::$app->currency;
        if ($cm->active['id'] !== $cm->main['id'] && ($minPrice > 0 || $maxPrice > 0)) {
            $minPrice = $cm->activeToMain($minPrice);
            $maxPrice = $cm->activeToMain($maxPrice);
        }

        if ($minPrice > 0)
            $this->query->applyPrice($minPrice, '>=');
        if ($maxPrice > 0)
            $this->query->applyPrice($maxPrice, '<=');
    }

}