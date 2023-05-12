<?php

namespace panix\mod\shop\api\controllers;

use panix\engine\api\ApiHelpers;
use panix\engine\CMS;
use panix\mod\plugins\components\View;
use panix\mod\shop\api\models\Product;
use panix\mod\shop\components\FilterLite;
use panix\mod\shop\components\FilterPro;
use panix\mod\shop\components\FilterView;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\ViewProduct;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use panix\engine\api\ApiController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FilterController extends ApiController
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

    public function actionMain()
    {

        return $this->asJson(['sad' => 1]);
    }


    public function actionIndex()
    {
        $route = Yii::$app->request->post('route');
        $param = Yii::$app->request->post('param');
        $ea = Yii::$app->request->post('ea');
        $eb = Yii::$app->request->post('eb');
        $accessAttributes = Yii::$app->request->post('attributes');
        $sort = Yii::$app->request->post('sort');

        //if (!Yii::$app->request->isAjax) {
        //    throw new ForbiddenHttpException('Acesss denied.');
        //}
        //if (!$route) {
        //    throw new ForbiddenHttpException('required POST route.');
        //}

        $productModel = Product::find();
        $query = $productModel->published();

        /*$requestParams = Yii::$app->getRequest()->getBodyParams();
        $params = Yii::$app->getRequest()->bodyParams;
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }*/


        $url = [];
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
        /*if ($param && in_array($route, ['shop/catalog/new', 'shop/catalog/sales', 'shop/catalog/view'])) {
            $category = Category::findOne($param);
            if (!$category)
                $this->error404();
            $query->applyCategories($param, 'andWhere', $category->children()->count());
            $url = $category->getUrl();
        }*/
        if ($param && in_array($route, ['shop/catalog/new', 'shop/catalog/sales', 'shop/catalog/view'])) {
            $category = Category::findOne($param);
            $query->applyCategories($param);
            $url = $category->getUrl();
        }
        $filterPost = Yii::$app->request->post('filter');

        $filter = new FilterView($query, [
            'route' => $url,
            'cacheKey' => Yii::$app->request->post('cache')
        ]);

        $filter->accessAttributes = $accessAttributes;

        $attributes = [];
        $brands = [];
        //FOR PRO FILTER!!!!111
        $attributes = $filter->getCategoryAttributesCallback();
        if (!in_array($route, ['shop/brand/view'])) {
            $brands = $filter->getCategoryBrandsCallback();
        }


        $total = $filter->resultQuery->count();


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

        return $this->asJson([
            'textTotal' => Yii::t('shop/default', 'FILTER_BUTTON_TEXT', $total),
            'totalCount' => (int)$total,
            'filters' => $results,
            'sliders' => $sliders,
            'url' => ApiHelpers::url($filter->getResultRoute())
        ]);
    }

    public function actionShow()
    {
        $productModel = Product::find();
        $query = $productModel->published();

        $category = Category::findOne(5);
        $query->applyCategories($category, 'andWhere', $category->children()->count());
        $url = $category->getUrl();

        $filter = new FilterLite($query, [
            'route' => [$url],
            'cacheKey' => CMS::gen(5) //'sss1'
        ]);

        return $this->asJson([
            'slides' => [
                'price' => [
                    'title' => 'Цена',
                    'min' => 1,
                    'max' => 100,
                ]
            ],
            'brands' => $filter->getCategoryBrands(),
            'attributes' => $filter->getRootCategoryAttributes(),
        ]);
    }
}
