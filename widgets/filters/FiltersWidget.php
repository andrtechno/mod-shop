<?php

namespace panix\mod\shop\widgets\filters;

use yii\helpers\Html;
use Yii;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Manufacturer;

class FiltersWidget extends \panix\engine\data\Widget
{

    /**
     * @var array of Attribute models
     */
    public $attributes;
    //public $countAttr = true;
    //public $countManufacturer = true;
    //public $prices = [];
    public $tagCount = 'sup';
    public $tagCountOptions = ['class' => 'filter-count'];
    //public $showEmpty = false;
    //public $_manufacturer = [];

    /**
     * @var Category
     */
    public $model;

    /**
     * @var string min price in the query
     */
    // private $_currentMinPrice, $_currentMaxPrice = null;
    public $_maxprice, $_minprice;

    public function init()
    {
        $view = $this->getView();
        $this->_maxprice = $view->context->maxprice;
        $this->_minprice = $view->context->minprice;
    }

    /*public function getMinPrice()
    {
        return Yii::$app->controller->getMinPrice();
    }

    public function getMaxPrice()
    {
        return Yii::$app->controller->getMaxPrice();
    }*/

    /**
     * @return array of attributes used in category
     */
    public function getCategoryAttributes()
    {
        $data = [];

        foreach ($this->attributes as $attribute) {
            $data[$attribute->name] = array(
                'title' => $attribute->title,
                'selectMany' => (boolean)$attribute->select_many,
                'filters' => array()
            );
            foreach ($attribute->options as $option) {
                $count = $this->countAttributeProducts($attribute, $option);
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

    public function countAttributeProducts2($attribute, $option)
    {
        $model = Product::find();
        //$model->attachBehaviors($model->behaviors());
        $model->published();
        //$model->applyCategories($this->model);
        $model->andWhere([Product::tableName() . '.main_category_id' => $this->model->id]);
        if (Yii::$app->request->get('min_price'))
            $model->applyMinPrice($this->convertCurrency(Yii::$app->request->getQueryParam('min_price')));

        if (Yii::$app->request->get('max_price'))
            $model->applyMaxPrice($this->convertCurrency(Yii::$app->request->getQueryParam('max_price')));

        if (Yii::$app->request->get('manufacturer'))
            $model->applyManufacturers(explode(',', Yii::$app->request->get('manufacturer')));

        //$data = array($attribute->name => $option->id);
        $current = $this->view->context->activeAttributes;

        $newData = [];

        foreach ($current as $key => $row) {
            if (!isset($newData[$key]))
                $newData[$key] = array();
            if (is_array($row)) {
                foreach ($row as $v)
                    $newData[$key][] = $v;
            } else
                $newData[$key][] = $row;
        }
        $newData[$attribute->name][] = $option->id;

        //echo $q->createCommand()->getRawSql();die;
        return $model->withEavAttributes($newData)->count();

    }

    public function countAttributeProducts($attribute, $option)
    {
        $model = Product::find();
        //$model->attachBehaviors($model->behaviors());
        $model->published();
        // $model->applyCategories($this->model);
        $model->andWhere([Product::tableName() . '.main_category_id' => $this->model->id]);
        $newData = [];
        $newData[$attribute->name][] = $option->id;


        $count = Product::getDb()->cache(function ($db) use ($model, $newData) {
            return $model->withEavAttributes($newData)->count();
        }, 3600);

        return $count;
    }

    public function run()
    {
        $manufacturers = $this->getCategoryManufacturers();


        $active = $this->view->context->getActiveFilters();

        if (!empty($active)) {
            echo $this->render('current', ['active' => $active]);
        }
        echo $this->render('price');
        echo $this->render('attributes', ['attributes' => $this->getCategoryAttributes()]);
        echo $this->render('manufacturer', ['manufacturers' => $manufacturers]);

        $this->view->registerJs("
            $(function () {
                var selector = $('.card .card-collapse');
                selector.collapse({
                    toggle: false
                });
                var panels = $.cookie();
            
                for (var panel in panels) {
                    //console.log(panel);
                    if (panel) {
                        var panelSelector = $('#' + panel);
                        if (panelSelector) {
                            if (panelSelector.hasClass('card-collapse')) {
                                if ($.cookie(panel) === '1') {
                                    panelSelector.collapse('show');
                                } else {
                                    panelSelector.collapse('hide');
                                }
                            }
                        }
                    }
                }
            
                selector.on('show.bs.collapse', function () {
                    var active = $(this).attr('id');
                    $.cookie(active, '1');
            
                });
            
                selector.on('hide.bs.collapse', function () {
                    var active = $(this).attr('id');
                    $.cookie(active, null);
                });
            });
        ");


    }


    public function getCategoryManufacturers()
    {

        //@todo: Fix manufacturer translation

        $query = Product::find();
        $query->published();
        //$query->applyCategories($dataModel);
        $query->andWhere([Product::tableName() . '.main_category_id' => $this->model->id]);
        $queryClone = clone $query;
        $queryMan = $queryClone->addSelect(['manufacturer_id', Product::tableName() . '.id']);
        $queryMan->joinWith([
            'manufacturer' => function (\yii\db\ActiveQuery $query) {
                $query->andWhere([Manufacturer::tableName() . '.switch' => 1]);
            },
        ]);
        //$queryMan->->applyMaxPrice($this->convertCurrency(Yii::$app->request->getQueryParam('max_price')))
        //$queryMan->->applyMinPrice($this->convertCurrency(Yii::$app->request->getQueryParam('min_price')))
        $queryMan->andWhere('manufacturer_id IS NOT NULL');
        $queryMan->groupBy('manufacturer_id');


        // $manufacturers = $queryMan->all();
        $db = Yii::$app->db;
        $manufacturers = $db->cache(function ($db) use ($queryMan) {
            return $queryMan->all();
        }, 3600);
        //$manufacturers =$queryMan->all();
        //echo $q->createCommand()->rawSql;die;
        $data = array(
            'title' => Yii::t('shop/default', 'FILTER_BY_MANUFACTURER'),
            'selectMany' => true,
            'filters' => array()
        );

        if ($manufacturers) {

            foreach ($manufacturers as $m) {

                $m = $m->manufacturer;
                if ($m) {
                    $q = clone $queryClone;
                    //$q->applyMinPrice($this->convertCurrency(Yii::app()->request->getQuery('min_price')))
                    //$q->applyMaxPrice($this->convertCurrency(Yii::app()->request->getQuery('max_price')))
                    $q->applyManufacturers($m->id);

                    $count = Product::getDb()->cache(function ($db) use ($q) {
                        return $q->count();
                    }, 3600);

                    $data['filters'][] = array(
                        'title' => $m->name,
                        'count' => $count,
                        'queryKey' => 'manufacturer',
                        'queryParam' => $m->id,
                    );
                    //$this->_manufacturer[$m->id] = array(
                    //    'label' => $m->name,
                    //);
                } else {
                    die('err');
                }
            }
        }

        return $data;
    }

    /*
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
    */
    public function convertCurrency($sum)
    {
        $cm = Yii::$app->currency;
        if ($cm->active->id != $cm->main->id)
            return $cm->activeToMain($sum);
        return $sum;
    }

    public function getCount($filter)
    {
        $result = ($filter['count'] > 0) ? $filter['count'] : 0;
        return Html::tag($this->tagCount, $result, $this->tagCountOptions);
    }

}
