<?php

namespace panix\mod\shop\controllers;

use panix\engine\CMS;
use panix\engine\Html;
use panix\mod\shop\components\Filter;
use panix\mod\shop\components\FilterV2;
use Yii;
use yii\helpers\Url;
use yii\web\Response;
use panix\mod\shop\components\FilterController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Category;

class SearchController extends FilterController
{

    public $provider;
    public $currentUrl;

    public function actionIndex()
    {
        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $this->query = $productModel::find();
        //$this->query->attachBehaviors((new $productModel)->behaviors());
$q=Yii::$app->request->post('q');


        //fix for POST send form
        if (!Yii::$app->request->isPjax || !Yii::$app->request->isAjax) {
            if ($q)
                return $this->redirect(['/shop/search/index', 'q' => Yii::$app->request->post('q')]);
        }

        // Filter by brand
        //if (Yii::$app->request->get('brand')) {
        //    $brands = explode(',', Yii::$app->request->get('brand', ''));
       //     $this->query->applyBrands($brands);
      //  }


        //comment
        //$this->query->groupBy(Product::tableName() . '.`id`');

        //если пусто, делаем фейк запрос для "не чего не найдено"
        $queryGet = Yii::$app->request->get('q');
        if(empty($queryGet)){
            $queryGet = CMS::gen(10);
        }
        $this->query->applySearch($queryGet)->published();
        //$this->query->applySearch($queryGet);

        $this->filter = new FilterV2($this->query,['cacheKey'=>'filter_search_'.$queryGet]);

        // Create clone of the current query to use later to get min and max prices.
        $this->filterQuery = clone $this->query;
        $this->currentQuery = clone $this->query;
        $this->filter->resultQuery->sort();
        //$this->query->applyAttributes($this->filter->activeAttributes);

        // Filter products by price range if we have min or max in request
        //$this->applyPricesFilter();
        $sort = explode(',', Yii::$app->request->get('sort'));
        if ($sort[0] == 'price' || $sort[0] == '-price') {
            $this->filter->resultQuery->aggregatePriceSelect(($sort[0] == 'price') ? SORT_ASC : SORT_DESC);
        }

        //echo $this->query->createCommand()->rawSql;die;
        $this->provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $this->filter->resultQuery,
            'sort' => $productModel::getSort(),
            'pagination' => [
                'pageSize' => $this->per_page,
            ]
        ]);

        $this->currentUrl = Url::to(['/shop/search/index', 'q' => Yii::$app->request->get('q')]);
        $this->refreshUrl=$this->currentUrl;
        $this->view->canonical = Url::to(['/shop/search/index', 'q' => Yii::$app->request->get('q')], true);
        $this->view->registerJs("var current_url = '" . $this->currentUrl . "';", yii\web\View::POS_HEAD, 'current_url');

        $this->pageName = Yii::t('shop/default', 'SEARCH_RESULT', [
            'query' => Html::encode(Yii::$app->request->get('q')),
            'count' => $this->provider->totalCount,
        ]);
        $this->view->title = $this->pageName;
        $this->view->params['breadcrumbs'][] = Yii::t('shop/default', 'SEARCH');
        //  $filterData = $this->getActiveFilters();

        return $this->_render('@shop/views/search/index');
    }

    /**
     * Search products
     */
    public function actionAjax()
    {
        if (Yii::$app->request->isPost) {
            return $this->redirect(Yii::$app->urlManager->addUrlParam('/shop/catalog/search', ['q' => Yii::$app->request->post('q')]));
        }
        $q = Yii::$app->request->get('q');
        if (empty($q)) {
            $q = '+';
        }

        $res = [];
        if (Yii::$app->request->isAjax && $q) {
            /** @var Product $productModel */
            $productModel = Yii::$app->getModule('shop')->model('Product');
            $model = $productModel::find();
            $model->applySearch($q);
            $model->published()->limit(16);

            $result = $model->all();

            //$res['count'] = count($result);
            foreach ($result as $m) {
                /** @var Product $m */
                $res[] = [
                    //'html' => $this->renderPartial('@shop/widgets/search/views/_item', ['model' => $m]),
                    'id' => $m->id,
                    'name' => $m->name,
                    'price' => $m->getFrontPrice(),
                    'currency' => Yii::$app->currency->active['symbol'],
                    'url' => Url::to($m->getUrl()),
                    'image' => $m->getMainImage('80x80')->url,
                ];
            }

        }


        $status = true;

        return $this->asJson(array(
            "status" => $status,
            "error" => null,
            "data" => array(
                "products" => $res
            )
        ));


    }

}
