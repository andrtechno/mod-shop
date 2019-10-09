<?php

namespace panix\mod\shop\controllers;


use Yii;
use yii\helpers\Url;
use yii\web\Response;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\Product;
use panix\mod\shop\components\FilterController;

class ManufacturerController extends FilterController
{


    public function actionIndex()
    {
        $model = Manufacturer::find()->published()->all();
        $this->currentUrl = '/';
        $this->pageName = Yii::t('shop/default','MANUFACTURER');
        return $this->render('index', ['model' => $model]);
    }

    /**
     * Display products by manufacturer
     * @param $slug
     * @return string
     */
    public function actionView($slug)
    {

        $this->findModel($slug);
       // $this->currentUrl = Url::to($this->dataModel->getUrl());
        $this->query = Product::find();
        $this->query->attachBehaviors((new Product)->behaviors());
        $this->query->published();
        $this->query->applyManufacturers($this->dataModel->id);
        $this->query->applyAttributes($this->activeAttributes);

        $this->currentQuery = clone $this->query;

        $this->applyPricesFilter();
        $this->pageName = $this->dataModel->name;
        $this->view->title = $this->dataModel->name;
        $this->view->registerJs("var current_url = '" . Url::to($this->dataModel->getUrl()) . "';", yii\web\View::POS_HEAD, 'current_url');
        $provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $this->query,
            'id' => null,
            'pagination' => [
                'pageSize' => $this->per_page,
            ]
        ]);


        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'CATALOG'),
            'url' => ['/shop']
        ];

        $filterData = $this->getActiveFilters();




        $currentUrl[] = '/shop/manufacturer/view';
        $currentUrl['slug'] = $this->dataModel->slug;
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

        if (Yii::$app->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'currentFilters' => $filterData,
                'full_url' => Url::to($this->currentUrl),
                'items' => $this->renderPartial('@shop/views/catalog/listview', [
                    'provider' => $provider,
                    'itemView' => $this->itemView
                ]),
                'currentFiltersData' => $this->renderPartial('@shop/widgets/filtersnew/views/current', [
                    'dataModel' => $this->dataModel,
                    'active' => $filterData
                ])
            ];

        }else{
            return $this->render('view', [
                'provider' => $provider,
                'model' => $this->dataModel
            ]);
        }

    }

    /**
     * @param $slug
     * @return mixed
     */
    protected function findModel($slug)
    {
        $this->dataModel = Manufacturer::find()
            ->where(['slug' => $slug])
            ->published()
            ->one();

        if ($this->dataModel !== null) {
            return $this->dataModel;
        } else {
            $this->error404('manufacturer not found');
        }
    }

}
