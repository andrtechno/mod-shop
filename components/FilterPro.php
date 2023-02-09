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
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use panix\mod\shop\models\query\ProductQuery;
use yii\db\ActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

class FilterPro extends Component
{

    private $attributes;
    public $query;
    public $resultQuery;
    public $cacheDuration = 86400;

    public $controller;
    public $activeAttributes;
    public $price_min;
    public $price_max;
    public $cacheKey;

    public $min;
    public $max;


    /**
     * @var string min price in the query
     */
    private $_currentMinPrice = null;

    /**
     * @var string max price in the query
     */
    private $_currentMaxPrice = null;
    private $_eavAttributes;

    public $accessAttributes = [];

    /**
     * @var string
     */
    //public $_maxPrice, $_minPrice;
    public $prices;
    public $route;

    protected $_route;

    public function getActiveAttributes()
    {
        $data = [];
        $filter = (Yii::$app->request->post('filter')) ? Yii::$app->request->post('filter') : $_GET;

        foreach (array_keys($filter) as $key) {
            if (array_key_exists($key, $this->_eavAttributes)) {
                if ((boolean)$this->_eavAttributes[$key]->select_many === true) {
                    $data[$key] = (is_array($filter[$key])) ? $filter[$key] : explode(',', $filter[$key]);
                } else {

                    if (isset($filter[$key]))
                        $data[$key] = [$filter[$key]];
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

                if ($key == 'price' && $values[1] == $this->min && $values[0] == $this->max) {
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
        $this->min = (int)floor($this->getMinPrice());
        $this->max = (int)ceil($this->getMaxPrice());
        if (($this->getCurrentMinPrice() != $this->min) || ($this->getCurrentMaxPrice() != $this->max)) {
            $this->resultQuery->applyRangePrices($this->getCurrentMinPrice(), $this->getCurrentMaxPrice());
        }


    }

    public function getActiveFilters()
    {
        $request = Yii::$app->request;
        // Render links to cancel applied filters like prices, brands, attributes.
        $menuItems = [];

        if (in_array(Yii::$app->controller->route, ['shop/catalog/view', 'shop/catalog/sales', 'shop/catalog/new', 'shop/search/index'])) {
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
            $menuItems['price']['items'][] = [
                // 'name'=>'min_price',
                'value_url' => number_format($this->price_min, 0, '', ''),
                'value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()) . ' - ' . Yii::$app->currency->number_format($this->getCurrentMaxPrice()),
                'label' => Html::decode(Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()) . ' до ' . Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active['symbol']])),
                'options' => ['class' => 'remove', 'data' => [
                    'type' => 'slider',
                    'slider-current-max' => $this->getCurrentMaxPrice(),
                    'slider-current-min' => $this->getCurrentMinPrice(),
                    'target' => 'price'
                ]],
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'price', $this->getCurrentMinPrice() . '-' . $this->getCurrentMaxPrice())
            ];
        }


        if (in_array(Yii::$app->controller->route, ['shop/catalog/view', 'shop/catalog/sales', 'shop/catalog/new', 'shop/search/index'])) {
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

        foreach ($this->getRootCategoryAttributes() as $attribute) {
            $data[$attribute['key']] = [
                'title' => $attribute['title'],
                'selectMany' => (boolean)$attribute['selectMany'],
                'type' => (int)$attribute['type'],
                'key' => $attribute['key'],
                'disable' => false,
                'changeCount' => true,
                'filters' => []
            ];

            $totalCount = 0;
            $filtersCount = 0;
            foreach ($attribute['filters'] as $option) {

                //$count=0;
                if (isset($active[$attribute['key']])) {
                    if (in_array($option['queryParam'], $active[$attribute['key']])) {
                        //$count = $this->countAttributeProductsCallback($attribute, $option);
                    }

                }
                $count = $this->countAttributeProductsCallback($attribute, $option);
                $first = array_key_first($active);
                $countText = $count;
                if (isset($active[$attribute['key']])) {
                    if ($first == $attribute['key'] && $count) {
                        $countText = '+' . $count;
                    }
                }

                $data[$attribute['key']]['filters'][] = [
                    'title' => $option['title'],
                    'count' => (int)$count,
                    'count_text' => $countText,
                    'key' => $option['key'],
                    'queryParam' => (int)$option['queryParam'],
                ];
                if ($count > 0)
                    $filtersCount++;

                $totalCount += $count;

            }
            $data[$attribute['key']]['totalCount'] = $totalCount;
            $data[$attribute['key']]['filtersCount'] = $filtersCount;
        }


        return $data;
    }

    public function getRootCategoryAttributes()
    {


        $data = Yii::$app->cache->get($this->cacheKey . '-attrs');
        if ($data === false) {
            //$data = [];
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
                foreach ($attribute->getOptions()->cache(0, new TagDependency(['tags' => 'attribute-' . $attribute->name]))->all() as $option) {
                    $count = $this->countRootAttributeProducts($attribute, $option);


                    if ($count > 0) {
                        $totalCount += $count;
                        $data[$attribute->name]['filters'][] = [
                            'title' => (Yii::$app->language == 'uk') ? $option->value_uk : $option->value,
                            'count' => (int)$count,
                            'count_text' => $count,
                            'data' => ($option->data) ? Json::decode($option->data) : [],
                            'abbreviation' => ($attribute->abbreviation) ? $attribute->abbreviation : null,
                            'key' => $attribute->name,
                            'queryParam' => (int)$option->id,
                        ];
                    }
                }
                $data[$attribute->name]['totalCount'] = $totalCount;
                $data[$attribute->name]['filtersCount'] = count($data[$attribute->name]['filters']);
                if ($attribute->sort == SORT_ASC) {
                    sort($data[$attribute->name]['filters']);
                } elseif ($attribute->sort == SORT_DESC) {
                    rsort($data[$attribute->name]['filters']);
                }
            }
            Yii::$app->cache->set($this->cacheKey . '-attrs', $data, 3600 * 24 * 7);
        }
        return $data;
    }

    public function countAttributeProducts($attribute, $option)
    {

        /** @var Product|ActiveQuery $model */
        $model = clone $this->query;
        $model->groupBy = false;
        $model->select('COUNT(*)');

        $newData = [];
        $newData[$attribute->name][] = $option->id;

        $newData2 = [];
        $first = array_key_first($this->activeAttributes);

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

        /** @var EavQueryTrait|ActiveQuery $model */
        if ($newData)
            $model->getFindByEavAttributes2($newData);

        return $model->createCommand()->queryScalar();
    }

    public function countRootAttributeProducts($attribute, $option)
    {
        /** @var Product|ActiveQuery $model */
        $model = clone $this->query;

        $model->groupBy = false;
        $model->orderBy = false;
        $model->select('COUNT(*)');

        $newData = [];
        $newData[$attribute->name][] = $option->id;

        foreach ($this->activeAttributes as $key => $p) {
            if ($key != $attribute->name) {
                $newData[$key] = $p;
            }
        }

        if ($newData)
            $model->getFindByEavAttributes2($newData);

        /*$model->cache(999999, new TagDependency([
            'tags' => [
                'attribute-' . $attribute->name,
                'attribute-' . $attribute->name . '-' . $option->id
            ]
        ]));*/
        //echo $this->cacheKey;die;
        //$model->cache(999999, new TagDependency(['tags'=>'attribute-' . $attribute->name]));
        $data2 = Yii::$app->cache->getOrSet($this->cacheKey . '-' . $option->id, function () use ($model) {
            return $model->createCommand()->queryScalar();
        }, 0);
        return $data2; //$model->createCommand()->queryScalar();
    }


    public function countAttributeProductsCallback($attribute, $option)
    {

        /** @var Product|ActiveQuery $model */
        $this->query->orderBy = false;
        $model = clone $this->query;
        $model->select('COUNT(*)');

        $newData = [];
        //$newData[$attribute->name][] = $option->id;
        $newData[$attribute['key']][] = $option['queryParam'];
        foreach ($this->activeAttributes as $key => $p) {
            if ($key != $attribute['key']) {
                $newData[$key] = $p;
            }
        }
        $filter = Yii::$app->request->post('filter');
        if ((isset($filter['brand']) || Yii::$app->request->get('brand')) && !in_array(Yii::$app->controller->route, ['shop/brand/view'])) {
            if (Yii::$app->request->get('brand')) {
                die('___!!!');
                $brands = explode(',', Yii::$app->request->get('brand'));
            } else {
                $brands = Yii::$app->request->get('brand');
            }
            $model->applyBrands($filter['brand']);
        }

        if (isset($this->prices[0], $this->prices[1])) {
            $min = (int)$this->prices[0];
            $max = (int)$this->prices[1];
            if ($this->min != $min || $this->max != $max) {
                $model->applyRangePrices($min, $max);
            }
        }

        /** @var EavQueryTrait|ActiveQuery $model */
        $model->getFindByEavAttributes2($newData);
        $model->cache(0, new TagDependency(['tags' => 'attribute-' . $attribute['key'] . '-' . $option['queryParam']]));
        return $model->createCommand()->queryScalar();
    }


    public function getEavAttributes()
    {
        if (is_array($this->_eavAttributes))
            return $this->_eavAttributes;


        $queryCategoryTypes = clone $this->query; //Product::find();
        $queryCategoryTypes->select(Product::tableName() . '.type_id');
        $queryCategoryTypes->groupBy(Product::tableName() . '.type_id');
        $queryCategoryTypes->distinct(true);
        $queryCategoryTypes->orderBy = false;

        $typesIds = $queryCategoryTypes->createCommand()->queryColumn();


        $query = Attribute::find()
            //->where(['IN', '`types`.`type_id`', $typesIds])
            ->where(['IN', '`type`.`type_id`', $typesIds])
            ->andWhere(['IN', 'type', [Attribute::TYPE_DROPDOWN, Attribute::TYPE_SELECT_MANY, Attribute::TYPE_CHECKBOX_LIST, Attribute::TYPE_RADIO_LIST, Attribute::TYPE_COLOR]])
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


    public function getResultMaxPrice()
    {
        $res = clone $this->resultQuery;
        $result = $res->aggregatePrice('MAX')->orderBy(false)->asArray()->one();
        return (int)$result['aggregation_price'];
    }

    public function getResultMinPrice()
    {
        $res = clone $this->resultQuery;
        $result = $res->aggregatePrice('MIN')->orderBy(false)->asArray()->one();
        return (int)$result['aggregation_price'];
    }

    /**
     * @return string min price
     */
    public function getMinPrice()
    {
        $res = clone $this->query;
        $result = $res->aggregatePrice('MIN')->orderBy(false)->asArray()->one();
        if ($result) {
            return (int)$result['aggregation_price'];
        } else {
            return 0;
        }
    }

    /**
     * @return string max price
     */
    public function getMaxPrice()
    {
        $res = clone $this->query;
        $result = $res->aggregatePrice('MAX')->orderBy(false)->asArray()->one();
        if ($result) {
            return (int)$result['aggregation_price'];
        } else {
            return 0;
        }
    }


    /**
     * @return mixed
     */
    public function getCurrentMinPrice()
    {
        if ($this->_currentMinPrice !== null)
            return $this->_currentMinPrice;

        $this->_currentMinPrice = (isset($this->prices[0])) ? trim($this->prices[0]) : Yii::$app->currency->convert($this->price_min);

        return (int)$this->_currentMinPrice;
    }

    /**
     * @return string
     */
    public function getCurrentMaxPrice()
    {
        if ($this->_currentMaxPrice !== null)
            return $this->_currentMaxPrice;

        $this->_currentMaxPrice = (isset($this->prices[1])) ? trim($this->prices[1]) : Yii::$app->currency->convert($this->price_max);

        return (int)$this->_currentMaxPrice;
    }

    //быстрее работает.
    public function getCategoryBrands()
    {
        $this->query->orderBy = false;
        $queryClone = clone $this->query;
        $queryClone->addSelect(['brand_id', Product::tableName() . '.id']);
        $queryClone->joinWith([
            'brand' => function (\yii\db\ActiveQuery $query) {
                $query->andWhere([Brand::tableName() . '.switch' => 1]);
            },
        ]);

        $sub_query = clone $this->query;
        $sub_query->andwhere('`brand_id`=' . Brand::tableName() . '.`id`');
        $sub_query->select(['count(*)']);

        $queryClone->andWhere('brand_id IS NOT NULL');
        $queryClone->groupBy('brand_id');
        $queryClone->addSelect([
            'counter' => $sub_query,
            Brand::tableName() . '.`name_' . Yii::$app->language . '` as name',
            Brand::tableName() . '.slug',
            Brand::tableName() . '.image'
        ]);
        $queryClone->cache($this->cacheDuration);

        $brands = $queryClone->createCommand()->queryAll();

        $data = [
            'title' => Yii::t('shop/default', 'FILTER_BY_BRAND'),
            'selectMany' => true,
            'filters' => []
        ];

        foreach ($brands as $m) {
            $data['filters'][] = [
                'title' => $m['name'],
                'count' => (int)$m['counter'],
                'count_text' => (int)$m['counter'],
                'key' => 'brand',
                'queryParam' => (int)$m['brand_id'],
                'slug' => $m['slug'],
                'image' => $m['image'],
            ];
            sort($data['filters']);
        }


        return $data;
    }

    //future functions IN DEV
    public function getCategoryBrandsCallback()
    {
        $this->query->orderBy = false;
        $queryClone = clone $this->query;
        $queryClone->addSelect(['brand_id', Product::tableName() . '.id']);
        $queryClone->joinWith([
            'brand' => function (\yii\db\ActiveQuery $query) {
                $query->andWhere([Brand::tableName() . '.switch' => 1]);
            },
        ]);

        $sub_query = clone $this->query;
        $sub_query->andWhere('`brand_id`=' . Brand::tableName() . '.`id`');
        $sub_query->select(['count(*)']);
        $newData = [];
        foreach ($this->activeAttributes as $key => $p) {
            $newData[$key] = $p;
        }

        $sub_query->getFindByEavAttributes2($newData);
        $sliders = Yii::$app->request->post('slide');
        if ($sliders) {
            if (isset($sliders['price'])) {
                $sub_query->applyRangePrices($sliders['price'][0], $sliders['price'][1]);
            }
        }

        $queryClone->andWhere('brand_id IS NOT NULL');
        $queryClone->groupBy('brand_id');
        $queryClone->addSelect(['counter' => $sub_query, Brand::tableName() . '.`name_' . Yii::$app->language . '` as name']);
        //$queryClone->cache($this->cacheDuration);

        $brands = $queryClone->createCommand()->queryAll();

        $data = [
            'title' => Yii::t('shop/default', 'FILTER_BY_BRAND'),
            'selectMany' => true,
            'filters' => []
        ];

        foreach ($brands as $m) {
            $data['filters'][] = [
                'title' => $m['name'],
                'count' => (int)$m['counter'],
                'count_text' => (int)$m['counter'],
                'key' => 'brand',
                'queryParam' => $m['brand_id'],
            ];
            sort($data['filters']);
        }

        return $data;
    }

}
