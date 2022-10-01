<?php

namespace panix\mod\shop\api\v1\controllers;

use panix\mod\shop\api\v1\models\Product;
use panix\mod\shop\api\v1\Serializer;
use panix\mod\shop\components\Filter;
use panix\mod\shop\components\FilterV2;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Category;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\Controller;
use yii\web\Response;

class FilterController extends Controller
{

   // public $modelClass = 'panix\mod\shop\api\v1\models\Product';
    public $serializer2 = [
        'class' => Serializer::class,
    ];
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formatParam' => 'format',
                'formats' => [
                    'json' => Response::FORMAT_JSON,
                ]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
            'authenticator' => [
                'class' => QueryParamAuth::class,
                'tokenParam' => 'token',
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ],
            ]
        ];
    }
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        parent::beforeAction($action);
    }

    public function actionIndex()
    {

        //$query = Product::find()->published();
        //$route = Yii::$app->request->post('route');
        //$param = Yii::$app->request->post('param');
        //$accessAttributes = Yii::$app->request->post('attributes');

        return $this->asJson(['ok' => true]);
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        $params = Yii::$app->getRequest()->bodyParams;
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        return $this->asJson(Yii::$app->getRequest()->getQueryParams());

        if ($route == 'shop/catalog/sales') {
            $query->sales();
            $url = [$route];

        } elseif ($route == 'shop/catalog/new') {
            $query->new();
            $url = ['/' . $route];

        } elseif ($route == 'shop/search/index') {
            $query->applySearch($param);
            $url = ['/' . $route, 'q' => $param];
        } elseif ($route == 'shop/brand/view') {
            $brand = Brand::findOne($param);

            $query->applyBrands($brand->id);
            $url = $brand->getUrl();
        }
        $category = null;
        if ($param && in_array($route, ['shop/catalog/new', 'shop/catalog/sales', 'shop/catalog/view'])) {
            $category = Category::findOne($param);
            // die('s');
            if (!$category)
                $this->error404();
            $query->applyCategories($category, 'andWhere', $category->children()->count());
            $url = $category->getUrl();
        }
        $filterPost = Yii::$app->request->post('filter');
        if ($filterPost) {
            // if (isset($filterPost['brand'])) {
            //    $query->applyBrands($filterPost['brand']);
            //}
            //unset(Yii::$app->request->post('filter')['brand']);
            // $query->getFindByEavAttributes3($filterPost);
        }
//echo $query->createCommand()->rawSql;die;

        $filter = new FilterV2($query, ['route' => $url]);
        //  echo $filter->activeUrl;

        //$filter->route = $url;
        $filter->accessAttributes = $accessAttributes;


        // $filter->resultQuery->applyAttributes($filter->activeAttributes);
        //if (Yii::$app->request->get('brand')) {
        //     $brands = explode(',', Yii::$app->request->get('brand', ''));
        //     $filter->resultQuery->applyBrands($brands);
        // }

        //  print_r($filter->resultQuery);die;
        //print_r($f->getPostActiveAttributes());die;
        $attributes = $filter->getCategoryAttributesCallback();
        $brands = [];
        if (!in_array($route, ['shop/brand/view'])) {
            $brands = $filter->getCategoryBrandsCallback();
        }

//print_r($filter->getResultUrl());die;
        $total = $filter->resultQuery->count();
//echo $filter->resultQuery->createCommand()->rawSql;die;

        $sliders = [];
        $sliders2 = Yii::$app->request->post('slide');
        if ($sliders2) {
            if (isset($sliders2['price'])) {

            }
            $sliders = [
                'price' => [
                    'min' => floor($sliders2['price'][0]),
                    'max' => ceil($sliders2['price'][1]),
                    'default' => [
                        'min' => $filter->min,
                        'max' => $filter->max
                    ],

                ]
            ];
        }


        $results = ArrayHelper::merge($attributes, ['brand' => $brands]);

        $route = $filter->getResultRoute();
//print_r($route);die;
        //   $total = $filter->count();
        return $this->asJson([
            'textTotal' => "Показать " . Yii::t('shop/default', 'PRODUCTS_COUNTER', $total),
            'totalCount' => (int)$total,
            'filters' => $results,
            'sliders' => $sliders,
            'url' => Url::to($filter->getResultRoute())
        ]);
    }
}
