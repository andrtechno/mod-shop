<?php

namespace panix\mod\shop\api\controllers;

use panix\engine\api\ApiHelpers;
use panix\engine\CMS;
use panix\mod\plugins\components\View;
use panix\mod\shop\api\models\Product;
use panix\mod\shop\components\FilterElastic;
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

class ElasticController extends ApiController
{


    private $_eavAttributes;
    public $route;


    public function actionIndex()
    {
        $route = Yii::$app->request->post('route');
        $param = Yii::$app->request->post('param');
        $slides = Yii::$app->request->post('slide');
        $sort = Yii::$app->request->post('sort');

        $elasticQuery = [];
        $urlParams = [];
        $query1 = Product::find();
        $query1->andWhere(['!=', "availability", Product::STATUS_ARCHIVE]);
        $query1->published();

        $elasticQuery['bool']['must_not'][] = ["term" => ["availability" => Product::STATUS_ARCHIVE]];
        //$query['bool']['must'][] = ["terms" => ["availability" => [Product::STATUS_PREORDER, Product::STATUS_IN_STOCK, Product::STATUS_OUT_STOCK]]];
        $elasticQuery['bool']['must'][] = ["term" => ["switch" => 1]];

        if ($route == 'shop/catalog/view') {
            $elasticQuery['bool']['must'][] = ["term" => ["categories" => $param]];
            $query1->applyCategories([$param]);
        } elseif ($route == 'shop/search/index') {
            $elasticQuery['bool']['must'][] = ["simple_query_string" => [
                //"query" => '('.$param.'* | *'.$param.'~)',
                "query" => "({$param}* | *{$param}~)",
                "fields" => ["name_uk"],
                //"flags"=> "OR|AND|PREFIX"
                //'auto_generate_synonyms_phrase_query' => false,
                //"default_operator" => "OR"
            ]];
            $query1->applySearch($param);
        } elseif ($route == 'shop/brand/view') {
            $elasticQuery['bool']['must'][] = ["term" => ["brand_id" => $param]];
            $query1->applyBrands($param);
        } elseif ($route == 'shop/catalog/new') {
            $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
            $config = Yii::$app->settings->get('shop');
            $elasticQuery['bool']['must'][] = [
                'range' => [
                    'created_at' => ['gte' => ($date_utc->getTimestamp() - (86400 * $config->label_expire_new))]
                ]
            ];
        }



        $filter = new FilterElastic($query1, [
            'route' => $this->route,
            'elasticQuery' => $elasticQuery

        ]);
        $eav = $filter->getEavAttributes();
        $active = $filter->getActiveAttributes();


        $qw = $filter->getElasticQuery(1);
        $startResult = $qw->search(null, ['size' => 0]);
        $data = [];
        $result = [];
        foreach ($startResult['aggregations']['options']['buckets'] as $i) {
            $result[$i['key']] = $i['doc_count'];
        }
        if ($route != 'shop/brand/view') {
            $brands = $filter->getCategoryBrands();
            $result2 = [];
            foreach ($startResult['aggregations']['brands']['buckets'] as $i2) {
                $result2[$i2['key']] = $i2['doc_count'];
            }

            foreach ($brands as $brand) {
                if (isset($result2[$brand['brand_id']])) {
                    if ($result2[$brand['brand_id']]) {
                        $data['brand'][$brand['brand_id']] = $brand['counter'];
                    }
                }
            }
        }
        foreach ($eav as $attribute) {
            foreach ($attribute->getOptions()->all() as $option) {
                if (isset($result[$option->id])) {
                    if ($result[$option->id]) {
                        $data[$attribute->name][$option->id] = $result[$option->id];
                    }
                }

            }
        }

        $result = $filter->getAttributesCallback($data);

        if ($route == 'shop/catalog/view') {
            $category = Category::findOne($param);
            $urlParams['slug'] = $category->full_path;
        } elseif ($route == 'shop/brand/view') {
            $brand = Brand::findOne($param);
            $urlParams['slug'] = $brand->slug;
        } elseif ($route == 'shop/search/index') {
            $urlParams['q'] = $param;
        }

        foreach ($active as $key => $p) {
            $urlParams[$key] = implode(',', $p);
        }
        if ($slides) {
            foreach ($slides as $slide_key => $slide) {
                if (isset($slide[0], $slide[1])) {
                    $urlParams[$slide_key] = $slide[0] . '-' . $slide[1];
                }
            }
        }
        if($sort){
            $urlParams['sort'] = $sort;
        }
        $url = ApiHelpers::url(Yii::$app->urlManager->addUrlParam('/' . $route, $urlParams));

        return $this->asJson([
            'textTotal' => Yii::t('shop/default', 'FILTER_BUTTON_TEXT', $result['totalCount']),
            'totalCount' => $result['totalCount'],
            'filters' => $result['data'],
            'url' => $url
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

    public function getEavAttributes()
    {
        if (is_array($this->_eavAttributes))
            return $this->_eavAttributes;


        //$queryCategoryTypes = clone $this->query; //Product::find();
        $queryCategoryTypes = Product::find();
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
}
