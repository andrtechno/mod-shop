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
        $this->query = Product::find();
        $this->query->attachBehaviors((new Product)->behaviors());
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
        $this->applyPricesFilter();

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

        $this->pageName = Yii::t('shop/default', 'SEARCH_RESULT', [
            'query' => Html::encode(Yii::$app->request->get('q')),
            'count' => $this->provider->totalCount,
        ]);
        $this->view->title = $this->pageName;
        $filterData = $this->getActiveFilters();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'currentFilters' => $filterData,
                'full_url' => Url::to($this->currentUrl),
                'items' => $this->renderPartial('@shop/views/catalog/listview', [
                    'provider' => $this->provider,
                    'itemView' => $this->itemView
                ]),
                'currentFiltersData' => $this->renderPartial('@shop/widgets/filtersnew/views/current', [
                    'dataModel' => $this->dataModel,
                    'active' => $filterData
                ])
            ];
        } else {
            return $this->render('index', [
                'provider' => $this->provider,
                'itemView' => $this->itemView
            ]);
        }
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


        if (Yii::$app->request->isAjax && Yii::$app->request->get('q')) {
            $res = [];
            $model = Product::find();
            $model->applySearch(Yii::$app->request->get('q'));
            //'fullurl'=>Html::a('FULL',Yii::$app->urlManager->createUrl(['/shop/catalog/search', 'q' => Yii::$app->request->post('q')])),
            foreach ($model->all() as $m) {
                /** @var Product $m */
                $res[] = [
                    'url' => Url::to($m->getUrl()),
                    'renderItem' => $this->renderPartial('@shop/widgets/search/views/_item', [
                        'name' => $m->name,
                        'price' => $m->getFrontPrice(),
                        'url' => $m->getUrl(),
                        'image' => $m->getMainImage('50x50')->url,
                    ])
                ];
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $res;
        }
        //if (!$q) {
        //    return $this->render('search');
        //} else {
        //    return $this->doSearch($q, 'search');
        //}
    }

    protected function findModel($slug)
    {
        if (($this->dataModel = Category::findOne(['full_path' => $slug])) !== null) {
            return $this->dataModel;
        } else {
            $this->error404('category not found');
        }
    }

}
