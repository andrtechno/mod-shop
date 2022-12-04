<?php

namespace panix\mod\shop\controllers;

use panix\engine\controllers\WebController;
use Yii;
use yii\helpers\Url;
use yii\web\Response;
use panix\mod\shop\components\FilterController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Category;
use panix\engine\CMS;
use panix\engine\data\ActiveDataProvider;
use panix\mod\discounts\models\Discount;
use panix\mod\shop\components\FilterV2;

/**
 * Class CatalogController
 *
 * @property \panix\engine\data\ActiveDataProvider $provider
 * @property array $currentUrl
 *
 * @package panix\mod\shop\controllers
 */
class TagController extends FilterController
{

    public $provider;
    public $currentUrl;

    public function beforeAction($action)
    {
        if (Yii::$app->request->headers->has('filter-ajax')) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }


    public function actionView($tag)
    {
        if (!$tag) {
            return $this->error404();
        }
        $this->pageName = Yii::t('shop/default', $tag);

        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $this->currentUrl = Url::to(['view']);
        $this->view->canonical = Url::to($this->currentUrl, true);
        $this->view->registerJs("var current_url = '" . $this->currentUrl . "';", yii\web\View::POS_HEAD, 'current_url');


        $this->query = $productModel::find()->published();
        $this->query->anyTagValues($tag);

        $categoriesIds = [];
        $categoriesQuery = clone $this->query;
        $categoriesResult = $categoriesQuery->groupBy('main_category_id')->select(['main_category_id'])->asArray()->all();
        foreach ($categoriesResult as $c) {
            $categoriesIds[] = $c['main_category_id'];
        }

        $categoriesResponse = Category::find()->where(['id' => $categoriesIds])->all();
        if (Yii::$app->request->getQueryParam('category')) {

            $ex = explode(',', Yii::$app->request->getQueryParam('category'));
            $category = Category::findOne(Yii::$app->request->getQueryParam('category'));


            $this->query->applyCategories($category);
            $this->currentUrl = Url::to(['/shop/tag/view', 'category' => Yii::$app->request->getQueryParam('category')]);

            $this->view->params['breadcrumbs'][] = [
                'url' => ['new'],
                'label' => $this->pageName
            ];

            $this->pageName = $category->name;

        }
        $this->refreshUrl = $this->currentUrl;
        $this->view->params['breadcrumbs'][] = $this->pageName;
        $cacheKey = 'filter_catalog_tag';
        if (Yii::$app->request->getQueryParam('category')) {
            $cacheKey .= Yii::$app->request->getQueryParam('category');
        }

        $this->filter = new FilterV2($this->query, ['cacheKey' => $cacheKey]);

        $this->currentQuery = clone $this->query;

        if (Yii::$app->request->get('sort') == 'price' || Yii::$app->request->get('sort') == '-price') {
            $this->filter->resultQuery->aggregatePriceSelect((Yii::$app->request->get('sort') == 'price') ? SORT_ASC : SORT_DESC);
        } else {
            $this->filter->resultQuery->orderBy(['created_at' => SORT_DESC]);
        }
        $this->provider = new ActiveDataProvider([
            'query' => $this->filter->resultQuery,
            'pagination' => [
                'pageSize' => $this->per_page,
            ],
        ]);

        return $this->_render('@shop/views/catalog/view', [
            'categories' => $categoriesResponse,
            'categoriesIds' => $categoriesIds
        ]);
    }


}
