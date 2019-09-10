<?php

namespace panix\mod\shop\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use panix\mod\shop\components\FilterController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Category;

class CategoryController extends FilterController
{

    public $allowedPageLimit = [];
    public $provider;


    public function beforeAction($action)
    {

        $this->allowedPageLimit = explode(',', Yii::$app->settings->get('shop', 'per_page'));

        Url::remember();

        if (Yii::$app->request->post('min_price') || Yii::$app->request->post('max_price')) {
            $data = [];
            if (Yii::$app->request->post('min_price'))
                $data['min_price'] = (int)Yii::$app->request->post('min_price');
            if (Yii::$app->request->post('max_price'))
                $data['max_price'] = (int)Yii::$app->request->post('max_price');

            if ($this->action->id === 'search') {
                return $this->redirect(Yii::$app->urlManager->addUrlParam('/shop/category/search', $data));
            } else {

                /*if (!Yii::app()->request->isAjaxRequest) {
                    if (Yii::app()->request->getPost('filter')) {
                        foreach (Yii::app()->request->getPost('filter') as $key => $filter) {
                            $data[$key] = $filter;
                        }
                    }
                    return $this->redirect(Yii::app()->request->addUrlParam('/shop/category/view', $data));
                }*/


                return $this->redirect(Yii::$app->urlManager->addUrlParam('/shop/category/view', $data));
            }
        }
        return parent::beforeAction($action);
    }

    public function actionView()
    {
        $this->dataModel = $this->findModel(Yii::$app->request->getQueryParam('slug'));
        // $this->canonical = Yii::$app->urlManager->createAbsoluteUrl($this->dataModel->getUrl());
        return $this->doSearch($this->dataModel, 'view');
    }

    /**
     * Search products
     */
    public function actionSearch()
    {
        if (Yii::$app->request->isPost) {
            return $this->redirect(Yii::$app->urlManager->addUrlParam('/shop/category/search', ['q' => Yii::$app->request->post('q')]));
        }
        $q = Yii::$app->request->get('q');
        if (empty($q)) {
            $q = '+';
        }


        if (Yii::$app->request->isAjax && Yii::$app->request->get('q')) {
            $res = [];
            $model = Product::find();
            $model->applySearch(Yii::$app->request->get('q'));
            //'fullurl'=>Html::a('FULL',Yii::$app->urlManager->createUrl(['/shop/category/search', 'q' => Yii::$app->request->post('q')])),
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
        if (!$q) {
            return $this->render('search');
        } else {
            return $this->doSearch($q, 'search');
        }
    }

    public function doSearch($data, $view)
    {

        $this->query = Product::find();
        //$searchModel = new ProductSearch();
        //$this->query = $searchModel->searchBySite(Yii::$app->request->getQueryParams());//
        $this->query->attachBehaviors((new Product)->behaviors());

        $this->query->sort();


        if ($data instanceof Category) {
            //  $cr->with = array('manufacturerActive');
            // Скрывать товары если производитель скрыт.
            //TODO: если у товара не выбран производитель то он тоже скрывается!! need fix
            //$this->query->with(array('manufacturer' => array(
            //        'scopes' => array('published')
            //)));

            $this->query->applyCategories($this->dataModel);
            //$this->query->andWhere([Product::tableName().'.main_category_id'=>$this->dataModel->id]);

            //  $this->query->with('manufacturerActive');
        } else {
            $this->query->applySearch($data);

        }
        $this->query->published();
        $this->query->applyAttributes($this->activeAttributes);
        // Filter by manufacturer
		
	if (empty(Yii::$app->request->get('manufacturer')) && isset($_GET['manufacturer'])) {
			//todo panix
			// throw new CHttpException(404, Yii::t('ShopModule.default', 'NOFIND_CATEGORY'));
		}
        if (Yii::$app->request->get('manufacturer')) {
            $manufacturers = explode(',', Yii::$app->request->get('manufacturer', ''));
            $this->query->applyManufacturers($manufacturers);
        }


        // Create clone of the current query to use later to get min and max prices.
        $this->currentQuery = clone $this->query;
        // Filter products by price range if we have min_price or max_price in request
        $this->applyPricesFilter();

        // $this->maxprice = (int)$this->currentQuery->max('price');
        // $this->minprice = (int)$this->currentQuery->min('price');
        //$this->maxprice = $this->getMaxPrice();
        //$this->minprice = $this->getMinPrice();

        $per_page = (int)$this->allowedPageLimit[0];

        // if (isset($_GET['per_page']) && in_array((int) $_GET['per_page'], $this->allowedPageLimit))
        //    $per_page = (int) $_GET['per_page'];

        if (Yii::$app->request->get('per_page') && in_array($_GET['per_page'], $this->allowedPageLimit)) {
            $per_page = (int)Yii::$app->request->get('per_page');
        }


        //$this->query->addOrderBy(['price'=>SORT_DESC]);
        //$this->query->orderBy(['price'=>SORT_DESC]);


        if (Yii::$app->request->get('sort') == 'price' || Yii::$app->request->get('sort') == '-price') {
            $this->query->aggregatePriceSelect((Yii::$app->request->get('sort') == 'price') ? SORT_ASC : SORT_DESC);
        }
        // $this->query->createCommand()->rawSql;die;


        $this->provider = new \panix\engine\data\ActiveDataProvider([

            'query' => $this->query,
            'sort' => Product::getSort(),
            'pagination' => [
                'pageSize' => $per_page,
                // 'defaultPageSize' =>(int)  $this->allowedPageLimit[0],
                // 'pageSizeLimit' => $this->allowedPageLimit,
            ]
        ]);

        // $this->provider->sort = Product::getSort();

        $this->pageName = $this->dataModel->name;
        $name = '';
        $this->view->registerJs("var current_url = '" . Url::to($this->dataModel->getUrl()) . "';", yii\web\View::POS_HEAD, 'current_url');
        if ($view != 'search') {



            $c = Yii::$app->settings->get('shop');
            if ($c->seo_categories) {
                $this->description = $this->dataModel->description();
                $this->view->title = $this->dataModel->title();
            }

            $this->breadcrumbs[] = [
                'label' => Yii::t('shop/default', 'CATALOG'),
                'url' => ['/shop']
            ];
            $m = $this->dataModel;
            $ancestors = Category::getDb()->cache(function ($db) use ($m) {
                return $m->ancestors()->addOrderBy('depth')->excludeRoot()->all();
            }, 3600);
            //$ancestors = $this->dataModel->ancestors()->addOrderBy('depth')->excludeRoot()->all();

            if ($ancestors) {
                foreach ($ancestors as $c) {
                    $this->breadcrumbs[] = [
                        'label' => $c->name,
                        'url' => $c->getUrl()
                    ];
                }
            }

            $name = $this->dataModel->name;
        }

        $itemView = '_view_grid';
        if (Yii::$app->request->get('view')) {
            if (in_array(Yii::$app->request->get('view'), ['list', 'grid'])) {
                $itemView = '_view_' . Yii::$app->request->get('view');
            }
        }


        $filterData = $this->getActiveFilters();


        unset($filterData['price']);
        if ($filterData) {

            foreach ($filterData as $filterKey => $filterItems) {
                if ($filterKey == 'manufacturer') {
                    $manufacturerNames = array();
                    foreach ($filterItems['items'] as $mKey => $mItems) {
                        $manufacturerNames[] = $mItems['label'];
                    }
                    $sep = (count($manufacturerNames) > 2) ? ', ' : ' и ';
                    $name .= ' ' . implode($sep, $manufacturerNames);
                    $this->pageName .= ' ' . implode($sep, $manufacturerNames);
                } else {
                    $attributesNames[$filterKey] = array();
                    foreach ($filterItems['items'] as $mKey => $mItems) {
                        $attributesNames[$filterKey][] = $mItems['label'];
                    }
                    if (isset($filterData['manufacturer'])) {
                        $prefix = '; ';
                    } else {
                        $prefix = ' ';
                    }

                    $sep = (count($attributesNames[$filterKey]) > 2) ? ', ' : ' и ';
                    $name .= $prefix . $filterItems['label'] . ' ' . implode($sep, $attributesNames[$filterKey]);
                    $this->pageName .= $prefix . $filterItems['label'] . ' ' . implode($sep, $attributesNames[$filterKey]);
                    $this->view->title = $this->pageName;
                }
            }
            $this->breadcrumbs[] = [
                'label' => $this->dataModel->name,
                'url' => $this->dataModel->getUrl()
            ];
        }
        $this->breadcrumbs[] = $name;

        if (Yii::$app->request->isAjax) {
            //\Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

            if (Yii::$app->request->get('render') == 'active-filters') {
                return $this->renderPartial('@shop/widgets/filtersnew/views/current', [
                    'dataModel' => $this->dataModel,
                    'active' => $filterData
                ]);
            } else {
                return $this->renderPartial('listview', [
                    'provider' => $this->provider,
                    'itemView' => $itemView
                ]);
            }


        } else {
            return $this->render($view, [
                'provider' => $this->provider,
                'itemView' => $itemView
            ]);
        }
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
