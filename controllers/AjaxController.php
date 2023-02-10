<?php

namespace panix\mod\shop\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Category;

class AjaxController extends Controller
{
    /**
     * Set store currency
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionCurrency($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->currency->setActive($id);
        } else {
            return $this->goHome();
        }
    }
    public function actionFilter2()
    {
        return $this->asJson(['ss'=>1]);
    }

    public function actionFilter()
    {

        $productModel = Yii::$app->getModule('shop')->model('Product');
        $query = $productModel::find()->published();
        $route = Yii::$app->request->post('route');
        $param = Yii::$app->request->post('param');
        $accessAttributes = Yii::$app->request->post('attributes');
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
        $filterClass = Yii::$app->getModule('shop')->filterClass;
        $filter = new $filterClass($query, ['route' => $url]);

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

        $attributes = [];
        $brands = [];
        if ($filterClass !== 'panix\mod\shop\components\FilterLite') {

            if (!in_array($route, ['shop/brand/view'])) {
                $brands = $filter->getCategoryBrandsCallback();
            }
            $attributes = $filter->getCategoryAttributesCallback();
        }

        $total = $filter->resultQuery->count();


        $sliders = [];
        $sliders2 = Yii::$app->request->post('slide');
        if ($sliders2) {
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
            'textTotal' => "Показать " . Yii::t('shop/default', 'PRODUCTS_COUNTER', $total),
            'totalCount' => (int)$total,
            'filters' => $results,
            'sliders' => $sliders,
            'url' => Url::to($route)
        ]);
    }
}
