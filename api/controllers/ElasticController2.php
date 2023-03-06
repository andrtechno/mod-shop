<?php

namespace panix\mod\shop\api\controllers;

use panix\engine\api\ApiHelpers;
use panix\engine\CMS;
use panix\mod\plugins\components\View;
use panix\mod\shop\api\models\Product;
use panix\mod\shop\components\FilterLite;
use panix\mod\shop\components\FilterPro;
use panix\mod\shop\components\FilterView;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\ViewProduct;
use Yii;
use yii\caching\TagDependency;
use yii\elasticsearch\Query as ElasticQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use panix\engine\api\ApiController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * {
 * "mappings": {
 * "properties": {
 * "full_text": { "type": "text" },
 * "name": { "type": "text" },
 * "slug": { "type": "text" },
 * "price": { "type": "double" },
 * "options": { "type": "keyword" },
 * "categories": { "type": "keyword" },
 * "created_at": { "type": "integer" },
 * "switch": { "type": "boolean" }
 * }
 * }
 * }
 */
class ElasticController2 extends ApiController
{

    public function behaviors()
    {
        //$b = parent::behaviors();
        $b['ajaxFilter'] = [
            'class' => 'yii\filters\AjaxFilter',
            'only' => ['index', 'show']
        ];
        return [];
    }


    public function getEavAttributes()
    {
        if (is_array($this->_eavAttributes))
            return $this->_eavAttributes;


        //$queryCategoryTypes = clone $this->query; //Product::find();
        $queryCategoryTypes = Product::find();;
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


        $this->_eavAttributes['brand'] = [
            'title' => "",
            'type' => 3,
            'filters' => []
        ];

        return $this->_eavAttributes;
    }

    private $_eavAttributes;
    public $route;

    public function getActiveAttributes()
    {
        $data = [];
        $filter = (Yii::$app->request->post('filter')) ? Yii::$app->request->post('filter') : $_GET;

        foreach (array_keys($filter) as $key) {
            if (array_key_exists($key, $this->_eavAttributes)) {
                $data[$key] = (is_array($filter[$key])) ? $filter[$key] : explode(',', $filter[$key]);
            }
        }

        return $data;
    }

    public function actionIndex()
    {
        $elasticQuery = [];
        $elasticTotalQuery = [];
        $this->getEavAttributes();

        $this->route = Yii::$app->request->post('route');
        $param = Yii::$app->request->post('param');
        $cache = Yii::$app->request->post('cache');
        $urlParams = [];
        $data = [];


        $filter = Yii::$app->request->post('filter');
        $slides = Yii::$app->request->post('slide');
        $active = $this->activeAttributes;
        $url = '';
        $first = array_key_first($active);
        if ($this->route === 'shop/catalog/view') {
            $elasticTotalQuery['bool']['must'][] = ['terms' => ['categories' => [$param]]];
            $category = Category::findOne($param);
            $urlParams['slug'] = $category->full_path;

        } elseif ($this->route === 'shop/search/index') {
            $elasticTotalQuery['bool']['filter'][] = [
                'match' => [
                    'name' => [
                        'query' => $param,
                        //'operator' => 'and'
                    ]
                ]
            ];
            $urlParams['q'] = $param;
        } elseif ($this->route === 'shop/brand/view') {
            $elasticTotalQuery['bool']['must'][] = ['term' => ['brand_id' => $param]];
            unset($active['brand']); //Remove brand filter in list
        }


        foreach ($active as $key => $p) {
            if ($p) {
                if ($key == 'brand') {
                    $elasticTotalQuery['bool']['must'][] = ['terms' => ['brand_id' => $p]];
                    //$elasticQuery['bool']['must'][] = ['terms' => ['brand_id' => $p]];
                } else {
                    $elasticTotalQuery['bool']['must'][] = ['terms' => ['options' => $p]];
                    //$elasticQuery['bool']['must'][] = ['terms' => ['options' => $p]];
                }
            }
            $urlParams[$key] = implode(',', $p);

        }

        if ($slides) {
            foreach ($slides as $slide_key => $slide) {
                if (isset($slide[0], $slide[1])) {
                    $elasticTotalQuery['bool']['filter'][] = [
                        'range' => [
                            $slide_key => ['gte' => $slide[0], 'lte' => $slide[1], 'boost' => 2.0]
                        ]
                    ];
                    $urlParams[$slide_key] = 'priceTEST';
                }
            }
        }


        $root = Yii::$app->cache->get($cache);
        if ($this->route === 'shop/brand/view') {
            unset($root['brand']); //Remove brand filter in list
        }
        foreach ($root as $attribute_key => $attribute) {

            $data[$attribute_key] = [
                'title' => $attribute['title'],
                'type' => (int)$attribute['type'],
                'filters' => []
            ];


            foreach ($attribute['filters'] as $option) {
                $totalCount = 0;
                $count = 0;

                //Started queries
                $elasticQuery = [];

                if ($this->route === 'shop/catalog/view') {
                    $elasticQuery['bool']['must'][] = ['terms' => ['categories' => [$param]]];
                } elseif ($this->route === 'shop/search/index') {
                    $elasticQuery22['bool']['must'][] = [
                        'multi_match' => [
                            'query' => $param,
                            'fields' => [
                                'name'
                                //'operator' => 'and'
                            ]
                        ]
                    ];
                    $elasticQuery['bool']['must'][] = [
                        'match' => [
                            'name' => $param,
                        ]
                    ];
                } elseif ($this->route === 'shop/brand/view') {
                    //$elasticTotalQuery['bool']['must'][] = ['term' => ['brand_id' => $param]];
                }

                foreach ($active as $key => $p) {
                    if ($key != $attribute_key) {
                        if ($p) {
                            if ($key == 'brand') {
                                $elasticQuery['bool']['must'][] = ['terms' => ['brand_id' => $p]];
                            } else {
                                $elasticQuery['bool']['must'][] = ['terms' => ['options' => $p]];
                            }
                        }
                    }
                }
                if ($attribute_key == 'brand') {
                    $elasticQuery['bool']['must'][] = ['term' => ['brand_id' => $option['id']]];
                } else {
                    $elasticQuery['bool']['must'][] = ['term' => ['options' => $option['id']]];
                }


              /*  $query2 = new ElasticQuery();
                $query2->from('product');
                $query2->query = [

                    "bool" => [

                        "must" => [
                            //['terms' => ['brand_id' => [447,387]]],
                            ['term' => ['brand_id' => 667]],
                            ['match' => [ //'match' or 'match_phrase'
                                'name' => 'Черевики 11',
                                //'operator' => 'and'
                            ]]
                        ]
                    ]
                ];
                //print_r($query2->query);die;
                echo $query2->count();
                die;*/


                $query = new ElasticQuery();
                $query->from('product');
                $query->query = $elasticQuery;
                // print_r($query->query);echo '<br><br>';
                $count = (int)$query->count();

                $countText = $count;
                if (isset($active[$attribute_key])) {
                    if ($first == $attribute_key && $count) {
                        $countText = '+' . $count;
                    }
                }

                $data[$attribute_key]['filters'][] = [
                    'title' => $option['title'],
                    'count' => 2222,//(int)$count,
                    'count_text' => $countText,
                    'id' => (int)$option['id'],
                ];
            }

            $data[$attribute_key]['filtersCount'] = count($data[$attribute_key]['filters']);

        }
//echo Yii::$app->urlManager->addUrlParam('/'.$route, ['test' => '123,123,333'], true);
        $queryTotal = new ElasticQuery();
        $queryTotal->from('product');
        $queryTotal->query = $elasticTotalQuery;
        $totalCount = $queryTotal->count();


        // print_r($this->getFilterUrl($urlParams));
        // die;
        $router = ['/shop/catalog/view', $urlParams];
        return $this->asJson([
            'textTotal' => Yii::t('shop/default', 'FILTER_BUTTON_TEXT', $totalCount),
            'totalCount' => $totalCount,
            'filters' => $data,
            //'sliders' => $sliders,
            'url' => ApiHelpers::url(Yii::$app->urlManager->addUrlParam('/' . $this->route, $urlParams))
        ]);
    }

    public function getRootCategoryAttributes()
    {
        $data = [];
        /*$brands = $this->getCategoryBrands();

        $data['brand'] = [
            'title' => $brands['title'],
            'type' => 1,
            //'key' => 0,
            'selectMany'=>true,
            'filtersCount'=>count($brands['filters']),
            'filters' => []
        ];
        foreach ($brands['filters'] as $key=>$brand) {
            // print_r($brand);die;

            $data['brand']['filters'][] = [
                'title' => $brand['title'],

                'count' => $brand['count'],
                //'key' => $brand['id'],
                'id' => $brand['id'],
            ];
        }*/
        //$active = $this->activeAttributes;
        //$first = array_key_first($active);

        $data = Yii::$app->cache->get($this->cacheKey . '-attrs');
        if ($data === false) {
            foreach ($this->_eavAttributes as $attribute) {
                $data[$attribute->name] = [
                    'title' => $attribute->title,
                    //'selectMany' => (boolean)$attribute->select_many,
                    'type' => (int)$attribute->type,
                    'key' => $attribute->name,
                    'filters' => []
                ];

                $totalCount = 0;
                foreach ($attribute->getOptions()->all() as $option) {
                    $query = new ElasticQuery();
                    $query->from('product');
                    $query->query = [

                        "bool" => [
                            "must" => [
                                /*[
                                    "term" => [
                                        "options" => 12
                                    ],
                                ],
                                [
                                    "term" => [
                                        "options" => 9
                                    ],
                                ],*/
                                /*[
                                    'range' => [
                                        'price' => [
                                            'gte' => 0,
                                            'lte' => 4100
                                        ]
                                    ],
                                ],*/
                                [
                                    "terms" => [
                                        "options" => [$option->id]
                                    ],
                                ],
                                [
                                    "term" => [
                                        "categories" => 5
                                    ],
                                ],
                            ]
                        ]
                    ];


                    /*$query->addAggregate('options', [
                        'terms' => ["field" => "options"],
                    ]);*/
                    //$query->addAggregate('options', [
                    //    'terms' => ["field" => "options"],
                    //]);
                    $count = $query->count();


                    //print_r($query);die;


                    if ($count > 0) {
                        $totalCount += $count;
                        $data[$attribute->name]['filters'][] = [
                            'title' => $option->value,
                            'count' => (int)$count,
                            'key' => $attribute->name,
                            'id' => (int)$option->id,
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

    public function getFilterUrl($params = [])
    {

        $route[0] = '/' . $this->route;
        $slides = Yii::$app->request->post('slide');


        foreach ($params as $attribute => $values) {
            if (is_array($values)) {
                array_push($route, [$attribute => implode(',', $values)]);
            }
        }

        if ($slides) {
            foreach ($slides as $key => $values) {
                $showRoute = true;

                if ($key == 'price' && $values[1] == $this->min && $values[0] == $this->max) {
                    $showRoute = false;
                }
                if ($showRoute)
                    $route[$key] = implode('-', $values);

            }
        }


        if (Yii::$app->request->post('sort')) {
            $route['sort'] = Yii::$app->request->post('sort');
        }
        if (Yii::$app->request->post('per-page')) {
            $route['per-page'] = Yii::$app->request->post('per-page');
        }
        if (Yii::$app->request->post('view')) {
            $route['view'] = Yii::$app->request->post('view');
        }


        return $route;
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
        $queryClone->cache($this->cacheDuration);

        $brands = $queryClone->createCommand()->queryAll();
        // echo $queryClone->createCommand()->rawSql;die;

        $data = [
            'title' => Yii::t('shop/default', 'FILTER_BY_BRAND'),
            //'selectMany' => true,
            'filters' => []
        ];

        foreach ($brands as $m) {
            $data['filters'][] = [
                'title' => $m['name'],
                'count' => (int)$m['counter'],
                'count_text' => (int)$m['counter'],
                'key' => 'brand',
                'id' => $m['brand_id'],
                'slug' => $m['slug'],
                'image' => $m['image'],
            ];
            sort($data['filters']);
        }


        return $data;
    }
}
