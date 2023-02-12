<?php

namespace panix\mod\shop\controllers;


use panix\engine\CMS;
use Yii;
use yii\helpers\Url;
use yii\web\Response;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Product;
use panix\mod\shop\components\FilterController;

class BrandController extends FilterController
{

    public $provider;
    public $currentUrl;

    public function actionIndex()
    {
        $model = Brand::find()->published()->all();
        $this->currentUrl = '/';
        $this->pageName = Yii::t('shop/default', 'BRANDS');
        $this->view->params['breadcrumbs'][] = $this->pageName;


        $memory = NULL;
        $sorting = [];

        foreach ($model as $item) {
            $productCount = $item->productsCount;
            if ($productCount) {
                $letter = mb_substr($item->name, 0, 1, 'utf-8');

                if ($letter != $memory) {
                    $memory = $letter;
                }
                if (is_numeric($letter)) {
                    $memory = '0-9';
                }

                $sorting[$memory][] = ['item' => $item, 'count' => $productCount];
            }
        }
        ksort($sorting);

        return $this->render('index', ['items' => $sorting]);
    }

    /**
     * Display products by brand
     * @param $slug
     * @return string
     */
    public function actionView($slug)
    {

        $this->findModel($slug);
        // $this->currentUrl = Url::to($this->dataModel->getUrl());
        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $this->query = $productModel::find()->published();
        //$this->query->attachBehaviors((new $productModel)->behaviors());

        $this->query->applyBrands($this->dataModel->id);


        $this->filter = new $this->filterClass($this->query, ['cacheKey' => str_replace('/','-',Yii::$app->controller->route).'-' . $this->dataModel->id]);

        $this->filterQuery = clone $this->query;
        $this->currentQuery = clone $this->query;
       // $this->filter->resultQuery->orderBy(['id' => SORT_DESC]);
        //$this->query->applyAttributes($this->activeAttributes);
        //$this->filterQuery->addorderBy(['created_at'=>SORT_DESC]);
        //$this->currentQuery->orderBy(['created_at'=>SORT_DESC]);

        //$this->applyPricesFilter();
        $this->pageName = $this->dataModel->name;
        $this->view->setModel($this->dataModel);
        $this->view->title = $this->dataModel->name;

        $this->view->text = $this->dataModel->description;

        // $this->query->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);


        $this->view->registerJs("var current_url = '" . Url::to($this->dataModel->getUrl()) . "';", yii\web\View::POS_HEAD, 'current_url');

        $sort = explode(',', Yii::$app->request->get('sort'));
        if ($sort[0] == 'price' || $sort[0] == '-price') {
            $this->filter->resultQuery->aggregatePriceSelect(($sort[0] == 'price') ? SORT_ASC : SORT_DESC);
        }
        //$this->filter->resultQuery->orderBy(['id' => SORT_DESC]); //изза этого не работает сортировка


        $this->provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $this->filter->resultQuery,
            'id' => null,
            'sort' => Product::getSort(),
            'pagination' => [
                'pageSize' => $this->per_page,
            ]
        ]);


        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('shop/default', 'BRANDS'),
            'url' => ['/brand']
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;
        $filterData = $this->filter->getActiveFilters();


        $currentUrl[] = '/shop/brand/view';
        $currentUrl['slug'] = $this->dataModel->slug;
        $this->refreshUrl = $currentUrl;
        $this->view->canonical = Url::to($currentUrl, true);
        //  print_r($filterData);die;
        foreach ($filterData as $name => $filter) {
            if (isset($filter['name'])) { //attributes
                $currentUrl[$filter['name']] = [];
                if (isset($filter['items'])) {
                    $params = [];
                    foreach ($filter['items'] as $item) {
                        $params[] = $item['value'];
                    }
                    $currentUrl[$filter['name']] = implode(',', $params);
                }
            }
        }

        $this->currentUrl = Url::to($currentUrl);

        return $this->_render('view');

    }

    /**
     * @param $slug
     * @return mixed
     */
    protected function findModel($slug)
    {
        $this->dataModel = Brand::find()
            ->where(['slug' => $slug])
            ->published()
            ->one();

        if ($this->dataModel !== null) {
            return $this->dataModel;
        } else {
            $this->error404();
        }
    }

}
