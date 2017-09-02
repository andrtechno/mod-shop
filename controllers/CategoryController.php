<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\ShopProduct;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;
class CategoryController extends WebController {

    public $model;
    public $allowedPageLimit = [];
    public $query;
    public $provider;
    public $currentQuery;

    /**
     * @var string
     */
    private $_minPrice;

    /**
     * @var string
     */
    private $_maxPrice;
    public $maxprice, $minprice;

    public function beforeAction($action) {

        $this->allowedPageLimit = explode(',', Yii::$app->settings->get('shop', 'per_page'));

        if (Yii::$app->request->post('min_price') || Yii::$app->request->post('max_price')) {
            $data = [];
            if (Yii::$app->request->post('min_price'))
                $data['min_price'] = (int) Yii::$app->request->post('min_price');
            if (Yii::$app->request->post('max_price'))
                $data['max_price'] = (int) Yii::$app->request->post('max_price');

            if ($this->action->id === 'search') {
                return $this->redirect(Yii::$app->request->addUrlParam('/shop/category/search', $data));
            } else {
                if (!Yii::$app->request->isAjax){
                    return $this->redirect(Url::toRoute(Yii::$app->request->addUrlParam('/shop/category/view', $data)));
                }
            }
        }
        return parent::beforeAction($action);
    }

    public function actionView() {
        $this->model = $this->findModel(Yii::$app->request->getQueryParam('seo_alias'));
        // $this->canonical = Yii::$app->createAbsoluteUrl($this->model->getUrl());
        $this->doSearch($this->model, 'view');
    }

    public function doSearch($data, $view) {
        $this->query = ShopProduct::find();

        $this->query->attachBehaviors($this->query->behaviors());
        // $this->query->applyAttributes($this->activeAttributes)->published();






        if ($data instanceof \panix\mod\shop\models\ShopCategory) {
            //  $cr->with = array('manufacturerActive');
            // Скрывать товары если производитель скрыт.
            //TODO: если у товара не выбран производитель то он тоже скрывается!! need fix
            //$this->query->with(array('manufacturer' => array(
            //        'scopes' => array('published')
            //)));


            $this->query->applyCategories($this->model);
            //  $this->query->with('manufacturerActive');
        } else {
            /* $cr = new CDbCriteria;
              $cr->with = array(
              // 'manufacturerActive',
              'translate' => array('together' => true),
              );

              $cr->addSearchCondition('t.sku', $data);
              $cr->addSearchCondition('translate.name', $data, true, 'OR');

              $this->query->getDbCriteria()->mergeWith($cr); */
        }

        // Filter by manufacturer
        if (Yii::$app->request->get('manufacturer')) {
            $manufacturers = explode(',', Yii::$app->request->getParam('manufacturer', ''));
            $this->query->applyManufacturers($manufacturers);
        }

        // Create clone of the current query to use later to get min and max prices.
        $this->currentQuery = clone $this->query;
        // Filter products by price range if we have min_price or max_price in request
        $this->applyPricesFilter();

        $this->maxprice = (int) $this->currentQuery->max('price');
        $this->minprice = (int) $this->currentQuery->min('price');
        //$this->maxprice = $this->getMaxPrice();
        //$this->minprice = $this->getMinPrice();

        $per_page = $this->allowedPageLimit[0];
        if (isset($_GET['per_page']) && in_array((int) $_GET['per_page'], $this->allowedPageLimit))
            $per_page = (int) $_GET['per_page'];

        $this->provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $this->query,
            //  'id' => false,
            'pagination' => array(
                'pageSize' => $per_page,
            )
        ]);

        $this->provider->sort = ShopProduct::getCSort();
        if ($view != 'search') {

            $c = Yii::$app->settings->get('shop');


            $ancestors = $this->model->leaves()->all();
            $this->breadcrumbs[] = [
                'label' => Yii::t('shop/default', 'BC_SHOP'),
                'url' => array('/shop')
            ];
            //foreach ($ancestors as $c)
            //     $this->breadcrumbs[$c->name] = $c->getUrl();
            // $this->breadcrumbs[] = $this->model->name;
        }
        $itemView = '_view_grid';
        /* if (isset($_GET['view'])) {
          if ($_GET['view'] == 'list') {
          $itemView = '_view_list';
          } elseif ($_GET['view'] == 'table') {
          $itemView = '_view_table';
          } else {
          $itemView = '_view_grid';
          }
          } */

        if (isset($_GET['view'])) {
            if (in_array($_GET['view'], array('list', 'table', 'grid'))) {
                $itemView = '_view_' . $_GET['view'];
            } else {
                $itemView = '_view_grid';
            }
        }

        echo $this->render($view, array(
            'provider' => $this->provider,
            'itemView' => $itemView
        ));
    }

    public function applyPricesFilter() {
        $minPrice = Yii::$app->request->get('min_price');
        $maxPrice = Yii::$app->request->get('max_price');

        /* $cm = Yii::$app->currency;
          if ($cm->active->id !== $cm->main->id && ($minPrice > 0 || $maxPrice > 0)) {
          $minPrice = $cm->activeToMain($minPrice);
          $maxPrice = $cm->activeToMain($maxPrice);
          } */

        if ($minPrice > 0)
            $this->query->applyMinPrice($minPrice);
        if ($maxPrice > 0)
            $this->query->applyMaxPrice($maxPrice);
    }

    protected function findModel($seo_alias) {
        $model = Yii::$app->getModule("shop")->model("ShopCategory");
        if (($this->model = $model::find()
                ->where(['full_path' => $seo_alias])
                ->one()) !== null) {
            return $this->model;
        } else {
            throw new NotFoundHttpException('product not found');
        }
    }

    /**
     * 
     * 
     * 
     * 
     * 
     * by FILTER function
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     */

    /**
     * @var string min price in the query
     */
    private $_currentMinPrice = null;

    /**
     * @var string max price in the query
     */
    private $_currentMaxPrice = null;

    public function getCurrentMaxPrice() {
        if ($this->_currentMaxPrice !== null)
            return $this->_currentMaxPrice;

        if (Yii::$app->request->get('max_price')) {
            $this->_currentMaxPrice = Yii::$app->request->get('max_price');
        } else {
            $this->_currentMaxPrice = Yii::$app->currency->convert($this->maxprice);
            //$this->_currentMaxPrice = $this->maxprice;
        }

        return $this->_currentMaxPrice;
    }

    public function getCurrentMinPrice() {
        if ($this->_currentMinPrice !== null)
            return $this->_currentMinPrice;

        if (Yii::$app->request->get('min_price')) {
            $this->_currentMinPrice = Yii::$app->request->get('min_price');
        } else {
          //  $this->_currentMinPrice = $this->minprice;
            $this->_currentMinPrice = Yii::$app->currency->convert($this->minprice);
        }

        return $this->_currentMinPrice;
    }

}
