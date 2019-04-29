<?php

namespace panix\mod\shop\controllers;


use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\search\ProductSearch;
use panix\mod\shop\models\translate\ProductTranslate;
use panix\mod\shop\models\TypeAttribute;
use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Attribute;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;

class CategoryController extends WebController
{

    public $allowedPageLimit = [];
    public $query, $provider, $currentQuery;
    private $_eavAttributes;

    /**
     * @var string min price in the query
     */
    private $_currentMinPrice = null;

    /**
     * @var string max price in the query
     */
    private $_currentMaxPrice = null;
    /**
     * @var string
     */
    public $_maxPrice, $_minPrice;

    /**
     * @var string
     */
    public $maxprice, $minprice;

    /**
     * @return mixed
     */
    public function getCurrentMinPrice()
    {
        if ($this->_currentMinPrice !== null)
            return $this->_currentMinPrice;

        if (Yii::$app->request->get('min_price'))
            $this->_currentMinPrice = Yii::$app->request->get('min_price');
        else
            $this->_currentMinPrice = Yii::$app->currency->convert($this->getMinPrice());

        return $this->_currentMinPrice;
    }

    /**
     * @return mixed
     */
    public function getCurrentMaxPrice()
    {
        if ($this->_currentMaxPrice !== null)
            return $this->_currentMaxPrice;

        if (Yii::$app->request->get('max_price'))
            $this->_currentMaxPrice = Yii::$app->request->get('max_price');
        else
            $this->_currentMaxPrice = Yii::$app->currency->convert($this->getMaxPrice());

        return $this->_currentMaxPrice;
    }

    /**
     * @return string min price
     */
    public function getMinPrice()
    {
        if ($this->_minPrice !== null)
            return $this->_minPrice;

        $this->_minPrice = $this->currentQuery->aggregatePrice('MIN');
        return $this->_minPrice;
    }

    /**
     * @return string max price
     */
    public function getMaxPrice()
    {
        $this->_maxPrice = $this->currentQuery->aggregatePrice('MAX');
        return $this->_maxPrice;
    }

    public function getEavAttributes()
    {
        if (is_array($this->_eavAttributes))
            return $this->_eavAttributes;

        // Find category types

        $model = Product::find();
        $query = $model->applyCategories($this->dataModel)->published();

        unset($model);


        $query->addSelect(['type_id']);
        $query->addGroupBy(['type_id']);
        $query->distinct(true);

        //$typesIds = $query->createCommand()->queryColumn();
        $typesIds = Attribute::getDb()->cache(function ($db) use ($query) {
            return $query->createCommand()->queryColumn();
        }, 3600);

        // Find attributes by type
        $query = Attribute::getDb()->cache(function ($db) use ($typesIds) {
            return Attribute::find()
                ->andWhere(['IN', TypeAttribute::tableName().'.type_id', $typesIds])
                ->useInFilter()
                ->addOrderBy(['ordern' => SORT_DESC])
                ->joinWith(['types', 'options'])
                ->all();
        }, 3600);
        /*$query = Attribute::find(['IN', '`types`.type_id', $typesIds])
            ->useInFilter()
            ->orderBy(['ordern' => SORT_DESC])
            ->joinWith(['types', 'options'])
            ->all();*/


        $this->_eavAttributes = array();
        foreach ($query as $attr)
            $this->_eavAttributes[$attr->name] = $attr;
        return $this->_eavAttributes;
    }

    public function getActiveAttributes()
    {
        $data = array();

        foreach (array_keys($_GET) as $key) {
            if (array_key_exists($key, $this->eavAttributes)) {
                if ((boolean)$this->eavAttributes[$key]->select_many === true) {
                    $data[$key] = explode(',', $_GET[$key]);
                } else {
                    $data[$key] = array($_GET[$key]);
                }
            }
        }
        return $data;
    }

    public function beforeAction($action)
    {

        $this->allowedPageLimit = explode(',', Yii::$app->settings->get('shop', 'per_page'));

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
        $this->dataModel = $this->findModel(Yii::$app->request->getQueryParam('seo_alias'));
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
        $this->view->registerJs("var categoryFullUrl = '" . Url::to($this->dataModel->getUrl()) . "';", yii\web\View::POS_HEAD, 'categoryFullUrl');
        if ($view != 'search') {
            $this->view->registerJs("
        var penny = '" . Yii::$app->currency->active->penny . "';
        var separator_thousandth = '" . Yii::$app->currency->active->separator_thousandth . "';
        var separator_hundredth = '" . Yii::$app->currency->active->separator_hundredth . "';
     ", yii\web\View::POS_HEAD, 'numberformat');


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

            if(Yii::$app->request->get('ajax')){
                return $this->renderPartial('listview', [
                    'provider' => $this->provider,
                    'itemView' => $itemView
                ]);
            }else{
                return $this->renderPartial('@shop/widgets/filtersnew/views/current', [
                    'dataModel'=>$this->dataModel,
                    'active'=>$this->getActiveFilters()
                ]);
            }

        } else {
            return $this->render($view, [
                'provider' => $this->provider,
                'itemView' => $itemView
            ]);
        }
    }

    public function applyPricesFilter()
    {
        $minPrice = Yii::$app->request->get('min_price');
        $maxPrice = Yii::$app->request->get('max_price');

        $cm = Yii::$app->currency;
        if ($cm->active->id !== $cm->main->id && ($minPrice > 0 || $maxPrice > 0)) {
            $minPrice = $cm->activeToMain($minPrice);
            $maxPrice = $cm->activeToMain($maxPrice);
        }

        if ($minPrice > 0)
            $this->query->applyMinPrice($minPrice);
        if ($maxPrice > 0)
            $this->query->applyMaxPrice($maxPrice);
    }

    protected function findModel($seo_alias)
    {
        if (($this->dataModel = Category::findOne(['full_path' => $seo_alias])) !== null) {
            return $this->dataModel;
        } else {
            $this->error404('category not found');
        }
    }


    /**
     * Get active/applied filters to make easier to cancel them.
     */
    public function getActiveFilters()
    {
        $request = Yii::$app->request;
        // Render links to cancel applied filters like prices, manufacturers, attributes.
        $menuItems = [];


        if ($this->route == 'shop/category/view' || $this->route == 'shop/category/search') {
            $manufacturers = array_filter(explode(',', $request->getQueryParam('manufacturer')));
            $manufacturers = Manufacturer::getDb()->cache(function ($db) use ($manufacturers) {
                return Manufacturer::findAll($manufacturers);
            }, 3600);
        }

        //$manufacturersIds = array_filter(explode(',', $request->getQueryParam('manufacturer')));


        if ($request->getQueryParam('min_price') || $request->getQueryParam('min_price')) {
            $menuItems['price'] = [
                'label' => Yii::t('shop/default', 'FILTER_BY_PRICE') . ':',
                'itemOptions' => ['id' => 'current-filter-prices']
            ];
        }
        if ($request->getQueryParam('min_price')) {
            $menuItems['price']['items'][] = [
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()), 'currency' => Yii::$app->currency->active->symbol]),
                'linkOptions' => ['class' => 'remove', 'data-price' => 'min_price'],
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'min_price')
            ];
        }

        if ($request->getQueryParam('max_price')) {
            $menuItems['price']['items'][] = [
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MAX', ['value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active->symbol]),
                'linkOptions' => array('class' => 'remove', 'data-price' => 'max_price'),
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'max_price')
            ];
        }

        if ($this->route == 'shop/category/view') {
            if (!empty($manufacturers)) {
                $menuItems['manufacturer'] = array(
                    'label' => Yii::t('shop/default', 'FILTER_BY_MANUFACTURER') . ':',
                    'itemOptions' => array('id' => 'current-filter-manufacturer')
                );
                foreach ($manufacturers as $id => $manufacturer) {
                    $menuItems['manufacturer']['items'][] = [
                        'label' => $manufacturer->name,
                        'linkOptions' => array(
                            'class' => 'remove',
                            'data-name' => 'manufacturer',
                            'data-target' => '#filter_manufacturer_' . $manufacturer->id
                        ),
                        'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'manufacturer', $manufacturer->id)
                    ];
                }
            }
        }

        // Process eav attributes
        $activeAttributes = $this->activeAttributes;
        if (!empty($activeAttributes)) {
            foreach ($activeAttributes as $attributeName => $value) {
                if (isset($this->eavAttributes[$attributeName])) {
                    $attribute = $this->eavAttributes[$attributeName];
                    $menuItems[$attributeName] = [
                        'label' => $attribute->title . ':',
                        'itemOptions' => array('id' => 'current-filter-' . $attribute->name)
                    ];
                    foreach ($attribute->options as $option) {
                        if (isset($activeAttributes[$attribute->name]) && in_array($option->id, $activeAttributes[$attribute->name])) {
                            $menuItems[$attributeName]['items'][] = [
                                'label' => $option->value . (($attribute->abbreviation) ? ' ' . $attribute->abbreviation : ''),
                                'linkOptions' => [
                                    'class' => 'remove',
                                    'data-name' => $attribute->name,
                                    'data-target' => "#filter_{$attribute->name}_{$option->id}"
                                ],
                                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, $attribute->name, $option->id)
                            ];
                            sort($menuItems[$attributeName]['items']);
                        }
                    }
                }
            }
        }

        return $menuItems;
    }

}
