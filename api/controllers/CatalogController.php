<?php

namespace panix\mod\shop\api\controllers;

use panix\engine\api\ApiActiveController;
use panix\engine\api\Serializer;
use panix\engine\api\ApiHelpers;
use panix\engine\CMS;
use panix\engine\controllers\WebController;
use panix\engine\data\ActiveDataProvider;
use panix\mod\discounts\models\Discount;
use panix\mod\pages\models\Pages;
use panix\mod\shop\components\Filter;
use panix\mod\shop\components\FilterV2;
use panix\mod\shop\models\ProductCategoryRef;
use Yii;
use yii\helpers\Url;
use yii\web\Response;
use panix\mod\shop\components\FilterController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Category;

/**
 * Class CatalogController
 *
 * @property \panix\engine\data\ActiveDataProvider $provider
 * @property array $currentUrl
 *
 * @package panix\mod\shop\controllers
 */
class CatalogController extends ApiActiveController
{
    public $modelClass = 'panix\mod\shop\api\models\Category';
    public $serializer = [
        'class' => Serializer::class,
    ];
    public $provider;
    public $currentUrl;

    public function actionFilterCallback()
    {
        return $this->asJson(['ok' => true]);
    }

    public function beforeAction($action)
    {
        if (Yii::$app->request->headers->has('filter-ajax')) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionSearch()
    {
        $lang = Yii::$app->language;
        $config = Yii::$app->settings->get('shop');
        $q = Yii::$app->request->get('q');
        if (empty($q)) {
            $q = '+';
        }
        $json = [];

        if ($q) {
            $model = Product::find()->published()->limit(16);
            $model->applySearch($q);
            if (!empty($config->search_availability)) {
                $model->andWhere(["availability" => $config->search_availability]);
            }
            $model->sort(SORT_DESC);
            //echo $model->createCommand()->rawSql;die;
            $result = $model->all();
            $json['data']['products'] = [];
            foreach ($result as $m) {
                /** @var Product $m */
                $name = "name_{$lang}";
                $json['data']['products'][] = [
                    'id' => $m->id,
                    'name' => $m->{$name},
                    'price' => Yii::$app->currency->number_format($m->hasDiscount ? $m->discountPrice : $m->price, $m->currency_id),
                    'currency' => Yii::$app->currency->getById($m->currency_id)->symbol,
                    //'url' => ApiHelpers::url($m->getUrl()),
                    'url' => ApiHelpers::url("/product/" . $m->slug . '-' . $m->id, true),
                    'image' => $m->getMainImage('small')->url,
                    'image_original' => $m->getMainImage()->url,
                ];
            }

        }
        $json['success'] = true;
        return $this->asJson($json);
    }

    public function actionView()
    {

        $this->dataModel = $this->findModel(Yii::$app->request->getQueryParam('slug'));


        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $this->currentUrl = $this->dataModel->getUrl();
        $this->query = $productModel::find();
        $this->query->andWhere(['!=', "{$productModel::tableName()}.availability", $productModel::STATUS_ARCHIVE]);
        $this->query->sortAvailability();
        $this->query->published();


        //  $cr->with = array('brandActive');
        // Скрывать товары если бренд скрыт.
        //TODO: если у товара не выбран бренд то он тоже скрывается!! need fix
        //$this->query->with(array('brand' => array(
        //        'scopes' => array('published')
        //)));

        //$this->query->applyCategories($this->dataModel, 'andWhere', $this->dataModel->children()->count());
        $this->query->applyCategories($this->dataModel, 'andWhere', $this->dataModel->children()->count());
//echo $this->dataModel->children()->count();

        $this->filter = new FilterV2($this->query, ['cacheKey' => 'filter_catalog_' . $this->dataModel->id]);
        //$this->filter->resultQuery->applyAttributes($this->filter->activeAttributes);
        // if (Yii::$app->request->get('brand')) {
        //     $brands = explode(',', Yii::$app->request->get('brand', ''));
        //     $this->filter->resultQuery->applyBrands($brands);
        // }

        //   echo $this->filter->getMaxPrice();
        //  CMS::dump($this->filter,2);die;

        $this->filterQuery = clone $this->query;
        $this->currentQuery = clone $this->query;


        $this->filter->resultQuery;//->sort()
        //$this->query->andWhere([Product::tableName().'.main_category_id'=>$this->dataModel->id]);
        //  $this->query->with('brandActive');
        $this->pageName = $this->dataModel->name;
        $this->view->setModel($this->dataModel);
        //$this->view->title = $this->pageName;
        $this->refreshUrl = $this->dataModel->getUrl();
        $this->view->registerJs("var current_url = '" . Url::to($this->dataModel->getUrl()) . "';", yii\web\View::POS_HEAD, 'current_url');


        //  $this->query->applyAttributes($this->filter->activeAttributes);
        //$this->query->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);

//echo $this->query->createCommand()->rawSql;die;
        // Create clone of the current query to use later to get min and max prices.

        // Filter by brand
        if (Yii::$app->request->get('brand')) {
            $brands = explode(',', Yii::$app->request->get('brand', ''));
            //$this->query->applyBrands($brands);
        }
        // Filter products by price range if we have min or max in request


        //$this->query->addOrderBy(['price'=>SORT_DESC]);
        //$this->query->orderBy(['price'=>SORT_DESC]);


        $sort = explode(',', Yii::$app->request->get('sort'));
        if ($sort[0] == 'price' || $sort[0] == '-price') {
            $this->filter->resultQuery->aggregatePriceSelect(($sort[0] == 'price') ? SORT_ASC : SORT_DESC);

            // echo $this->query->createCommand()->rawSql;die;
        }

        //  echo  $this->filter->resultQuery->createCommand()->rawSql;die;

        $this->provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $this->filter->resultQuery,
            'sort' => Product::getSort(),
            'pagination' => [
                'pageSize' => $this->per_page,
                // 'defaultPageSize' =>(int)  $this->allowedPageLimit[0],
                // 'pageSizeLimit' => $this->allowedPageLimit,
            ]
        ]);


        //if (YII_DEBUG) {
        //   $s = $this->dataModel->parent()->andWhere(['use_seo_parents'=>1])->addOrderBy(['depth'=>SORT_ASC])->one();

        /*  if ($this->dataModel->use_seo_parents) {

              $this->view->title = $this->dataModel->title($this->dataModel->name);
              $this->view->description = $this->dataModel->description($this->dataModel->name);
              $this->view->h1 = $this->dataModel->h1($this->dataModel->name);
          } else {
              $s = $this->dataModel->parent()
                  ->andWhere(['use_seo_parents' => 1])
                  ->addOrderBy(['depth' => SORT_DESC])
                  ->one();
              if ($s) {
                  $this->view->title = $s->title($this->dataModel->name).'zzz';
                  $this->view->description = $s->description($this->dataModel->name);
                  $this->view->h1 = $s->h1($this->dataModel->name);
              } else {
                  $this->view->title = $this->dataModel->title($this->dataModel->name);
                  $this->view->description = $this->dataModel->description($this->dataModel->name);
                  $this->view->h1 = $this->dataModel->h1($this->dataModel->name);
              }

          }*/
        $min_price = $this->filter->price_min;
        $meta_params['{name}'] = $this->dataModel->name;
        $meta_params['{min_price}'] = ($min_price) ? Yii::$app->currency->number_format($min_price) : 0;
        $meta_params['{currency.symbol}'] = Yii::$app->currency->active['symbol'];

        if ($this->dataModel->use_seo_parents) {

            $this->view->title = $this->dataModel->title($meta_params);
            $this->view->description = $this->dataModel->description($meta_params);
            $this->view->h1 = $this->dataModel->h1($meta_params);
        } else {
            $s = $this->dataModel->parent()
                ->andWhere(['use_seo_parents' => 1])
                ->addOrderBy(['depth' => SORT_DESC])
                ->one();
            if ($s) {
                if (!empty($this->dataModel->meta_title)) {
                    $this->view->title = $this->dataModel->title($meta_params);
                } else {
                    $this->view->title = $s->title($meta_params);
                }
                if (!empty($this->dataModel->meta_description)) {
                    $this->view->description = $this->dataModel->description($meta_params);
                } else {
                    $this->view->description = $s->description($meta_params);
                }

                if (!empty($this->dataModel->h1)) {
                    $this->view->h1 = $this->dataModel->h1($meta_params);
                } else {
                    $this->view->h1 = (!empty($s->h1)) ? $s->h1($meta_params) : $this->dataModel->h1($meta_params);
                }


            } else {
                $this->view->title = $this->dataModel->title($meta_params);
                $this->view->description = $this->dataModel->description($meta_params);
                $this->view->h1 = $this->dataModel->h1($meta_params);
            }

        }

        //}


        /* $this->view->params['breadcrumbs'][] = [
             'label' => Yii::t('shop/default', 'CATALOG'),
             'url' => ['/catalog']
         ];*/

        $ancestors = $this->dataModel->ancestors()->addOrderBy('depth')->excludeRoot()->cache(3600)->all();
        //$ancestors = Category::getDb()->cache(function ($db) use ($m) {
        //     return $m->ancestors()->addOrderBy('depth')->excludeRoot()->all();
        // }, 3600);

        if ($ancestors) {
            foreach ($ancestors as $category) {
                $this->view->params['breadcrumbs'][] = [
                    'label' => $category->name,
                    'url' => $category->getUrl()
                ];
            }
        }

        $name = $this->dataModel->name;


        $filterData = $this->filter->getActiveFilters();


        $currentUrl[] = '/shop/catalog/view';
        $currentUrl['slug'] = $this->dataModel->full_path;
        $this->view->canonical = Url::to($currentUrl, true);
        foreach ($filterData as $name => $filter) {
            if (isset($filter['name'])) { //attributes
                $currentUrl[$filter['name']] = [];
                if (isset($filter['items'])) {
                    $params = [];
                    foreach ($filter['items'] as $item) {
                        $params[] = ($filter['name'] == 'price') ? $item['value_url'] : $item['value'];
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
                if ($filterKey == 'brand') {
                    $brandNames = [];
                    foreach ($filterItems['items'] as $mKey => $mItems) {
                        $brandNames[] = $mItems['label'];
                    }
                    $sep = (count($brandNames) > 2) ? ', ' : ' ' . Yii::t('yii', 'AND') . ' ';
                    $name .= ' ' . implode($sep, $brandNames);
                    $this->pageName .= ' ' . implode($sep, $brandNames);
                } else {
                    $attributesNames[$filterKey] = [];
                    foreach ($filterItems['items'] as $mKey => $mItems) {
                        $attributesNames[$filterKey][] = $mItems['label'];
                    }
                    $prefix = isset($filterData['brand']) ? '; ' : ', ';

                    $sep = (count($attributesNames[$filterKey]) > 2) ? ', ' : ' ' . Yii::t('yii', 'AND') . ' ';
                    $name .= $prefix . $filterItems['label'] . ' ' . implode($sep, $attributesNames[$filterKey]);
                    $this->pageName .= $prefix . $filterItems['label'] . ' ' . implode($sep, $attributesNames[$filterKey]);
                    $this->view->title = $this->pageName;
                }
            }
            $this->view->params['breadcrumbs'][] = [
                'label' => $this->dataModel->name,
                'url' => $this->dataModel->getUrl()
            ];
        }*/
        if (Yii::$app->settings->get('shop', 'smart_bc')) {
            $smartData = $this->smartNames();
            $this->view->params['breadcrumbs'][] = [
                'label' => $this->dataModel->name,
                'url' => $this->dataModel->getUrl()
            ];
            if ($smartData['breadcrumbs'])
                $this->view->params['breadcrumbs'][] = $smartData['breadcrumbs'];
        } else {
            $this->view->params['breadcrumbs'][] = $this->dataModel->name;
        }
        if (Yii::$app->settings->get('shop', 'smart_title')) {
            $smartData = $this->smartNames();
            $this->pageName .= $smartData['title'];
            if ($this->view->title) {
                $this->view->title .= $smartData['title'];
                //  $this->view->h1 = $smartData['title'];
            } else {
                $this->view->title = $this->pageName;
            }

        }


        return $this->_render();
    }

    public function actionNew()
    {
        $config = Yii::$app->settings->get('shop');
        $this->pageName = Yii::t('shop/default', 'NEW');

        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $this->currentUrl = Url::to(['new']);
        $this->view->canonical = Url::to($this->currentUrl, true);
        $this->view->registerJs("var current_url = '" . $this->currentUrl . "';", yii\web\View::POS_HEAD, 'current_url');


        $this->query = $productModel::find()->published()->new();


        $categoriesIds = [];
        $categoriesQuery = clone $this->query;
        $categoriesResult = $categoriesQuery->groupBy('main_category_id')->select(['main_category_id'])->asArray()->all();
        foreach ($categoriesResult as $c) {
            $categoriesIds[] = $c['main_category_id'];
        }


        // $categoriesResponse = Category::find()->dataTree(1, null, ['switch' => 1,'id'=>$categoriesIds],CMS::gen(100));
        //  CMS::dump($categoriesResponse);die;
        $categoriesResponse = Category::find()->where(['id' => $categoriesIds])->all();
        if (Yii::$app->request->getQueryParam('category')) {

            $ex = explode(',', Yii::$app->request->getQueryParam('category'));
            $category = Category::findOne(Yii::$app->request->getQueryParam('category'));


            $this->query->applyCategories($category);
            $this->currentUrl = Url::to(['/shop/catalog/new', 'category' => Yii::$app->request->getQueryParam('category')]);

            $this->view->params['breadcrumbs'][] = [
                'url' => ['new'],
                'label' => $this->pageName
            ];

            $this->pageName = $category->name;

        }
        $this->refreshUrl = $this->currentUrl;
        $this->view->params['breadcrumbs'][] = $this->pageName;
        $cacheKey = 'filter_catalog_new';
        if (Yii::$app->request->getQueryParam('category')) {
            $cacheKey .= Yii::$app->request->getQueryParam('category');
        }
//echo $this->query->createCommand()->rawSql;die;
        $this->filter = new FilterV2($this->query, ['cacheKey' => $cacheKey]);

        $this->filterQuery = clone $this->filter->resultQuery;
        $this->currentQuery = clone $this->query;
        //$this->query->applyAttributes($this->filter->activeAttributes);


        //if (Yii::$app->request->get('brand')) {
        //    $brands = explode(',', Yii::$app->request->get('brand', ''));
        //    $this->query->applyBrands($brands);
        //}
        // $this->query->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);

        if (Yii::$app->request->get('sort') == 'price' || Yii::$app->request->get('sort') == '-price') {
            $this->filterQuery->aggregatePriceSelect((Yii::$app->request->get('sort') == 'price') ? SORT_ASC : SORT_DESC);
        }
        $this->provider = new ActiveDataProvider([
            'query' => $this->filterQuery,
            'pagination' => [
                'pageSize' => $this->per_page,
            ],
        ]);

        return $this->_render('@shop/views/catalog/view', ['categories' => $categoriesResponse, 'categoriesIds' => $categoriesIds]);
    }

    public function actionSales()
    {
        /** @var Product $dataModel */
        /** @var Product $dataModel */
        $this->dataModel = Yii::$app->getModule('shop')->model('Product');


        // $this->query = $this->dataModel::find()->published()->isNotEmpty('discount');

        $this->query = Product::find()->published()->sales();


        $brands = [];
        $categories = [];
        $discounts = (Yii::$app->hasModule('discounts')) ? Yii::$app->getModule('discounts')->discounts : false;
        if ($discounts) {
            $categoriesList = [];
            $brandsList = [];
            foreach ($discounts as $discount) {
                /** @var \panix\mod\discounts\models\Discount $discount */
                $categoriesList[] = $discount->categories;
                $brandsList[] = $discount->brands;
            }

            foreach ($categoriesList as $category) {
                foreach ($category as $item) {
                    $categories[] = $item;
                }
            }

            foreach ($brandsList as $brand) {
                foreach ($brand as $item2) {
                    $brands[] = $item2;
                }
            }

        }


        if ($brands || Yii::$app->request->get('brand')) {
            if (!$brands)
                $brands = explode(',', Yii::$app->request->get('brand', ''));
            $this->query->applyBrands(array_unique($brands), 'orWhere');
        }
        if ($categories) {
            $this->query->applyCategories(array_unique($categories), 'orWhere');
        }

        $this->currentUrl = Url::to(['/shop/catalog/sales']);

        $this->pageName = Yii::t('shop/default', 'DISCOUNT');
        $categoriesIds = [];
        $categoriesQuery = clone $this->query;
        $categoriesResult = $categoriesQuery->groupBy('main_category_id')->select(['main_category_id'])->asArray()->all();
        foreach ($categoriesResult as $c) {
            $categoriesIds[] = $c['main_category_id'];
        }

        $categoriesResponse = Category::find()->where(['id' => $categoriesIds])->all();
        if (Yii::$app->request->getQueryParam('category')) {

            $ex = explode(',', Yii::$app->request->getQueryParam('category'));
            $category = Category::findOne(Yii::$app->request->getQueryParam('category'));//$this->findModel(Yii::$app->request->getQueryParam('slug'));


            $this->query->applyCategories($category);
            $this->currentUrl = Url::to(['/shop/catalog/sales', 'category' => Yii::$app->request->getQueryParam('category')]);

            $this->view->params['breadcrumbs'][] = [
                'url' => ['sales'],
                'label' => $this->pageName
            ];

            $this->pageName = $category->name;

        }
        $this->refreshUrl = $this->currentUrl;

        $this->view->params['breadcrumbs'][] = $this->pageName;


        $this->view->canonical = Url::to($this->currentUrl, true);
        $this->view->registerJs("var current_url = '" . $this->currentUrl . "';", yii\web\View::POS_HEAD, 'current_url');

        $cacheKey = 'filter_catalog_sales';
        if (Yii::$app->request->getQueryParam('category')) {
            $cacheKey .= Yii::$app->request->getQueryParam('category');
        }
        $this->filter = new FilterV2($this->query, ['cacheKey' => $cacheKey]);


        $this->filterQuery = clone $this->filter->resultQuery;
        $this->currentQuery = clone $this->query;


        // $this->query->applyAttributes($this->filter->activeAttributes);
        // $this->query->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);

        if (Yii::$app->request->get('sort') == 'price' || Yii::$app->request->get('sort') == '-price') {
            $this->filterQuery->aggregatePriceSelect((Yii::$app->request->get('sort') == 'price') ? SORT_ASC : SORT_DESC);
        }


        $this->provider = new ActiveDataProvider([
            'query' => $this->filterQuery,
            'pagination' => [
                'pageSize' => $this->per_page,
            ],
        ]);


        // 'criteria' => array(
        //     'condition' => 'is_sale = 1 OR is_discount=1 && switch=1',
        // ),


        return $this->_render('@shop/views/catalog/view', [
            'categories' => $categoriesResponse,
            'categoriesIds' => $categoriesIds
        ]);

    }

    protected function findModel($slug)
    {
        if (($this->dataModel = Category::findOne(['full_path' => $slug])) !== null) {
            return $this->dataModel;
        } else {
            $this->error404(Yii::t('shop/default', 'NOT_FOUND_CATEGORY'));
        }
    }

    public function actionAjaxFilterPrices()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [];
    }

}
