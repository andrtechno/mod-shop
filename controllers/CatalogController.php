<?php

namespace panix\mod\shop\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use panix\mod\shop\components\FilterController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Category;

class CatalogController extends FilterController
{

    public $provider;
    public $currentUrl;

    public function beforeAction($action)
    {
        $this->dataModel = $this->findModel(Yii::$app->request->getQueryParam('slug'));
        return parent::beforeAction($action);
    }

    public function actionView()
    {

        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $this->currentUrl = $this->dataModel->getUrl();
        $this->query = $productModel::find();
        $this->query->attachBehaviors((new $productModel)->behaviors());
        $this->query->sort();


        //  $cr->with = array('manufacturerActive');
        // Скрывать товары если производитель скрыт.
        //TODO: если у товара не выбран производитель то он тоже скрывается!! need fix
        //$this->query->with(array('manufacturer' => array(
        //        'scopes' => array('published')
        //)));

        $this->query->applyCategories($this->dataModel);
        //$this->query->andWhere([Product::tableName().'.main_category_id'=>$this->dataModel->id]);

        //  $this->query->with('manufacturerActive');
        $this->pageName = $this->dataModel->name;
        //$this->view->title = $this->pageName;
        $this->view->registerJs("var current_url = '" . Url::to($this->dataModel->getUrl()) . "';", yii\web\View::POS_HEAD, 'current_url');

        $this->query->published();
        $this->query->applyAttributes($this->activeAttributes);

        // Filter by manufacturer
        if (Yii::$app->request->get('manufacturer')) {
            $manufacturers = explode(',', Yii::$app->request->get('manufacturer', ''));
            $this->query->applyManufacturers($manufacturers);
        }

//echo $this->query->createCommand()->rawSql;die;
        // Create clone of the current query to use later to get min and max prices.
        $this->currentQuery = clone $this->query;
        // Filter products by price range if we have min or max in request
        $this->applyPricesFilter();

        // $this->maxprice = (int)$this->currentQuery->max('price');
        // $this->minprice = (int)$this->currentQuery->min('price');
        //$this->maxprice = $this->getMaxPrice();
        //$this->minprice = $this->getMinPrice();


        //$this->query->addOrderBy(['price'=>SORT_DESC]);
        //$this->query->orderBy(['price'=>SORT_DESC]);


        if (Yii::$app->request->get('sort') == 'price' || Yii::$app->request->get('sort') == '-price') {
            $this->query->aggregatePriceSelect((Yii::$app->request->get('sort') == 'price') ? SORT_ASC : SORT_DESC);
        }

        //echo $this->query->createCommand()->rawSql;die;
        $this->provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $this->query,
            'sort' => Product::getSort(),
            'pagination' => [
                'pageSize' => $this->per_page,
                // 'defaultPageSize' =>(int)  $this->allowedPageLimit[0],
                // 'pageSizeLimit' => $this->allowedPageLimit,
            ]
        ]);


        $this->view->title = $this->dataModel->title();


        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'CATALOG'),
            'url' => ['/catalog']
        ];

        $ancestors = $this->dataModel->ancestors()->addOrderBy('depth')->excludeRoot()->cache(3600)->all();
        //$ancestors = Category::getDb()->cache(function ($db) use ($m) {
        //     return $m->ancestors()->addOrderBy('depth')->excludeRoot()->all();
        // }, 3600);

        if ($ancestors) {
            foreach ($ancestors as $category) {
                $this->breadcrumbs[] = [
                    'label' => $category->name,
                    'url' => $category->getUrl()
                ];
            }
        }

        $name = $this->dataModel->name;


        $filterData = $this->getActiveFilters();


        $currentUrl[] = '/shop/catalog/view';
        $currentUrl['slug'] = $this->dataModel->full_path;

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

        /*unset($filterData['price']);
        if ($filterData) {
            $name = '';
            foreach ($filterData as $filterKey => $filterItems) {
                if ($filterKey == 'manufacturer') {
                    $manufacturerNames = [];
                    foreach ($filterItems['items'] as $mKey => $mItems) {
                        $manufacturerNames[] = $mItems['label'];
                    }
                    $sep = (count($manufacturerNames) > 2) ? ', ' : ' ' . Yii::t('shop/default', 'AND') . ' ';
                    $name .= ' ' . implode($sep, $manufacturerNames);
                    $this->pageName .= ' ' . implode($sep, $manufacturerNames);
                } else {
                    $attributesNames[$filterKey] = [];
                    foreach ($filterItems['items'] as $mKey => $mItems) {
                        $attributesNames[$filterKey][] = $mItems['label'];
                    }
                    $prefix = isset($filterData['manufacturer']) ? '; ' : ', ';

                    $sep = (count($attributesNames[$filterKey]) > 2) ? ', ' : ' ' . Yii::t('shop/default', 'AND') . ' ';
                    $name .= $prefix . $filterItems['label'] . ' ' . implode($sep, $attributesNames[$filterKey]);
                    $this->pageName .= $prefix . $filterItems['label'] . ' ' . implode($sep, $attributesNames[$filterKey]);
                    $this->view->title = $this->pageName;
                }
            }
            $this->breadcrumbs[] = [
                'label' => $this->dataModel->name,
                'url' => $this->dataModel->getUrl()
            ];
        }*/
        if(Yii::$app->settings->get('shop','smart_bc')){
            $smartData = $this->smartNames();
            $this->breadcrumbs[] = [
                'label' => $this->dataModel->name,
                'url' => $this->dataModel->getUrl()
            ];
            //CMS::dump($smartData);die;
            $this->breadcrumbs[] = $smartData['breadcrumbs'];
        }else{
            $this->breadcrumbs[] = $this->dataModel->name;
        }
        if(Yii::$app->settings->get('shop','smart_title')){
            $smartData = $this->smartNames();
            $this->pageName .= $smartData['title'];
            $this->view->title = $this->pageName;
        }



        //var_dump(Yii::$app->request->headers['filter-ajax']);die;

        if (Yii::$app->request->isAjax) {

            if (Yii::$app->request->headers->has('filter-ajax')) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    //'currentFilters' => $filterData,
                    //'full_url' => Url::to($this->currentUrl),
                    'currentUrl' => Yii::$app->request->getUrl(),
                    'items' => $this->renderPartial('listview', [
                        'provider' => $this->provider,
                        'itemView' => $this->itemView
                    ]),
                    'i' => $this->itemView,
                    'currentFiltersData' => ($filterData) ? $this->renderPartial('@app/widgets/filters/current', [ //'@shop/widgets/filtersnew/views/current', '@app/widgets/filters/current'
                        'dataModel' => $this->dataModel,
                        'active' => $filterData
                    ]) : null
                ];
            } else {
                return $this->renderPartial('listview', [
                    'provider' => $this->provider,
                    'itemView' => $this->itemView
                ]);
            }
        }

        return $this->render('view', [
            'provider' => $this->provider,
            'itemView' => $this->itemView
        ]);
    }

    protected function findModel($slug)
    {
        if (($this->dataModel = Category::findOne(['full_path' => $slug])) !== null) {
            return $this->dataModel;
        } else {
            $this->error404('category not found');
        }
    }

    public function actionAjaxFilterPrices()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [];
    }

}
