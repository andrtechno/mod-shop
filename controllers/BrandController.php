<?php

namespace panix\mod\shop\controllers;


use panix\engine\CMS;
use panix\mod\shop\components\Filter;
use panix\mod\shop\components\FilterV2;
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
        $this->pageName = Yii::t('shop/default','BRAND');
        $this->view->params['breadcrumbs'][] = $this->pageName;
        return $this->render('index', ['model' => $model]);
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

        $this->filter = new FilterV2($this->query,['cacheKey'=>'filter_brand_'.$this->dataModel->id]);
       // CMS::dump($this->filter,2);die;
        $this->filterQuery = clone $this->query;
        $this->currentQuery = clone $this->query;
        //$this->query->applyAttributes($this->activeAttributes);



        //$this->applyPricesFilter();
        $this->pageName = $this->dataModel->name;
        $this->view->setModel($this->dataModel);
        $this->view->title = $this->dataModel->name;

        $this->view->text = $this->dataModel->description;

       // $this->query->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);



        $this->view->registerJs("var current_url = '" . Url::to($this->dataModel->getUrl()) . "';", yii\web\View::POS_HEAD, 'current_url');


        $sort = explode(',',Yii::$app->request->get('sort'));
        if ($sort[0] == 'price' || $sort[0] == '-price') {
            $this->filter->resultQuery->aggregatePriceSelect(($sort[0] == 'price') ? SORT_ASC : SORT_DESC);
        }

        $this->provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $this->filter->resultQuery,
            'id' => null,
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
            $this->error404('brand not found');
        }
    }

}
