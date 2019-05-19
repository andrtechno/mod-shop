<?php

namespace panix\mod\shop\components;

use Yii;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\TypeAttribute;
use panix\engine\controllers\WebController;
use yii\base\Exception;

class FilterController extends WebController
{

    public $query, $currentQuery;

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

    /**
     * @var string
     */
    public $maxprice, $minprice;


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

        if (Yii::$app->request->get('min_price'))
            $this->_currentMinPrice = Yii::$app->request->get('min_price');
        else
            $this->_currentMinPrice = Yii::$app->currency->convert($this->getMinPrice());

        return $this->_currentMinPrice;
    }

    /**
     * @return mixed
     */
    public function getCurrentMaxPrice()
    {
        if ($this->_currentMaxPrice !== null)
            return $this->_currentMaxPrice;

        if (Yii::$app->request->get('max_price'))
            $this->_currentMaxPrice = Yii::$app->request->get('max_price');
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


        if ($this->route == 'shop/category/view' || $this->route == 'shop/category/search') {
            $manufacturers = array_filter(explode(',', $request->getQueryParam('manufacturer')));
            $manufacturers = Manufacturer::getDb()->cache(function ($db) use ($manufacturers) {
                return Manufacturer::findAll($manufacturers);
            }, 3600);
        }

        //$manufacturersIds = array_filter(explode(',', $request->getQueryParam('manufacturer')));


        if ($request->getQueryParam('min_price') || $request->getQueryParam('min_price')) {
            $menuItems['price'] = [
                'label' => Yii::t('shop/default', 'FILTER_BY_PRICE') . ':',
                'itemOptions' => ['id' => 'current-filter-prices']
            ];
        }
        if ($request->getQueryParam('min_price')) {
            $menuItems['price']['items'][] = [
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()), 'currency' => Yii::$app->currency->active->symbol]),
                'linkOptions' => ['class' => 'remove', 'data-price' => 'min_price'],
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'min_price')
            ];
        }

        if ($request->getQueryParam('max_price')) {
            $menuItems['price']['items'][] = [
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MAX', ['value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active->symbol]),
                'linkOptions' => array('class' => 'remove', 'data-price' => 'max_price'),
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'max_price')
            ];
        }

        if ($this->route == 'shop/category/view') {
            if (!empty($manufacturers)) {
                $menuItems['manufacturer'] = array(
                    'label' => Yii::t('shop/default', 'FILTER_BY_MANUFACTURER') . ':',
                    'itemOptions' => array('id' => 'current-filter-manufacturer')
                );
                foreach ($manufacturers as $id => $manufacturer) {
                    $menuItems['manufacturer']['items'][] = [
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
                        'label' => $attribute->title . ':',
                        'itemOptions' => array('id' => 'current-filter-' . $attribute->name)
                    ];
                    foreach ($attribute->options as $option) {
                        if (isset($activeAttributes[$attribute->name]) && in_array($option->id, $activeAttributes[$attribute->name])) {
                            $menuItems[$attributeName]['items'][] = [
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
        $minPrice = Yii::$app->request->get('min_price');
        $maxPrice = Yii::$app->request->get('max_price');

        $cm = Yii::$app->currency;
        if ($cm->active->id !== $cm->main->id && ($minPrice > 0 || $maxPrice > 0)) {
            $minPrice = $cm->activeToMain($minPrice);
            $maxPrice = $cm->activeToMain($maxPrice);
        }

        if ($minPrice > 0)
            $this->query->applyMinPrice($minPrice);
        if ($maxPrice > 0)
            $this->query->applyMaxPrice($maxPrice);
    }

}