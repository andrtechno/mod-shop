<?php

namespace panix\mod\shop\controllers;

use panix\engine\Html;
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
        $this->query->attachBehaviors((new $productModel)->behaviors());
        $this->query->sort();
        $this->query->published();


        // Filter by manufacturer
        if (Yii::$app->request->get('manufacturer')) {
            $manufacturers = explode(',', Yii::$app->request->get('manufacturer', ''));
            $this->query->applyManufacturers($manufacturers);
        }
        $this->query->groupBy(Product::tableName() . '.`id`');
        $this->query->applySearch(Yii::$app->request->get('q'));
        $this->query->applyAttributes($this->activeAttributes);


        // Create clone of the current query to use later to get min and max prices.
        $this->currentQuery = clone $this->query;
        // Filter products by price range if we have min or max in request
        //$this->applyPricesFilter();

        if (Yii::$app->request->get('sort') == 'price' || Yii::$app->request->get('sort') == '-price') {
            $this->query->aggregatePriceSelect((Yii::$app->request->get('sort') == 'price') ? SORT_ASC : SORT_DESC);
        }

        //echo $this->query->createCommand()->rawSql;die;
        $this->provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $this->query,
            'sort' => Product::getSort(),
            'pagination' => [
                'pageSize' => $this->per_page,
            ]
        ]);
        $this->view->registerJs("var current_url = '" . Url::to(Yii::$app->request->getUrl()) . "';", yii\web\View::POS_HEAD, 'current_url');

        $this->pageName = Yii::t('shop/default', 'SEARCH_RESULT', [
            'query' => Html::encode(Yii::$app->request->get('q')),
            'count' => $this->provider->totalCount,
        ]);
        $this->view->title = $this->pageName;
        $this->breadcrumbs[] = Yii::t('shop/default', 'SEARCH');
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


        if (Yii::$app->request->isAjax && $q) {
            $res = [];
            $model = Product::find();
            $model->applySearch($q);
            $model->limit(5);

            $result = $model->all();

            $res['count'] = count($result);
            /** @var Product $m */
            //$res['data'] = $this->renderAjax('@shop/widgets/search/views/_result', ['model' => $model->all(),'q'=>$q]);
            foreach ($result as $m) {
                /** @var Product $m */
                $res[] = [
                    'url' => Url::to($m->getUrl()),
                    'renderItem' => $this->renderPartial('@shop/widgets/search/views/_item', ['model' => $m])
                ];
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $res;
        }
    }

}
