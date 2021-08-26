<?php

namespace panix\mod\shop\components;

use panix\engine\CMS;
use panix\engine\Html;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductAttributesEav;
use panix\mod\shop\models\traits\EavQueryTrait;
use yii\base\BaseObject;
use yii\base\Component;
use yii\db\ActiveQuery;
use panix\mod\shop\models\query\ProductQuery;
use yii\db\ActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class FilterV2 extends Component
{

    private $attributes;
    public $query;
    public $resultQuery;
    public $testQuery;
    public $cacheDuration = 86400;

    public $controller;
    public $activeAttributes;
    public $price_min;
    public $price_max;
    //public $selectedPriceMin;
    //public $selectedPriceMax;

    /**
     * @var string min price in the query
     */
    private $_currentMinPrice = null;

    /**
     * @var string max price in the query
     */
    private $_currentMaxPrice = null;

    private $_eavAttributes;


    /**
     * @var string
     */
    //public $_maxPrice, $_minPrice;
    public $prices;
    public $route;

    public function getActiveAttributes()
    {
        $data = [];
        $sss = (Yii::$app->request->post('filter')) ? Yii::$app->request->post('filter') : $_GET;

        foreach (array_keys($sss) as $key) {
            if (array_key_exists($key, $this->_eavAttributes)) {

                // if (empty($_GET[$key]) && isset($_GET[$key])) {
                //	 throw new CHttpException(404, Yii::t('shop/default', 'NOFIND_CATEGORY'));
                // }

                if ((boolean)$this->_eavAttributes[$key]->select_many === true) {
                    $data[$key] = (is_array($sss[$key])) ? $sss[$key] : explode(',', $sss[$key]);
                } else {

                    if (isset($sss[$key]))
                        $data[$key] = [$sss[$key]];
                }
            } else {
                //  $this->error404(Yii::t('shop/default', 'NOFIND_CATEGORY1'));
            }
        }

        return $data;
    }


    public function getActiveBrands()
    {
        $data = [];
        $post = Yii::$app->request->post('filter');

        if ($post && !in_array($this->route[0], ['/shop/brand/view'])) {
            $query = (isset($post['brand'])) ? $post['brand'] : Yii::$app->request->get('brand');
            if (is_array($query)) {
                $data = $query;
            } else {
                if (!empty($query))
                    $data = explode(',', $query);
            }

        }

        return $data;
    }

    protected $_route;

    public function setResultRoute($route)
    {
        $this->_route = $route;
    }

    public function getResultRoute()
    {
        $this->_route = $this->route;
        $this->_route[0] = '/' . $this->_route[0];
        $slides = Yii::$app->request->post('slide');
        $brands = $this->getActiveBrands();
        foreach ($this->getActiveAttributes() as $attribute => $values) {
            if (is_array($values)) {
                $this->_route[$attribute] = implode(',', $values);
            }

        }
        if ($brands) {
            $this->_route['brand'] = (is_array($brands)) ? implode(',', $brands) : $brands;
        }

        if ($slides) {
            foreach ($slides as $key => $values) {
                $showRoute = true;

                if ($key == 'price' && $values[1] == ceil($this->getMaxPrice()) && $values[0] == floor($this->getMinPrice())) {
                    $showRoute = false;
                }
                if ($showRoute)
                    $this->_route[$key] = implode('-', $values);

            }
        }


        if (Yii::$app->request->post('sort')) {
            $this->_route['sort'] = Yii::$app->request->post('sort');
        }
        if (Yii::$app->request->post('per-page')) {
            $this->_route['per-page'] = Yii::$app->request->post('per-page');
        }
        if (Yii::$app->request->post('view')) {
            $this->_route['view'] = Yii::$app->request->post('view');
        }

        return $this->_route;
    }


    public function __construct(ProductQuery $query, $config = [])
    {

        parent::__construct($config);
        $data = Yii::$app->request->post('filter');
        $slides = Yii::$app->request->post('slide');
        $this->resultQuery = clone $query;
        $this->query = clone $query;
        $this->setResultRoute($this->route);
        // $this->_route = $this->route;
        // $this->route = $this->getResultRoute();
        //  $this->setRoute($this->getActiveUrl());
        //  $this->setRoute($this->getActiveUrl());

        //  echo $this->resultQuery->createCommand()->rawSql;die;

        $this->attributes = $this->getEavAttributes();
        $this->activeAttributes = $this->getActiveAttributes();

        //Apply attributes
        $this->resultQuery->applyAttributes($this->activeAttributes);

        //Apply Brand's
        //$this->resultQuery->applyBrands($this->getActiveBrands());
        if (Yii::$app->request->get('brand') || isset($data['brand']) && Yii::$app->controller->route != '/shop/brand/view') {
            if (Yii::$app->request->get('brand')) {
                $brands = explode(',', Yii::$app->request->get('brand', ''));
            } else {
                $brands = $data['brand'];
            }
            // echo 'zzz';
            $this->resultQuery->applyBrands($brands);
        }

        if (isset($slides['price'])) {
            $this->prices = $slides['price'];
        }
        if (Yii::$app->request->get('price')) {
            if (preg_match('/^[0-9\-]+$/', Yii::$app->request->get('price'))) {
                $this->prices = explode('-', Yii::$app->request->get('price'));
            } else {
                // $this->error404();
            }
        }
        $this->resultQuery->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);


        // $this->getCurrentMinPrice();
        // $this->getCurrentMaxPrice();

        // $this->price_min = $this->getMinPrice();
        // $this->price_max = $this->getMaxPrice();

    }

    // public function getRoute()
    // {
    //return $this->route;
    // }

    //public function getResultQuery()
    //{
    //     return $this->resultQuery;
    // }

    public function getActiveFilters()
    {
        $request = Yii::$app->request;
        // Render links to cancel applied filters like prices, brands, attributes.
        $menuItems = [];


        if (Yii::$app->controller->route == 'shop/catalog/view' || Yii::$app->controller->route == 'shop/search/index') {
            $brands = array_filter(explode(',', $request->getQueryParam('brand')));
            $brands = Brand::getDb()->cache(function ($db) use ($brands) {
                return Brand::findAll($brands);
            }, 3600);
        }

        if ($request->getQueryParam('price')) {
            $menuItems['price'] = [
                'name' => 'price',
                'label' => Yii::t('shop/default', 'FILTER_BY_PRICE') . ':',
                'itemOptions' => ['id' => 'current-filter-prices']
            ];
            //if ($this->price_min > 0 && $this->price_max) {
            //  print_r($this->getCurrentMinPrice());die;
            $menuItems['price']['items'][] = [
                // 'name'=>'min_price',
                'value_url' => number_format($this->price_min, 0, '', ''),
                'value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()) . ' - ' . Yii::$app->currency->number_format($this->getCurrentMaxPrice()),
                'label' => Html::decode(Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()) . ' до ' . Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active['symbol']])),
                'options' => ['class' => 'remove', 'data' => [
                    'type' => 'slider',
                    'slider-max' => round($this->price_max),
                    'slider-min' => round($this->price_min),
                    'slider-current-max' => $this->getCurrentMaxPrice(),
                    'slider-current-min' => $this->getCurrentMinPrice(),
                ]],
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'price', $this->getCurrentMinPrice() . '-' . $this->getCurrentMaxPrice())
            ];
            // }
        }


        if (Yii::$app->controller->route == 'shop/catalog/view') {
            if (!empty($brands)) {
                $menuItems['brand'] = [
                    'name' => 'brand',
                    'label' => Yii::t('shop/default', 'FILTER_BY_BRAND') . ':',
                    'itemOptions' => ['id' => 'current-filter-brand']
                ];
                foreach ($brands as $id => $brand) {
                    $menuItems['brand']['items'][] = [
                        'value' => $brand->id,
                        'label' => $brand->name,
                        'options' => [
                            'class' => 'remove',
                            'data-type' => 'checkbox',
                            'data-name' => 'brand',
                            'data-target' => '#filter_brand_' . $brand->id
                        ],
                        'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'brand', $brand->id)
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
                        'itemOptions' => ['id' => 'current-filter-' . $attribute->name],
                        'items' => []
                    ];
                    foreach ($attribute->options as $option) {
                        if (isset($activeAttributes[$attribute->name]) && in_array($option->id, $activeAttributes[$attribute->name])) {
                            $menuItems[$attributeName]['items'][] = [
                                'value' => $option->id,
                                'label' => $option->value . (($attribute->abbreviation) ? ' ' . $attribute->abbreviation : ''),
                                'options' => [
                                    'class' => 'remove',
                                    'data-type' => 'checkbox',
                                    'data-name' => $attribute->name,
                                    'data-target' => "#filter_{$attribute->name}_{$option->id}"
                                ],
                                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, $attribute->name, $option->id)
                            ];
                            sort($menuItems[$attributeName]['items']);
                        }
                    }
                    //Если нет не одного итема, то делаем пустой пассив.
                    if (!$menuItems[$attributeName]['items']) {
                        $menuItems = [];
                    }

                }
            }
        }

        return $menuItems;
    }

    public function getCategoryAttributesCallback()
    {
        $data = [];
        $active = $this->activeAttributes;
//print_r(array_keys($this->_eavAttributes));die;

        foreach ($this->_eavAttributes as $attribute) {
            if (in_array($attribute->name, $this->accessAttributes)) {
                $data[$attribute->name] = [
                    'title' => $attribute->title,
                    'selectMany' => (boolean)$attribute->select_many,
                    'type' => (int)$attribute->type,
                    'key' => $attribute->name,
                    'filters' => []
                ];
                $totalCount = 0;
                $filtersCount = 0;
                foreach ($attribute->getOptions()->all() as $option) {
                    $count = $this->countAttributeProductsCallback($attribute, $option);
                    //$count=1;
                    //if ($count > 1) {


                    $ss = array_key_first($active);
                    //print_r($ss);
                    $countText = $count;
                    if (isset($active[$attribute->name])) {

                        //if($active[$attribute->name] != $attribute->name){
                        //    $countText = '+'.$count;
                        //}

                        if ($ss == $attribute->name && $count) {
                            $countText = '+' . $count;
                        }
                    }

                    $data[$attribute->name]['filters'][] = [
                        'title' => $option->value,
                        'count' => (int)$count,
                        'count_text' => $countText,
                        'data' => unserialize($option->data),
                        'abbreviation' => ($attribute->abbreviation) ? $attribute->abbreviation : null,
                        'key' => $attribute->name,
                        'queryParam' => (int)$option->id,
                    ];
                    if ($count > 0)
                        $filtersCount++;

                    $totalCount += $count;
                    //}
                }

                $data[$attribute->name]['totalCount'] = $totalCount;
                $data[$attribute->name]['filtersCount'] = $filtersCount;
            }
        }
        return $data;
    }

    public $accessAttributes = [];
    public $cacheKey;

    public function getRootCategoryAttributes()
    {

        //   print_r($this->cacheKey);die;
          $data = [];
        $cacheData = [];

       // $data = Yii::$app->cache->get($this->cacheKey);
        //if ($data === false) {
            $active = $this->activeAttributes;

            $first = array_key_first($active);

            foreach ($this->_eavAttributes as $attribute) {
                $data[$attribute->name] = [
                    'title' => $attribute->title,
                    'selectMany' => (boolean)$attribute->select_many,
                    'type' => (int)$attribute->type,
                    'key' => $attribute->name,
                    'filters' => []
                ];
                $totalCount = 0;
                $filtersCount = 0;
                foreach ($attribute->getOptions()->cache($this->cacheDuration)->all() as $option) {
                    $count = $this->countRootAttributeProducts($attribute, $option);


                    if ($count > 0) {
                        $data[$attribute->name]['filters'][] = [
                            'title' => $option->value,
                            'count' => (int)$count,
                            'count_text' => $count,
                            'data' => unserialize($option->data),
                            'abbreviation' => ($attribute->abbreviation) ? $attribute->abbreviation : null,
                            'key' => $attribute->name,
                            'queryParam' => (int)$option->id,
                        ];
                    }
                }
                $data[$attribute->name]['totalCount'] = 11;
                $data[$attribute->name]['filtersCount'] = count($data[$attribute->name]['filters']);
            }

            //Yii::$app->cache->set($this->cacheKey, $data, Yii::$app->db->queryCacheDuration);
       // }
        //  CMS::dump($data);die;
        return $data;
    }

    public function getCategoryAttributes()
    {

        $attributes = $this->getRootCategoryAttributes();

        $data = [];
        if ($attributes) {
            foreach ($attributes as $key => $attribute) {

                $data[$key] = [
                    'title' => $attribute['title'],
                    'selectMany' => (boolean)$attribute['selectMany'],
                    'type' => (int)$attribute['type'],
                    'key' => $attribute['key'],
                    'filters' => []
                ];
                foreach ($attribute['filters'] as $option) {
                    //  print_r($option);die;
                    $count = $this->countAttributeProducts2($key, $option['queryParam']);
                    // $data[$key][$option['queryParam']]=$count;

                    $data[$key]['filters'][] = [
                        'title' => $option['title'],
                        'count' => (int)$count,
                        'count_text' => $count,

                        //  'abbreviation' => ($attribute->abbreviation) ? $attribute->abbreviation : null,
                        'key' => $key,
                        'queryParam' => $option['queryParam'],
                    ];
                }
                $data[$key]['totalCount'] = 11;
                $data[$key]['filtersCount'] = count($data[$key]['filters']);
            }
        }
        // CMS::dump($data);die;
        return $data;

        $cacheData = [];
        //$data = Yii::$app->cache->get('catalog/ukhod-dla-volos/sampun-dla-volos');
        //if ($data === false) {
        $active = $this->activeAttributes;

        $first = array_key_first($active);

        foreach ($this->_eavAttributes as $attribute) {
            $data[$attribute->name] = [
                'title' => $attribute->title,
                'selectMany' => (boolean)$attribute->select_many,
                'type' => (int)$attribute->type,
                'key' => $attribute->name,
                'filters' => []
            ];
            $totalCount = 0;
            $filtersCount = 0;
            foreach ($attribute->getOptions()->cache($this->cacheDuration)->all() as $option) {
                $count = $this->countAttributeProducts($attribute, $option);
                //$count=1;


                //print_r($ss);
                $countText = $count;
                if (isset($active[$attribute->name])) {

                    // if($first == $attribute->name){
                    $countText = '+' . $count;
                    // }
                }

                //if ($count > 1) {
                $data[$attribute->name]['filters'][] = [
                    'title' => $option->value,
                    'count' => (int)$count,
                    'count_text' => $countText,
                    'data' => unserialize($option->data),
                    'abbreviation' => ($attribute->abbreviation) ? $attribute->abbreviation : null,
                    'key' => $attribute->name,
                    'queryParam' => (int)$option->id,
                ];
                if ($count > 0) {
                    $cacheData[$attribute->name][(int)$option->id] = $option->value;
                }
                if ($count > 0)
                    $filtersCount++;

                $totalCount += $count;
                //}
            }

            $data[$attribute->name]['totalCount'] = $totalCount;
            $data[$attribute->name]['filtersCount'] = $filtersCount;
            if ($attribute->sort == SORT_ASC) {
                sort($data[$attribute->name]['filters']);
            } elseif ($attribute->sort == SORT_DESC) {
                rsort($data[$attribute->name]['filters']);
            }
        }

        //Yii::$app->cache->set('catalog/ukhod-dla-volos/sampun-dla-volos', $data, 100000);
        //}
        CMS::dump($data);
        die;
        return $data;
    }

    public function countAttributeProducts($attribute, $option)
    {

        /** @var Product|ActiveQuery $model */
        $model = clone $this->query;

        $model->groupBy = false;
        $model->select('COUNT(*)');

        $newData = [];
        //echo $model->createCommand()->rawSql;die;
        $newData[$attribute->name][] = $option->id;

        $newData2 = [];
        $ss = array_key_first($this->activeAttributes);

        if ($attribute->type != Attribute::TYPE_SELECT_MANY) {
            foreach ($this->activeAttributes as $key => $p) {
                if ($key != $attribute->name) {
                    $newData[$key] = $p;

                }
            }
        }

        $model->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);
        if (Yii::$app->request->get('brand')) {

            $brands = explode(',', Yii::$app->request->get('brand', ''));
            $model->applyBrands($brands);
        }


        //$newData = ArrayHelper::merge($newData,$this->activeAttributes);

        // echo $model->createCommand()->rawSql;
        // echo '<br><br><br>';
        /** @var EavQueryTrait|ActiveQuery $model */
        // $model->withEavAttributes($newData);
//print_r($newData);die;
        if ($newData)
            $model->getFindByEavAttributes2($newData);

        if ($attribute->name == 'pol') {

        }
        // echo $model->createCommand()->rawSql;       die;

        return $model->createCommand()->queryScalar();
    }


    public function countAttributeProducts2($attribute, $option)
    {

        /** @var Product|ActiveQuery $model */
        $model = clone $this->query;

        $model->groupBy = false;
        $model->select('COUNT(*)');

        $newData = [];
        //echo $model->createCommand()->rawSql;die;
        $newData[$attribute][] = $option;

        $newData2 = [];
        $ss = array_key_first($this->activeAttributes);

        // if ($attribute->type != Attribute::TYPE_SELECT_MANY) {
        foreach ($this->activeAttributes as $key => $p) {
            if ($key != $attribute) {
                $newData[$key] = $p;

            }
        }
        //  }

        $model->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);
        if (Yii::$app->request->get('brand')) {

            $brands = explode(',', Yii::$app->request->get('brand', ''));
            $model->applyBrands($brands);
        }


        //$newData = ArrayHelper::merge($newData,$this->activeAttributes);

        // echo $model->createCommand()->rawSql;
        // echo '<br><br><br>';
        /** @var EavQueryTrait|ActiveQuery $model */
        // $model->withEavAttributes($newData);
//print_r($newData);die;
        if ($newData)
            $model->getFindByEavAttributes2($newData);


        // echo $model->createCommand()->rawSql;       die;

        return $model->createCommand()->queryScalar();
    }

    public function countRootAttributeProducts($attribute, $option)
    {
        /** @var Product|ActiveQuery $model */
        $model = clone $this->query;

        $model->groupBy = false;
        $model->select('COUNT(*)');

        $newData = [];
        $newData[$attribute->name][] = $option->id;

        foreach ($this->activeAttributes as $key => $p) {
            if ($key != $attribute->name) {
                $newData[$key] = $p;
            }
        }

        if ($attribute->name == 'strana_proizvoditela') {
//echo $model->createCommand()->rawSql;die;
        }
        if ($newData)
            $model->getFindByEavAttributes2($newData);


        return $model->createCommand()->queryScalar();
    }


    public function countAttributeProductsCallback($attribute, $option)
    {

        /** @var Product|ActiveQuery $model */
        $model = clone $this->query;

        $model->select('COUNT(*)');
        $newData = [];
        $newData[$attribute->name][] = $option->id;


        foreach ($this->activeAttributes as $key => $p) {
            if ($key != $attribute->name) {
                $newData[$key] = $p;
            }
        }
        $filter = Yii::$app->request->post('filter');
        if ((isset($filter['brand']) || Yii::$app->request->get('brand')) && !in_array(Yii::$app->controller->route, ['shop/brand/view'])) {
            if (Yii::$app->request->get('brand')) {
                die('test!!!');
                $brands = explode(',', Yii::$app->request->get('brand'));
            } else {
                $brands = Yii::$app->request->get('brand');
            }

            $model->applyBrands($filter['brand']);

        }

        $sliders = Yii::$app->request->post('slide');
        if ($sliders) {
            if (isset($sliders['price'])) {
                //  print_r($this->price_max);
                //  print_r($this->price_min);
                //  die;
                $model->applyRangePrices($sliders['price'][0], $sliders['price'][1]);
            }
        }


        //$newData = ArrayHelper::merge($newData,$this->activeAttributes);

        // echo $model->createCommand()->rawSql;
        // echo '<br><br><br>';
        /** @var EavQueryTrait|ActiveQuery $model */
        // $model->withEavAttributes($newData);

        $model->getFindByEavAttributes2($newData);

        //  $model->groupBy = false;
        //$model->cache($this->cacheDuration);
        // $res->distinct(true);
        // print_r($newData);die;


        //$dependencyQuery = $model;
        //$dependencyQuery->select('COUNT(*)');
        //$dependency = new DbDependency([
        //     'sql' => $dependencyQuery->createCommand()->rawSql,
        //]);


        // $count = Attribute::getDb()->cache(function () use ($model) {
        //     return $model->count();
        // }, 1, $dependency);
        Yii::info($model->createCommand()->rawSql);
        if ($attribute->name == 'tip') {

        }
        //echo $model->createCommand()->rawSql;die;
        return $model->createCommand()->queryScalar();
    }


    public function getEavAttributes()
    {
        if (is_array($this->_eavAttributes))
            return $this->_eavAttributes;


        $queryCategoryTypes = clone $this->query; //Product::find();
        // $queryCategoryTypes = $this->currentQuery;
        //if ($this->model instanceof Category) {
        //     $queryCategoryTypes->applyCategories($this->model);
        // } elseif ($this->model instanceof Brand) {
        //     $queryCategoryTypes->applyBrands($this->model->id);
        // }

        //$queryCategoryTypes->published();
        $queryCategoryTypes->select(Product::tableName() . '.type_id');
        $queryCategoryTypes->groupBy(Product::tableName() . '.type_id');
        $queryCategoryTypes->distinct(true);
        $queryCategoryTypes->orderBy=false;

        $typesIds = $queryCategoryTypes->createCommand()->queryColumn();


        $query = Attribute::find()
            //->where(['IN', '`types`.`type_id`', $typesIds])
            ->where(['IN', '`type`.`type_id`', $typesIds])
            ->andWhere(['IN', 'type', [Attribute::TYPE_DROPDOWN, Attribute::TYPE_SELECT_MANY, Attribute::TYPE_CHECKBOX_LIST, Attribute::TYPE_RADIO_LIST]])
            ->distinct(true)
            ->useInFilter()
            ->sort()
            ->orderBy(null)
            ->joinWith(['types type', 'options']);


        $result = $query->all();

        $this->_eavAttributes = [];
        foreach ($result as $attr) {
            $this->_eavAttributes[$attr->name] = $attr;
        }
        return $this->_eavAttributes;
    }


    /**
     * @return string min price
     */
    public function getMinPrice()
    {
        // if ($this->_minPrice !== null)
        //    return $this->_minPrice;
        $res = clone $this->query;
        // if ($this->currentQuery) {
        $result = $res->aggregatePrice('MIN')->asArray()->one();
        // if (isset($result['aggregation_price'])) {
        return $result['aggregation_price'];
        // }
        // }

        // return $this->_minPrice;
    }

    public function getResultMaxPrice()
    {
        // if ($this->_minPrice !== null)
        //    return $this->_minPrice;

        $res = clone $this->resultQuery;
        // if ($this->currentQuery) {
        $result = $res->aggregatePrice('MAX')->asArray()->one();
        // if (isset($result['aggregation_price'])) {
        return $result['aggregation_price'];
        // }
        // }

        // return $this->_minPrice;
    }

    public function getResultMinPrice()
    {
        // if ($this->_minPrice !== null)
        //    return $this->_minPrice;
        $res = clone $this->resultQuery;
        //echo $res->createCommand()->rawSql;die;
        // if ($this->currentQuery) {
        $result = $res->aggregatePrice('MIN')->asArray()->one();
        // if (isset($result['aggregation_price'])) {
        return $result['aggregation_price'];
        // }
        // }

        // return $this->_minPrice;
    }

    /**
     * @return string max price
     */
    public function getMaxPrice()
    {
        $res = clone $this->query;

        $result = $res->aggregatePrice('MAX')->asArray()->one();
        // if (isset($result['aggregation_price'])) {
        return $result['aggregation_price'];
        // }
        // return $this->_maxPrice;
    }


    /**
     * @return mixed
     */
    public function getCurrentMinPrice()
    {
        if ($this->_currentMinPrice !== null)
            return $this->_currentMinPrice;

        $this->_currentMinPrice = (isset($this->prices[0])) ? trim($this->prices[0]) : Yii::$app->currency->convert($this->price_min);

        return $this->_currentMinPrice;
    }

    /**
     * @return string
     */
    public function getCurrentMaxPrice()
    {
        if ($this->_currentMaxPrice !== null)
            return $this->_currentMaxPrice;

        $this->_currentMaxPrice = (isset($this->prices[1])) ? trim($this->prices[1]) : Yii::$app->currency->convert($this->price_max);

        return $this->_currentMaxPrice;
    }


    public function getCategoryBrands()
    {

        $queryClone = clone $this->query;

        //    echo $queryClone->createCommand()->rawSql;die;

        $queryMan = $queryClone->addSelect(['brand_id', Product::tableName() . '.id']);
        $queryMan->joinWith([
            'brand' => function (\yii\db\ActiveQuery $query) {
                $query->andWhere([Brand::tableName() . '.switch' => 1]);
            },
        ]);
        //$queryMan->->applyMaxPrice($this->convertCurrency(Yii::$app->request->getQueryParam('max_price')))
        //$queryMan->->applyMinPrice($this->convertCurrency(Yii::$app->request->getQueryParam('min_price')))

        $queryMan->andWhere('brand_id IS NOT NULL');
        $queryMan->groupBy('brand_id');

//echo $queryMan->createCommand()->rawSql;die;
        // $brands = $queryMan->all();


        $brands = Brand::getDb()->cache(function ($db) use ($queryMan) {
            return $queryMan
                //->joinWith('translations as translate')
                //->orderBy(['translate.name'=>SORT_ASC])
                ->all();
        }, $this->cacheDuration);


        //$brands =$queryMan->all();
        //echo $q->createCommand()->rawSql;die;
        $data = [
            'title' => Yii::t('shop/default', 'FILTER_BY_BRAND'),
            'selectMany' => true,
            'filters' => []
        ];

        if ($brands) {

            foreach ($brands as $m) {

                $m = $m->brand;

                if ($m) {
                    $query = clone $this->query;
                    // echo $query->createCommand()->rawSql;die;
                    // $query = Product::find();
                    //  $query->published();

                    // $query->applyCategories($this->model);
                    //$query->andWhere([Product::tableName() . '.main_category_id' => $this->model->id]);


                    //$q->applyMinPrice($this->convertCurrency(Yii::app()->request->getQuery('min_price')))
                    //$q->applyMaxPrice($this->convertCurrency(Yii::app()->request->getQuery('max_price')))
                    $query->applyBrands($m->id);

                    // if (Yii::$app->request->get('q') && Yii::$app->requestedRoute == 'shop/search/index') {
                    //     $query->applySearch(Yii::$app->request->get('q'));
                    // }

                    // $query->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);
                    if (Yii::$app->request->get('brand')) {
                        $brandsList = explode(',', Yii::$app->request->get('brand', ''));
                        //$query->applyBrands($brands);
                    }

                    /*$dependencyQuery = $query;
                    $dependencyQuery->select('COUNT(*)');
                    $dependency = new DbDependency([
                        'sql' => $dependencyQuery->createCommand()->rawSql,
                    ]);

                    $count = Product::getDb()->cache(function () use ($query) {
                        return $query->count();
                    }, 3600 * 24, $dependency);*/


                    $query->orderBy = false;
                    if ($m->id == 89) {
                        //echo $query->createCommand()->rawSql;die;
                    }
                    $count = $query->count();

                    $data['filters'][] = [
                        'title' => $m->name,
                        'count' => (int)$count,
                        'count_text' => $count,
                        'key' => 'brand',
                        'queryParam' => $m->id,
                    ];
                    sort($data['filters']);
                } else {
                    die('err brand');
                }
            }
        }

        return $data;
    }


    public function getCategoryBrandsCallback()
    {


        /** @var ProductQuery $query */
        $query = Product::find();
        // $query = clone $this->query;

        //echo $query->createCommand()->rawSql;die;


        if (Yii::$app->request->get('q') && Yii::$app->requestedRoute == 'shop/search/index') {
            //$query->applySearch(Yii::$app->request->get('q'));
        }
        //  $query->published();


        $queryClone = clone $this->query;

        $queryMan = $queryClone->addSelect(['brand_id', Product::tableName() . '.id']);
        $queryMan->joinWith([
            'brand' => function (\yii\db\ActiveQuery $query) {
                $query->andWhere([Brand::tableName() . '.switch' => 1]);
            },
        ]);
        //$queryMan->->applyMaxPrice($this->convertCurrency(Yii::$app->request->getQueryParam('max_price')))
        //$queryMan->->applyMinPrice($this->convertCurrency(Yii::$app->request->getQueryParam('min_price')))

        $queryMan->andWhere('`brand_id` IS NOT NULL');
        $queryMan->groupBy('brand_id');

        $brands = $queryMan->cache($this->cacheDuration)->all();
        //  echo $queryMan->createCommand()->rawSql;die;

        /*$brands = Brand::getDb()->cache(function ($db) use ($queryMan) {
            return $queryMan
                //->joinWith('translations as translate')
                //->orderBy(['translate.name'=>SORT_ASC])
                ->all();
        }, $this->cacheDuration);*/

//print_r($brands);die;
        //$brands =$queryMan->all();
        //echo $q->createCommand()->rawSql;die;
        $data = [
            'title' => Yii::t('shop/default', 'FILTER_BY_BRAND'),
            'selectMany' => true,
            'filters' => []
        ];

        if ($brands) {

            foreach ($brands as $m) {

                $m = $m->brand;

                if ($m) {
                    /*$query = Product::find();
                    $query->published();
                    if ($this->model) {
                        $query->applyCategories($this->model);
                        //$query->andWhere([Product::tableName() . '.main_category_id' => $this->model->id]);
                    }

                    //$q->applyMinPrice($this->convertCurrency(Yii::app()->request->getQuery('min_price')))
                    //$q->applyMaxPrice($this->convertCurrency(Yii::app()->request->getQuery('max_price')))
                    $query->applyBrands($m->id);

                    if (Yii::$app->request->get('q') && Yii::$app->requestedRoute == 'shop/search/index') {
                        $query->applySearch(Yii::$app->request->get('q'));
                    }*/


                    /*$dependencyQuery = $query;
                    $dependencyQuery->select('COUNT(*)');
                    $dependency = new DbDependency([
                        'sql' => $dependencyQuery->createCommand()->rawSql,
                    ]);

                    $count = Product::getDb()->cache(function () use ($query) {
                        return $query->count();
                    }, 3600 * 24, $dependency);*/

                    $query = clone $this->query; //resultQuery
                    $query->orderBy = false;
                    $s = false;
                    if (Yii::$app->request->get('brand')) {
                        $bra = explode(',', Yii::$app->request->get('brand'));
                        foreach ($bra as $b) {
                            if ($m->id == $b) {
                                $s = true;
                            }
                        }
                    }
                    if (!$s)
                        $query->applyBrands($m->id);
                    // $query->andWhere(['!=','brand_id',$m->id]);

                    $query->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);
                    $newData = [];
                    foreach ($this->activeAttributes as $key => $p) {
                        $newData[$key] = $p;
                    }

                    /** @var EavQueryTrait|ActiveQuery $model */
                    // $model->withEavAttributes($newData);
                    $query->getFindByEavAttributes2($newData);

                    $sliders = Yii::$app->request->post('slide');
                    if ($sliders) {
                        if (isset($sliders['price'])) {
                            $query->applyRangePrices($sliders['price'][0], $sliders['price'][1]);
                        }
                    }

                    // echo $query->createCommand()->rawSql;die;
                    $count = $query->count();

                    $data['filters'][] = [
                        'title' => $m->name,
                        'count' => (int)$count,
                        'count_text' => $count,
                        'key' => 'brand',
                        'queryParam' => $m->id,
                    ];
                    sort($data['filters']);
                } else {
                    die('err brand');
                }
            }
        }

        return $data;
    }
}
