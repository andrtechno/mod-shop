<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;
use yii\web\NotFoundHttpException;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Attribute;
use yii\helpers\Html;
use yii\helpers\Url;

class CategoryController extends WebController {

    public $allowedPageLimit = [];
    public $query, $provider, $currentQuery;
    private $_eavAttributes;

    /**
     * @var string
     */
    private $_maxPrice, $_minPrice;

    /**
     * @var string
     */
    public $maxprice, $minprice;

    public function getEavAttributes() {
        if (is_array($this->_eavAttributes))
            return $this->_eavAttributes;

        // Find category types

        $model = Product::find();
        $query = $model->applyCategories($this->dataModel)->published();

        unset($model);


        $query->addSelect(['type_id']);
        $query->addGroupBy(['type_id']);
        $query->distinct(true);

        $typesIds = $query->createCommand()->queryColumn();



        // Find attributes by type

        $query = Attribute::find(['IN', '`types`.type_id', $typesIds])
                ->useInFilter()
                ->orderBy(['ordern' => SORT_DESC])
                ->joinWith(['types', 'options'])
                ->all();




        $this->_eavAttributes = array();
        foreach ($query as $attr)
            $this->_eavAttributes[$attr->name] = $attr;
        return $this->_eavAttributes;
    }

    public function getActiveAttributes() {
        $data = array();

        foreach (array_keys($_GET) as $key) {
            if (array_key_exists($key, $this->eavAttributes)) {
                if ((boolean) $this->eavAttributes[$key]->select_many === true) {
                    $data[$key] = explode(',', $_GET[$key]);
                } else {
                    $data[$key] = array($_GET[$key]);
                }
            }
        }
        return $data;
    }

    public function beforeAction($action) {

        $this->allowedPageLimit = explode(',', Yii::$app->settings->get('shop', 'per_page'));

        if (Yii::$app->request->post('min_price') || Yii::$app->request->post('max_price')) {
            $data = [];
            if (Yii::$app->request->post('min_price'))
                $data['min_price'] = (int) Yii::$app->request->post('min_price');
            if (Yii::$app->request->post('max_price'))
                $data['max_price'] = (int) Yii::$app->request->post('max_price');

            if ($this->action->id === 'search') {
                return $this->redirect(Yii::$app->urlManager->addUrlParam('/shop/category/search', $data))->send();
            } else {
                return $this->redirect(Yii::$app->urlManager->addUrlParam('/shop/category/view', $data))->send();
            }
        }
        return parent::beforeAction($action);
    }

    public function actionView() {
        $this->dataModel = $this->findModel(Yii::$app->request->getQueryParam('seo_alias'));
        // $this->canonical = Yii::$app->urlManager->createAbsoluteUrl($this->dataModel->getUrl());
        $this->doSearch($this->dataModel, 'view');
    }

    /**
     * Search products
     */
    public function actionSearch() {

        if (Yii::$app->request->isPost) {
            return $this->redirect(Yii::$app->urlManager->addUrlParam('/shop/category/search', ['q' => Yii::$app->request->post('q')]))->send();
        }
        $q = Yii::$app->request->get('q');
        if (empty($q)) {
            $q = '+';
        }


        if (Yii::$app->request->isAjax && Yii::$app->request->get('q')) {
            $res = [];
            $model = Product::find();
            $model->joinWith(['manufacturer', 'translations']); //manufacturerActive
            $model->andWhere(['LIKE', '{{%shop_product}}.sku', Yii::$app->request->get('q')]);
            $model->orWhere(['LIKE', '{{%shop_product_translate}}.name', Yii::$app->request->get('q')]);
            //'fullurl'=>Html::a('FULL',Yii::$app->urlManager->createUrl(['/shop/category/search', 'q' => Yii::$app->request->post('q')])),
            foreach ($model->all() as $m) {
                $res[] = [
                    'url' => Url::to($m->getUrl()),
                    'renderItem' => $this->renderPartial('@shop/widgets/search/views/_item', [
                        'name' => $m->name,
                        'price' => $m->getDisplayPrice(),
                        'url' => $m->getUrl(),
                        'image' => $m->getMainImageUrl('50x50'),
                    ])
                ];
            }
            echo \yii\helpers\Json::encode($res);
            die;
        }
        if (!$q) {
            return $this->render('search');
        } else {
            $this->doSearch($q, 'search');
        }
    }

    public function doSearch($data, $view) {
        $this->query = Product::find();

        $this->query->attachBehaviors($this->query->behaviors());
        $this->query->applyAttributes($this->activeAttributes)->published();






        if ($data instanceof \panix\mod\shop\models\Category) {
            //  $cr->with = array('manufacturerActive');
            // Скрывать товары если производитель скрыт.
            //TODO: если у товара не выбран производитель то он тоже скрывается!! need fix
            //$this->query->with(array('manufacturer' => array(
            //        'scopes' => array('published')
            //)));


            $this->query->applyCategories($this->dataModel);
            //  $this->query->with('manufacturerActive');
        } else {
            $this->query->joinWith(['manufacturer', 'translations']); //manufacturerActive
            $this->query->andWhere(['LIKE', '{{%shop_product}}.sku', $data]);
            $this->query->orWhere(['LIKE', '{{%shop_product_translate}}.name', $data]);

            //echo $this->query->createCommand()->getRawSql();
        }

        // Filter by manufacturer
        if (Yii::$app->request->get('manufacturer')) {
            $manufacturers = explode(',', Yii::$app->request->get('manufacturer', ''));
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

        $this->provider->sort = Product::getSort();
        if ($view != 'search') {

            $c = Yii::$app->settings->get('shop');

            if ($c['seo_categories']) {
                $this->keywords = $this->dataModel->keywords();
                $this->description = $this->dataModel->description();
                $this->title = $this->dataModel->title();
            }
            $ancestors = $this->dataModel->ancestors()->addOrderBy('depth')->excludeRoot()->all();

            $this->breadcrumbs[] = [
                'label' => Yii::t('shop/default', 'CATALOG'),
                'url' => array('/shop')
            ];
            foreach ($ancestors as $c) {
                $this->breadcrumbs[] = [
                    'label' => $c->name,
                    'url' => $c->getUrl()
                ];
            }
            $this->breadcrumbs[] = $this->dataModel->name;
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
            if (in_array($_GET['view'], ['list', 'table', 'grid'])) {
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
        $model = new Category;
        if (($this->dataModel = $model::find()
                ->where(['full_path' => $seo_alias])
                ->one()) !== null) {
            return $this->dataModel;
        } else {
            throw new NotFoundHttpException('product not found');
        }
    }

}
