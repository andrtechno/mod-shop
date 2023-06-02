<?php

namespace panix\mod\shop\components;

use panix\engine\api\ApiHelpers;
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
use yii\elasticsearch\Query as ElasticQuery;

class FilterElastic extends Component
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
    public $elasticQuery;
    protected $_route;

    public function getActiveAttributes()
    {
        $data = [];
        $filters = (Yii::$app->request->post('filter')) ? Yii::$app->request->post('filter') : $_GET;
        if (isset($_GET['lang'])) {
            unset($_GET['lang']);
        }
        foreach (array_keys($filters) as $key) {
            if (array_key_exists($key, $this->_eavAttributes)) {
                if ((boolean)$this->_eavAttributes[$key]->select_many === true) {
                    $data[$key] = (is_array($filters[$key])) ? $filters[$key] : explode(',', $filters[$key]);
                } else {
                    if (isset($filters[$key]))
                        $data[$key] = [$filters[$key]];
                }
            }
            if (isset($filters['brand'])) {
                $data['brand'] = (is_array($filters['brand'])) ? $filters['brand'] : explode(',', $filters['brand']);
            }
        }

        return $data;
    }


    public function setResultRoute($route)
    {
        $this->_route = $route;
    }

    public $addAttributes = [];

    //public $applyAttributes = [];

    public function __construct(ProductQuery $query = null, $config = [])
    {

        parent::__construct($config);
        $data = Yii::$app->request->post('filter');
        $slides = Yii::$app->request->post('slide');

        // var_dump($this->elasticQuery);die;
        if ($query) {
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
            //print_r($this->min);die;
            if (($this->getCurrentMinPrice() != $this->min) || ($this->getCurrentMaxPrice() != $this->max)) {
                $this->resultQuery->applyRangePrices($this->getCurrentMinPrice(), $this->getCurrentMaxPrice());
            }

        } else {


            /*$this->query = Product::find();
            $this->query->andWhere(['!=', "availability", Product::STATUS_ARCHIVE]);
            $this->query->published();
            $this->query->applyCategories([5]);*/

            // $this->getEavAttributes();
        }
    }

    public function getActiveFilters()
    {
        $request = Yii::$app->request;
        // Render links to cancel applied filters like prices, brands, attributes.
        $menuItems = [];

        if (in_array(Yii::$app->controller->route, ['shop/catalog/view', 'shop/search/index'])) {
            $brands = array_filter(explode(',', $request->getQueryParam('brand')));
            $brands = Brand::getDb()->cache(function ($db) use ($brands) {
                return Brand::findAll($brands);
            }, 3600);
        }

        if ($request->getQueryParam('price')) {

            if ($this->prices) {
                $menuItems['price'] = [
                    'name' => 'price',
                    'label' => Yii::t('shop/default', 'FILTER_BY_PRICE') . ':',
                    'itemOptions' => ['id' => 'current-filter-prices']
                ];

                $menuItems['price']['items'][] = [
                    // 'name'=>'min_price',
                    'value_url' => number_format($this->prices[0], 0, '', ''),
                    'value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()) . ' - ' . Yii::$app->currency->number_format($this->getCurrentMaxPrice()),
                    'label' => Html::decode(Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()) . ' до ' . Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active['symbol']])),
                    'options' => ['class' => 'remove', 'data' => [
                        'type' => 'slider',
                        'slider-max' => round($this->prices[1]),
                        'slider-min' => round($this->prices[0]),
                        'slider-current-max' => $this->getCurrentMaxPrice(),
                        'slider-current-min' => $this->getCurrentMinPrice(),
                    ]],
                    'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'price', $this->getCurrentMinPrice() . '-' . $this->getCurrentMaxPrice())
                ];
            }
        }


        if (in_array(Yii::$app->controller->route, ['shop/catalog/view', 'shop/search/index'])) {
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

                            if (Yii::$app->language == 'ru') {
                                $value = "value";
                            } else {
                                $value = "value_" . Yii::$app->language;
                            }

                            $menuItems[$attributeName]['items'][] = [
                                'value' => $option->id,
                                'label' => $option->$value . (($attribute->abbreviation) ? ' ' . $attribute->abbreviation : ''),
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


    public function getAttributes()
    {

        $active = $this->getActiveAttributes();
        $urlParams = [];
        //$this->route = 'shop/catalog/view';
        if ($this->route === 'shop/catalog/view') {
            $category = Category::findOne(['full_path' => Yii::$app->request->get('slug')]);
            $urlParams['slug'] = $category->full_path;
        } elseif ($this->route === 'shop/brand/view') {
            //$brand = Brand::findOne(['slug' => Yii::$app->request->get('slug')]);
            //$urlParams['slug'] = $brand->slug;
        } elseif ($this->route === 'shop/search/index') {

        }

        $first = array_key_first($active);
        foreach ($active as $key => $p) {
            $urlParams[$key] = implode(',', $p);

            /*if ($key == 'brand') {
                $this->elasticQuery['bool']['must'][] = ["terms" => ["brand_id" => $p]];
            } else {
                if ($first != $key) {
                    $this->elasticQuery['bool']['must'][] = ["terms" => ["options" => $p]];
                }
            }*/

        }


        $elasticQuery = $this->getElasticQuery();
        // CMS::dump($this->elasticQuery);die;
        $search = $elasticQuery->search();
        //CMS::dump($search);die;
        $opts = [];
        foreach ($search['aggregations']['options']['buckets'] as $opt) {
            $opts[$opt['key']] = $opt['doc_count'];
        }


        $this->getEavAttributes();

        if ($this->route != 'shop/brand/view') {
            $brands = $this->getCategoryBrands();
            if ($brands) {
                $data['data']['brand'] = [
                    'title' => Yii::t('shop/default', 'FILTER_BY_BRAND'),
                    'key' => 'brand',
                    'type' => 3,
                    'filters' => []
                ];

                foreach ($brands as $m) {
                    $data['data']['brand']['filters'][] = [
                        'title' => $m['name'],
                        'count' => (int)$m['counter'],
                        'count_text' => (int)$m['counter'],
                        'id' => $m['brand_id'],
                        //'key' => 'brand',
                        'slug' => $m['slug'],
                        'image' => $m['image'],
                    ];
                    sort($data['data']['brand']['filters']);
                }
                $data['data']['brand']['filtersCount'] = count($data['data']['brand']['filters']);
            }
        }

        foreach ($this->_eavAttributes as $attribute) {
            $data['data'][$attribute->id] = [
                'title' => $attribute->title,
                'type' => (int)$attribute->type,
                'key' => $attribute->name,
                'filters' => []
            ];

            $filtersCount = 0;
            foreach ($attribute->getOptions()->all() as $option) {
                $show = false;
                $count = 0;
                if (isset($opts[$option->id])) {
                    $count = $opts[$option->id];
                    $show = true;
                }

                $countText = $count;

                if (isset($active[$attribute->name])) {
                    if ((array_key_first($active) == $attribute->name) && $count) {
                        $first = $attribute->name;
                    }
                }

                if (Yii::$app->language == 'ru') {
                    $value = "value";
                } else {
                    $value = "value_" . Yii::$app->language;
                }

                $data['data'][$attribute->id]['filters'][] = [
                    'title' => $option->$value,
                    'count_text' => $countText,
                    'count' => (int)$count,

                    'data' => ($option->data) ? Json::decode($option->data) : [],
                    // 'key' => $attribute->name, //neeed delete
                    'id' => (int)$option->id,
                ];
                // }
            }
            $data['data'][$attribute->id]['filtersCount'] = count($data['data'][$attribute->id]['filters']);
            if ($attribute->sort == SORT_ASC) {
                sort($data['data'][$attribute->id]['filters']);
            } elseif ($attribute->sort == SORT_DESC) {
                rsort($data['data'][$attribute->id]['filters']);
            }
        }
        $data['totalCount'] = $elasticQuery->count();
        //var_dump($this->route);die;
        $data['url'] = 'asd';//ApiHelpers::url(Yii::$app->urlManager->addUrlParam('/' . $this->route, $urlParams));
        return $data;
    }

    public function getAttributesCallback($test = [])
    {

        $this->getEavAttributes();
        $actives = $this->getActiveAttributes();

        $elasticQuery = $this->getElasticQueryCallback($test);
        $search = $elasticQuery->search(null, ['size' => 0]);

        $opts = [];
        if (isset($search['aggregations']['filtered']['buckets'])) {
            foreach ($search['aggregations']['filtered']['buckets'] as $keys => $item) {
                list($key, $id) = explode(':', $keys);
                $opts[$key][$id] = $item['doc_count'];
            }
        }

        $data['data']['brand'] = [
            'title' => 'brand',
            'type' => (int)3,
            'key' => 'brand',
            'filters' => []
        ];

        foreach ($this->getCategoryBrands() as $brand) {
            $count = 0;
            if (isset($opts['brand'][$brand['brand_id']])) {
                $count = $opts['brand'][$brand['brand_id']];

            }
            $countText = $count;
            $data['data']['brand']['filters'][] = [
                //'title' => $option->value,
                'count_text' => $countText,
                'count' => (int)$count,
                //'key' => 'brand',
                'id' => (int)$brand['brand_id'],
            ];

        }


        foreach ($this->_eavAttributes as $attribute) {
            $data['data'][$attribute->name] = [
                'title' => $attribute->title,
                'type' => (int)$attribute->type,
                'key' => $attribute->name,
                'filters' => []
            ];
            foreach ($attribute->getOptions()->all() as $option) {
                $count = 0;
                if (isset($opts[$attribute->name][$option->id])) {
                    $count = $opts[$attribute->name][$option->id];

                }
                $countText = $count;
                $value = "value_" . Yii::$app->language;
                $data['data'][$attribute->name]['filters'][] = [
                    'title' => $option->$value,
                    'count_text' => $countText,
                    'count' => (int)$count,
                    //'key' => $attribute->name,
                    'lang' => Yii::$app->language,
                    'id' => (int)$option->id,
                ];
            }
            $data['data'][$attribute->name]['filtersCount'] = count($data['data'][$attribute->name]['filters']);
        }

        foreach ($actives as $active_key => $active) {
            if ($active_key == 'brand') {
                $this->elasticQuery['bool']['must'][] = ["terms" => ["brand_id" => $active]];
            } else {
                $this->elasticQuery['bool']['must'][] = ["terms" => ["options" => $active]];
            }
        }

        $slides = Yii::$app->request->post('slide');
        if ($slides) {
            foreach ($slides as $slide_key => $slide) {
                if (isset($slide[0], $slide[1])) {
                    $this->elasticQuery['bool']['must'][] = [
                        'range' => [
                            'price' => ['gte' => (double)$slide[0], 'lte' => (double)$slide[1]]
                        ]
                    ];
                }
            }
        }


        $queryTotal = new ElasticQuery();
        $queryTotal->from('product');
        $queryTotal->query = $this->elasticQuery;
        $data['totalCount'] = $queryTotal->count();

        return $data;
    }

    public function getElasticQuery($min = 1)
    {
        $query = new ElasticQuery();
        $query->from('product');

        $query->source('*');
        /*$query->runtimeMappings = [
            'price2' => [
                "type" => "double",
                "script" => [
                    "lang" => "painless",
                    "source" => "emit(doc['price'].value);",
                    "params" => [
                        "multiplier" => 2
                    ]
                ]
            ]
        ];*/


        //$query->fields = ['price', 'name', 'switch'];
        // ID=>RATE
        $currencies = [0 => 10, 1 => 20, 2 => 20];

        //see documentaton for add script to DB and show by ID !!! https://www.youtube.com/watch?v=a3OgrYj3ja0&ab_channel=Codetuber
        /*$query->scriptFields = [
            "price_with_currency" => [
                "script" => [
                    'lang'=>'painless',
                    //"source" => "doc['price'].value * params.get('multiplier')['2'];",
                    "source" => "doc['price'].value * params.get('multiplier')[params._source.switch];",
                    "params" => [
                        "multiplier" => $currencies
                    ]
                ]
            ]
        ];*/

        $query->query = $this->elasticQuery;
        $query->addAggregate('min_price', [
            'min' => ["field" => "price"],
        ]);
        $query->addAggregate('max_price', [
            'max' => ["field" => "price"],
        ]);

        $query->addAggregate('options', [
            'terms' => [
                "field" => "options",
                'order' => [
                    '_key' => 'desc',
                    //'_count' => 'desc'
                ],
                "min_doc_count" => $min,
                'size' => 9999
            ],
        ]);
        $query->addAggregate('brands', [
            'terms' => [
                "field" => "brand_id",
                "min_doc_count" => $min,
                'size' => 9999
            ],

        ]);

        /*$query->addSuggester('my_suggest', [
            'text' => 'крос',
            'term' => [
                'field' => 'name',
            ]
        ]);*/

        //CMS::dump($query->search());
        //die;
        return $query;
    }


    public function getElasticQueryCallback(array $start = [])
    {

        $active = $this->getActiveAttributes();

        $query = new ElasticQuery();
        $query->from('product');
        //$query->fields = ['*'];
//CMS::dump($this->elasticQuery);die;
        $query->query = $this->elasticQuery;
        /*$query->addAggregate('min_price', [
            'min' => ["field" => "price"],
        ]);
        $query->addAggregate('max_price', [
            'max' => ["field" => "price"],
        ]);*/

        $filtered = [];


        $slides = Yii::$app->request->post('slide');
        $filter = Yii::$app->request->post('filter');

        //Отделяем бренд от общих рахактеристик
        $brands = [];
        if (isset($start['brand'])) {
            $brands = $start['brand'];
        }
        $attributes = $start;
        unset($attributes['brand']);
        //---------------
        foreach ($brands as $brand_id => $brand_ids) {
            $filtered['filters']['filters']['brand:' . $brand_id]['bool']['filter'][] = ["term" => ['brand_id' => $brand_id]];
            if ($slides) {
                foreach ($slides as $slide_key => $slide) {
                    if (isset($slide[0], $slide[1])) {
                        $filtered['filters']['filters']['brand:' . $brand_id]['bool']['filter'][] = [
                            'range' => [
                                'price' => [
                                    'gte' => (double)$slide[0],
                                    'lte' => (double)$slide[1],
                                ]
                            ]
                        ];
                    }
                }
                /*$filtered['filters']['filters']['brand:' . $brand_id]['bool']['filter'][] = [
                    'script' => [
                        'script' => [
                            "source"=> "doc['price'].value * 40.80",
                            "lang"=> "painless",
                        ]
                    ]
                ];*/
            }

            foreach ($active as $option_key2 => $options2) {
                if ($option_key2 != 'brand') {
                    $filtered['filters']['filters']['brand:' . $brand_id]['bool']['filter'][] = ["terms" => ['options' => $options2]];
                } else {
                    //$filtered['filters']['filters']['brand:' . $brand_id]['bool']['filter'][] = ["terms" => ['brand_id' => $options2]];
                }
            }
        }

        foreach ($attributes as $attribute_key => $items) {
            foreach ($items as $key => $count) {
                $filtered['filters']['filters'][$attribute_key . ':' . $key]['bool']['filter'][] = ["term" => ['options' => $key]];
                if ($slides) {
                    foreach ($slides as $slide_key => $slide) {
                        if (isset($slide[0], $slide[1])) {

                            $filtered['filters']['filters'][$attribute_key . ':' . $key]['bool']['filter'][] = [
                                'range' => [
                                    'price' => [
                                        'gte' => (double)$slide[0],
                                        'lte' => (double)$slide[1],
                                    ]
                                ]
                            ];
                        }
                    }
                }
                foreach ($active as $option_key => $options) {
                    if ($attribute_key != $option_key && $option_key != 'brand') {
                        $filtered['filters']['filters'][$attribute_key . ':' . $key]['bool']['filter'][] = ["terms" => ['options' => $options]];
                    }
                    if ($option_key == 'brand') {
                        $filtered['filters']['filters'][$attribute_key . ':' . $key]['bool']['filter'][] = ["terms" => ['brand_id' => $options]];
                    }
                }
            }
        }

        /*$query->scriptFields = [
            'price' => [
                'script' => [
                    "lang" => "painless",
                    'source' => "if(doc['currency_id'].value) { doc['price'].value * params.currency } else { doc['price'].value }",
                    //'source' => "doc['price'].value * params.currency",
                    "params" => [
                        "currency" => 40
                    ]
                ],
            ],
        ];*/

        /*$query->runtimeMappings = [
            'price_calc' => [
                'type' => 'double',
                "script" => [
                    //"source"=> "if (true) { emit(params._source[\'price\'] * 33) } else { emit(params._source[\'price\']) }"
                    //"source"=> 'emit(params._source[\'price\'] * '.$currency_value.')'
                    "source" => "emit(doc['currency_id'].empty ? doc['price'].value * params.currency : doc['price'].value)",
                    "params" => [
                        "currency" => 40.80
                    ]
                ],
                //'script' => 'emit(params._source[\'price\'] * '.$currency_value.')'
            ],
        ];*/
//print_r($filtered);die;
        if ($filtered) {
            $query->addAggregate('filtered', $filtered);
        } else {
            //echo 'error no aggrigation';die;
        }

        //$query->addOrderBy(['created_at'=>SORT_ASC]);
        //$search = $query->search(null, ['size' => 10,'from'=>1]);
        $search = $query->search(null, ['size' => 0]);
//print_r($search);die;
        return $query;
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

        //$attributeId
        $query = Attribute::find();
        //->where(['IN', '`types`.`type_id`', $typesIds])\
        $query->where(['type' => [Attribute::TYPE_DROPDOWN, Attribute::TYPE_SELECT_MANY, Attribute::TYPE_CHECKBOX_LIST, Attribute::TYPE_RADIO_LIST, Attribute::TYPE_COLOR]]);

        if ($typesIds) {
            $query->andWhere(['type.type_id' => $typesIds]);
        }
        if ($this->addAttributes) {
            $query->andWhere([Attribute::tableName() . '.id' => $this->addAttributes]);
        }
        $query->distinct(true);
        $query->useInFilter();
        $query->sort();
        $query->orderBy(false);

        $query->joinWith(['types type', 'options']);

        // echo $query->createCommand()->rawSql;
        // die;
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
            Brand::tableName() . '.slug as slug',
            Brand::tableName() . '.image as image'
        ]);
        //$queryClone->cache($this->cacheDuration);

        $brands = $queryClone->createCommand()->queryAll();
        // echo $queryClone->createCommand()->rawSql;die;


        return $brands;
    }

}
