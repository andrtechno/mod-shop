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
class ElasticController extends ApiController
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
        return $this->_eavAttributes;
    }

    private $_eavAttributes;

    public function getActiveAttributes()
    {
        $data = [];
        $sss = (Yii::$app->request->post('filter')) ? Yii::$app->request->post('filter') : $_GET;

        foreach (array_keys($sss) as $key) {
            if (array_key_exists($key, $this->_eavAttributes)) {
                if ((boolean)$this->_eavAttributes[$key]->select_many === true) {
                    $data[$key] = (is_array($sss[$key])) ? $sss[$key] : explode(',', $sss[$key]);
                } else {
                    if (isset($sss[$key]))
                        $data[$key] = [$sss[$key]];
                }
            }
        }

        return $data;
    }

    public function actionIndex()
    {
        $this->getEavAttributes();
        $route = Yii::$app->request->post('route');
        $param = Yii::$app->request->post('param');
        $accessAttributes = Yii::$app->request->post('attributes');


        $data = [];


        $filter = Yii::$app->request->post('filter');
        $slides = Yii::$app->request->post('slide');
        $active = $this->activeAttributes;
        $first = array_key_first($active);
        $mustTotal = [];
        $totalTerms = [];
        $mustTotal[] = ['term' => ['categories' => 5]];
        foreach ($active as $key => $p) {
            $test = [];
            foreach ($p as $a) {
                $test[] = $a;
                //$mustTotal[] = ['term' => ['options' =>$a]];

            }
            if ($test) {
                $mustTotal[] = ['terms' => ['options' => $test]];
            }
        }
        //if ($test) {
        //$mustTotal[] = ['terms' => ['options' =>$test]];
        //}
        foreach ($slides as $slide_key => $slide) {
            print_r($slide);
            if (isset($slide[0], $slide[1])) {
                $must[] = [
                    'range' => [
                        'price' => [
                            'gte' => 1,
                            'lte' => 2,
                            'boost' => 2.0
                        ]
                    ]
                ];

            }
        }
        die;
        $root = $this->getRootCategoryAttributes();
        foreach ($root as $attribute) {

            $data[$attribute['key']] = [
                'title' => $attribute['title'],
                'selectMany' => (boolean)$attribute['selectMany'],
                'type' => (int)$attribute['type'],
                'key' => $attribute['key'],
                'filters' => []
            ];


            foreach ($attribute['filters'] as $option) {
                $totalCount = 0;
                $count = 0;
                $must = [];
                $must[] = ['term' => ['categories' => 5]];

                //$newData = [];
                foreach ($active as $key => $p) {
                    $newData = [];
                    if ($key != $attribute['key']) {
                        foreach ($p as $a) {
                            $newData[] = $a;
                            //$must[]=['term'=>['options'=>$a]];
                        }

                    }
                    if ($newData) {
                        //if (count($newData) > 0) {
                        $must[] = ['terms' => ['options' => $newData]];
                        //} else {
                        //    $must[] = ['term' => ['options' => $newData]];
                        //}
                    }
                }


                $newData[] = $option['id'];
                $must[] = ['term' => ['options' => $option['id']]];


                /*$query2 = new ElasticQuery();
                $query2->from('product');
                $query2->query = [
                    "bool" => [
                        "must" => [
                            //'match_all' => [],
                            ['terms' => ['options' => [40, 175]]],
                            ['term' => ['categories' => 5]],
                            //['term' => ['options' => 212]],
                            ['term' => ['options' => 12]]
                        ]
                    ]
                ];
                //print_r($query2->query);die;
                echo $query2->count();
                die;*/


                $query = new ElasticQuery();
                $query->from('product');
                $query->query = [
                    //'match_all' => [],
                    "bool" => [
                        "must" => $must
                    ]
                ];
                $count = (int)$query->count();


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
                    'key' => $attribute['key'],
                    'id' => (int)$option['id'],
                ];
            }


            //$data[$attribute['key']]['totalCount'] = $totalCount;
            $data[$attribute['key']]['filtersCount'] = count($data[$attribute['key']]['filters']);

        }


        $queryTotal = new ElasticQuery();
        $queryTotal->from('product');
        $queryTotal->query = [
            "bool" => [
                "must" => $mustTotal
            ]
        ];

        $totalCount = $queryTotal->count();
        return $this->asJson([
            'textTotal' => Yii::t('shop/default', 'FILTER_BUTTON_TEXT', $totalCount),
            'totalCount' => $totalCount,
            'filters' => $data,
            //'sliders' => $sliders,
            //'url' => ApiHelpers::url($filter->getResultRoute())
        ]);
    }

    public function getRootCategoryAttributes()
    {
        $data = [];

        //$active = $this->activeAttributes;
        //$first = array_key_first($active);

        //$data = Yii::$app->cache->get($this->cacheKey . '-attrs');
        //if ($data === false) {
        foreach ($this->_eavAttributes as $attribute) {
            $data[$attribute->name] = [
                'title' => $attribute->title,
                'selectMany' => (boolean)$attribute->select_many,
                'type' => (int)$attribute->type,
                'key' => $attribute->name,
                'filters' => []
            ];

            $totalCount = 0;
            foreach ($attribute->getOptions()->cache(0, new TagDependency(['tags' => 'attribute-' . $attribute->name]))->all() as $option) {
                // $count = $this->countRootAttributeProducts($attribute, $option);


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
                $query->addAggregate('options', [
                    'terms' => ["field" => "options"],
                ]);
                $count = $query->count();


                //print_r($query);die;


                if ($count > 0) {
                    $totalCount += $count;
                    $data[$attribute->name]['filters'][] = [
                        'title' => $option->value,
                        'count' => (int)$count,
                        //'count_text' => $count,
                        //'data' => ($option->data) ? Json::decode($option->data) : [],
                        //'abbreviation' => ($attribute->abbreviation) ? $attribute->abbreviation : null,
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

        //Yii::$app->cache->set($this->cacheKey . '-attrs', $data, 3600 * 24 * 7);
        //}
        return $data;
    }

}
