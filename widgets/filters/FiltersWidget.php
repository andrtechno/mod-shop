<?php

namespace panix\mod\shop\widgets\filters;

use yii\helpers\Html;
use Yii;
use panix\mod\shop\models\ShopProduct;
use panix\mod\shop\models\ShopManufacturer;

class FiltersWidget extends \panix\engine\data\Widget {

    /**
     * @var array of ShopAttribute models
     */
    public $attributes;
    //public $countAttr = true;
    //public $countManufacturer = true;
    //public $prices = [];
    public $tagCount = 'sup';
    public $tagCountOptions = ['class'=>'filter-count'];
    //public $showEmpty = false;
    public $_manufacturer = [];

    /**
     * @var ShopCategory
     */
    public $model;

    /**
     * @var string min price in the query
     */
    private $_currentMinPrice, $_currentMaxPrice = null;
    public $_maxprice, $_minprice;

    public function init() {
        $view = $this->getView();
        $this->_maxprice = $view->context->maxprice;
        $this->_minprice = $view->context->minprice;
    }

    /**
     * @return array of attributes used in category
     */
    public function getCategoryAttributes() {
        $data = array();

        foreach ($this->attributes as $attribute) {
            $data[$attribute->name] = array(
                'title' => $attribute->title,
                'selectMany' => (boolean) $attribute->select_many,
                'filters' => array()
            );
            foreach ($attribute->options as $option) {
                $count = $this->countAttributeProducts2($attribute, $option);
                if ($count) {
                    $data[$attribute->name]['filters'][] = array(
                        'title' => $option->value,
                        'count' => $count,
                        'queryKey' => $attribute->name,
                        'queryParam' => $option->id,
                    );
                }
            }
        }
        return $data;
    }
    public function countAttributeProducts2($attribute, $option) {


           // $dependency = new CDbCacheDependency('SELECT MAX(date_update) FROM {{shop_product}}');

            $model = ShopProduct::find();
            $model->attachBehaviors($model->behaviors());
            $model->published();
            $model->applyCategories($this->model);
            $model->applyMinPrice($this->convertCurrency(Yii::$app->request->getQueryParam('min_price')));
            $model->applyMaxPrice($this->convertCurrency(Yii::$app->request->getQueryParam('max_price')));

            if (Yii::$app->request->get('manufacturer'))
                $model->applyManufacturers(explode(',', Yii::$app->request->get('manufacturer')));

            //$data = array($attribute->name => $option->id);
            $current = $this->view->context->activeAttributes;

            $newData = array();

            foreach ($current as $key => $row) {
                if (!isset($newData[$key]))
                    $newData[$key] = array();
                if (is_array($row)) {
                    foreach ($row as $v)
                        $newData[$key][] = $v;
                } else
                    $newData[$key][] = $row;
            }
            //$model->cache($this->cache_time,$dependency);
            $newData[$attribute->name][] = $option->id;

            return $model->withEavAttributes($newData)->count();

    }
    public function countAttributeProducts($attribute, $option) {


        $model = ShopProduct::find();
        $model->attachBehaviors($model->behaviors());
        $model->published();
        $model->applyCategories($this->model);
        // $model->applyMinPrice($this->convertCurrency(Yii::app()->request->getQuery('min_price')));
        // $model->applyMaxPrice($this->convertCurrency(Yii::app()->request->getQuery('max_price')));
        //if (Yii::app()->request->getParam('manufacturer'))
        //   $model->applyManufacturers(explode(',', Yii::app()->request->getParam('manufacturer')));
        $newData = array();
        $newData[$attribute->name][] = $option->id;
        return $model->withEavAttributes($newData)->count();
    }

    public function run() {
        $manufacturers = $this->getCategoryManufacturers();
        $active = $this->getActiveFilters();

        if (!empty($active)) {
            echo $this->render('current', ['active' => $active]);
        }
        echo $this->render('attributes', ['attributes' => $this->getCategoryAttributes()]);
        echo $this->render('manufacturer', ['manufacturers' => $manufacturers]);
        echo $this->render('price');
    }

    /**
     * Get active/applied filters to make easier to cancel them.
     */
    public function getActiveFilters() {
        $request = Yii::$app->request;
        // Render links to cancel applied filters like prices, manufacturers, attributes.
        $menuItems = array();
        $manufacturersIds = array_filter(explode(',', $request->getQueryParam('manufacturer')));


        if ($request->getQueryParam('min_price')) {
            array_push($menuItems, array(
                'linkOptions' => array('class' => 'remove'),
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['min' => (int) $this->getCurrentMinPrice(), 'currency' => Yii::$app->currency->active->symbol]),
                'url' => Yii::$app->urlManager->removeUrlParam('/shop/category/view', 'min_price')
            ));
        }

        if ($request->getQueryParam('max_price')) {
            array_push($menuItems, array(
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MAX', ['max' => (int) $this->getCurrentMaxPrice(), 'currency' => Yii::$app->currency->active->symbol]),
                'linkOptions' => array('class' => 'remove'),
                'url' => Yii::$app->urlManager->removeUrlParam('/shop/category/view', 'max_price')
            ));
        }



        foreach ($manufacturersIds as $id => $manufacturer) {
            array_push($menuItems, array(
                'label' => $this->_manufacturer[$manufacturer]['label'],
                'linkOptions' => array('class' => 'remove'),
                'url' => Yii::$app->urlManager->removeUrlParam('/shop/category/view', 'manufacturer', $id)
            ));
        }


        // Process eav attributes
        $activeAttributes = $this->view->context->activeAttributes;
        if (!empty($activeAttributes)) {
            foreach ($activeAttributes as $attributeName => $value) {
                if (isset($this->view->context->eavAttributes[$attributeName])) {
                    $attribute = $this->view->context->eavAttributes[$attributeName];
                    foreach ($attribute->options as $option) {
                        if (isset($activeAttributes[$attribute->name]) && in_array($option->id, $activeAttributes[$attribute->name])) {
                            array_push($menuItems, array(
                                'label' => $option->value,
                                'linkOptions' => array('class' => 'remove'),
                                'url' => Yii::$app->urlManager->removeUrlParam('/shop/category/view', $attribute->name, $option->id)
                            ));
                        }
                    }
                }
            }
        }

        return $menuItems;
    }

    public function getCategoryManufacturers() {

        //@todo: Fix manufacturer translation
        $dataModel = $this->model;
        $manufacturers = ShopProduct::find()

                /* 'with' => array(
                  'productsCount' => array(
                  'scopes' => array(
                  'published',
                  'applyCategories' => array($mdl, null),
                  'applyAttributes' => array($this->getOwner()->activeAttributes),
                  'applyMinPrice' => array($this->convertCurrency(Yii::app()->request->getQuery('min_price'))),
                  'applyMaxPrice' => array($this->convertCurrency(Yii::app()->request->getQuery('max_price'))),
                  ))
                  ), */
                ->published()
                ->applyCategories($dataModel, null)
                ->with(['manufacturer'])
                //->applyMaxPrice($this->convertCurrency(Yii::$app->request->getQueryParam('max_price')))
                //->applyMinPrice($this->convertCurrency(Yii::$app->request->getQueryParam('min_price')))
                ->addSelect(['manufacturer_id', '{{%shop_product}}.id'])
                ->groupBy('manufacturer_id')
                ->andWhere('manufacturer_id IS NOT NULL')
                ->all();

        $data = array(
            'title' => Yii::t('app', 'Производитель'),
            'selectMany' => true,
            'filters' => array()
        );

        if ($manufacturers) {

            foreach ($manufacturers as $m) {
                $m = $m->manufacturer;
                if ($m) {
                    $model = ShopProduct::find();
                    $model->attachBehaviors($model->behaviors());
                    $model->published();
                    $model->applyCategories($dataModel);
                    //$model->applyMinPrice($this->convertCurrency(Yii::app()->request->getQuery('min_price')))
                    //$model->applyMaxPrice($this->convertCurrency(Yii::app()->request->getQuery('max_price')))

                    $model->applyManufacturers($m->id);



                    $data['filters'][] = array(
                        'title' => $m->name,
                        'count' => $model->count(),
                        'queryKey' => 'manufacturer',
                        'queryParam' => $m->id,
                    );
                    $this->_manufacturer[$m->id] = array(
                        'label' => $m->name,
                    );
                } else {
                    die('err');
                }
            }
        }

        return $data;
    }

    public function getCurrentMaxPrice() {
        if ($this->_currentMaxPrice !== null)
            return $this->_currentMaxPrice;

        if (Yii::$app->request->get('max_price')) {
            $this->_currentMaxPrice = Yii::$app->currency->convert(Yii::$app->request->get('max_price'));
        } else {
            $this->_currentMaxPrice = Yii::$app->currency->convert($this->_maxprice);
        }

        return $this->_currentMaxPrice;
    }

    public function getCurrentMinPrice() {
        if ($this->_currentMinPrice !== null)
            return $this->_currentMinPrice;

        if (Yii::$app->request->get('min_price')) {
            $this->_currentMinPrice = Yii::$app->currency->convert(Yii::$app->request->get('min_price'));
        } else {
            $this->_currentMinPrice = Yii::$app->currency->convert($this->_minprice);
        }

        return $this->_currentMinPrice;
    }

    public function convertCurrency($sum) {
        $cm = Yii::$app->currency;
        if ($cm->active->id != $cm->main->id)
            return $cm->activeToMain($sum);
        return $sum;
    }

    public function getCount($filter) {
        $result = ($filter['count'] > 0) ? $filter['count'] : 0;
        return Html::tag($this->tagCount, $result, $this->tagCountOptions);
    }

}
