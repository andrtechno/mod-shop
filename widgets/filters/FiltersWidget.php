<?php

namespace panix\mod\shop\widgets\filters;

use panix\engine\CMS;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\traits\EavQueryTrait;
use yii\caching\DbDependency;
use yii\db\ActiveQuery;
use yii\helpers\Html;
use Yii;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Brand;
use panix\engine\data\Widget;

/**
 * Class FiltersWidget
 * @package panix\mod\shop\widgets\filtersnew2
 */
class FiltersWidget extends Widget
{
    public $data;
    /**
     * @var array of Attribute models
     */
    public $attributes;
    //public $countAttr = true;
    //public $countBrand = true;
    //public $prices = [];
    public $count = false;
    public $tagCount = 'sup';
    public $tagCountOptions = ['class' => 'filter-count'];
    //public $showEmpty = false;
    public $searchItem = 20;
    public $skin = 'default';
    /**
     * @var \panix\mod\shop\models\query\CategoryQuery
     */
    public $model;
    public $priceView = 'price';
    public $brandView = 'brand';
    public $attributeView = 'attributes';
    public $query;
    public $cacheDuration = 86400;


    /**
     * @var string min/max price in the query
     */
    protected $_currentPriceMin, $_currentPriceMax = null;
    public $priceMin, $priceMax;
    protected $prices = [];

    public function init()
    {
        $view = $this->getView();

        $this->priceMax = ceil($this->data->max);
        $this->priceMin = floor($this->data->min);
//CMS::dump($this->data->getResultMaxPrice());die;
        if (Yii::$app->request->get('price')) {
            $this->prices = explode('-', Yii::$app->request->get('price'));
        }

        FilterAsset::register($view);
    }


    /**
     * @return array of attributes used in category
     */


    public function run()
    {
        //  $brands = $this->data->getCategoryBrands();


        $active = $this->data->getActiveFilters();


        //echo Html::beginTag('div', ['id' => 'filters']);
        //  echo Html::beginForm($this->view->context->currentUrl, 'GET', ['id' => 'filter-form']);

        /*echo Html::beginTag('div', ['id' => 'ajax_filter_current']);
        if (!empty($active)) {
            $url = ($this->model) ? $this->model->getUrl() : ['/' . Yii::$app->requestedRoute];
            echo $this->render(Yii::$app->getModule('shop')->filterViewCurrent, ['active' => $active, 'dataModel' => $this->model, 'url' => $url]);
        }
        echo Html::endTag('div');*/
        /*if($this->priceView)
            echo $this->render($this->priceView, [
                'priceMin' => $this->priceMin,
                'priceMax' => $this->priceMax,
                'currentPriceMin'=>$this->data->getCurrentMinPrice(),
                'currentPriceMax'=>$this->data->getCurrentMaxPrice(),
            ]);
        if($this->attributeView)
            echo $this->render($this->attributeView, ['attributes' => $this->data->getCategoryAttributes()]);
        if($this->brandView)
            echo $this->render($this->brandView, ['brands' => $brands]);

        echo Html::submitButton('Применить',['class'=>'btn btn-block btn-primary']);
        echo Html::submitButton('Стросить',['class'=>'btn btn-block btn-secondary']);*/

        //   echo Html::endForm();
//echo $this->data->getCurrentMinPrice();die;
        // echo Html::endTag('div');
        //   print_r($this->model);die;
        $attributes = $this->data->getRootCategoryAttributes();
        echo $this->render($this->skin, [
            'model' => $this->model,
            'currentUrl' => $this->view->context->currentUrl,
            //'refreshUrl' => (($this->model) ? $this->model->getUrl() : ['/' . Yii::$app->requestedRoute]),
            'refreshUrl' => $this->view->context->refreshUrl,
            'priceMin' => floor($this->priceMin),
            'priceMax' => ceil($this->priceMax),
            'currentPrice' => $this->prices,
            // 'currentPriceMin' => $this->data->getCurrentMinPrice(),
            //  'currentPriceMax' => $this->data->getCurrentMaxPrice(),
            'active' => $active,
            'attributes' => ($attributes) ? $attributes : [],
            'brands' => (Yii::$app->controller->route != 'shop/brand/view') ? $this->data->getCategoryBrands() : []
        ]);
        // var category_id = {$this->model->id};
        /*$this->view->registerJs("
       
            $(function () {
                var selector = $('.card .card-collapse');
                selector.collapse({
                    toggle: false
                });
                var panels = $.cookie();
            
                for (var panel in panels) {
                    if (panel) {
                        var panelSelector = $('#' + panel);
                        if (panelSelector) {
                            var header = panelSelector.parent().find('.card-header');
                            if (panelSelector.hasClass('card-collapse')) {
                                if ($.cookie(panel) === '1') {
                                    panelSelector.collapse('show');
                                    header.addClass('collapsed').attr('aria-expanded',true);
                                } else {
                                    panelSelector.collapse('hide');
                                    header.removeClass('collapsed').attr('aria-expanded',false);
                                }
                            }
                        }
                    }
                }
            
                selector.on('show.bs.collapse', function () {
                    var active = $(this).attr('id');
                    $(this).parent().find('.card-header').addClass('collapsed');

                    
                    $.cookie(active, '1');
            
                });
            
                selector.on('hide.bs.collapse', function () {
                    var active = $(this).attr('id');
                    $(this).parent().find('.card-header').removeClass('collapsed');

                    $.cookie(active, null);
                });
            });
        ");*/


    }


    public function _____getCategoryBrands()
    {

        $query = Product::find();

        if ($this->model) {
            $query->applyCategories($this->model);
            //$query->andWhere([Product::tableName() . '.main_category_id' => $this->model->id]);
        }

        if (Yii::$app->request->get('q') && Yii::$app->requestedRoute == 'shop/search/index') {
            $query->applySearch(Yii::$app->request->get('q'));
        }
        $query->published();
        $queryClone = clone $query;
        $queryMan = $queryClone->addSelect(['brand_id', Product::tableName() . '.id']);
        $queryMan->joinWith([
            'brand' => function (\yii\db\ActiveQuery $query) {
                $query->andWhere([Brand::tableName() . '.switch' => 1]);
            },
        ]);
        //$queryMan->->applyMaxPrice($this->convertCurrency(Yii::$app->request->getQueryParam('max_price')))
        //$queryMan->->applyMinPrice($this->convertCurrency(Yii::$app->request->getQueryParam('min_price')))

        $queryMan->andWhere('brand_id IS NOT NULL');
        $queryMan->groupBy('brand_id');


        // $brands = $queryMan->all();


        $brands = Brand::getDb()->cache(function ($db) use ($queryMan) {
            return $queryMan
                //->joinWith('translations as translate')
                //->orderBy(['translate.name'=>SORT_ASC])
                ->all();
        }, $this->cacheDuration);


        //$brands =$queryMan->all();
        //echo $q->createCommand()->rawSql;die;
        $data = [
            'title' => Yii::t('shop/default', 'FILTER_BY_BRAND'),
            'selectMany' => true,
            'filters' => []
        ];

        if ($brands) {

            foreach ($brands as $m) {

                $m = $m->brand;

                if ($m) {
                    $query = Product::find()->published();
                    if ($this->model) {
                        $query->applyCategories($this->model);
                        //$query->andWhere([Product::tableName() . '.main_category_id' => $this->model->id]);
                    }
                    $brandsList[] = $m->id;
                    $query->applyBrands($brandsList);
                    //$q->applyMinPrice($this->convertCurrency(Yii::app()->request->getQuery('min_price')))
                    //$q->applyMaxPrice($this->convertCurrency(Yii::app()->request->getQuery('max_price')))
                    //  $query->applyBrands($m->id);

                    if (Yii::$app->request->get('q') && Yii::$app->requestedRoute == 'shop/search/index') {
                        $query->applySearch(Yii::$app->request->get('q'));
                    }


                    $query->applyRangePrices((isset($this->prices[0])) ? $this->prices[0] : 0, (isset($this->prices[1])) ? $this->prices[1] : 0);
                    if (Yii::$app->request->get('brand')) {
                        $brandsList = explode(',', Yii::$app->request->get('brand', ''));
                        //$query->applyBrands($brands);
                    }
                    // print_r($brandsList);die;

                    /*$dependencyQuery = $query;
                    $dependencyQuery->select('COUNT(*)');
                    $dependency = new DbDependency([
                        'sql' => $dependencyQuery->createCommand()->rawSql,
                    ]);

                    $count = Product::getDb()->cache(function () use ($query) {
                        return $query->count();
                    }, 3600 * 24, $dependency);*/


                    $query->orderBy = false;
                    //echo $query->createCommand()->rawSql;die;
                    $count = $query->count();

                    $data['filters'][] = [
                        'title' => $m->name,
                        'count' => (int)$count,
                        'count_text' => $count,
                        'key' => 'brand',
                        'queryParam' => $m->id,
                    ];
                    sort($data['filters']);
                } else {
                    die('err brand');
                }
            }
        }

        return $data;
    }

    public function convertCurrency($sum)
    {
        $cm = Yii::$app->currency;
        if ($cm->active->id != $cm->main->id)
            return $cm->activeToMain($sum);
        return $sum;
    }

    public function getCount($filter)
    {
        //$this->tagCountOptions=[];
        if (isset($filter['key'])) {
            $this->tagCountOptions['id'] = 'filter-count-' . $filter['key'] . '-' . $filter['queryParam'];
        }

        $result = ($filter['count'] > 0) ? $filter['count_text'] : 0;
        return '';//($this->count) ? ' ' . Html::tag($this->tagCount, $result, $this->tagCountOptions) : '';
    }

    public function generateGradientCss($data)
    {
        $css = '';
        if (isset($data['color'])) {
            //if(isset($data['color'][0]) && !empty($data['color'][0])){
            $css .= "background: {$data['color'][0]};";
            //}

            if (count($data['color']) > 1) {

                $res_data = [];
                foreach ($data['color'] as $k => $color) {
                    $res_data[] = $color;
                }
                $res = implode(', ', $res_data);

                if (count($data['color']) == 2) {
                    $value = "45deg, {$data['color'][0]} 50%, {$data['color'][1]} 50%";
                    $css .= "background: -moz-linear-gradient({$value});";
                    $css .= "background: -webkit-linear-gradient({$value});";
                    $css .= "background: linear-gradient({$value});";
                } elseif (count($data['color']) == 3) {
                    $value = "45deg, {$data['color'][0]} 0%, {$data['color'][0]} 33%, {$data['color'][1]} 33%, {$data['color'][1]} 66%, {$data['color'][2]} 66%, {$data['color'][2]} 100%";
                    $css .= "background: -moz-linear-gradient({$value});";
                    $css .= "background: -webkit-linear-gradient({$value});";
                    $css .= "background: linear-gradient({$value});";
                } elseif (count($data['color']) == 4) {
                    $value = "45deg, {$data['color'][0]} 0%, {$data['color'][0]} 25%, {$data['color'][1]} 25%, {$data['color'][1]} 50%, {$data['color'][2]} 50%, {$data['color'][2]} 75%, {$data['color'][3]} 75%, {$data['color'][3]} 100%";
                    $css .= "background: -moz-linear-gradient({$value});";
                    $css .= "background: -webkit-linear-gradient({$value});";
                    $css .= "background: linear-gradient({$value});";
                } elseif (count($data['color']) >= 4) {
                    $css .= "background: -moz-radial-gradient(farthest-corner at 0% 100%, {$res});";
                    $css .= "background: -webkit-radial-gradient(farthest-corner at 0% 100%, {$res});";
                    $css .= "background: radial-gradient(farthest-corner at 0% 100%, {$res});";
                }
                $css .= "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='{$data['color'][0]}', endColorstr='{$data['color'][1]}',GradientType=1 );";
            }
        }
        return $css;
    }


    /**
     * @return mixed
     */
    public function getCurrentMinPrice()
    {
        if ($this->_currentPriceMin !== null)
            return $this->_currentPriceMin;

        $this->_currentPriceMin = (isset($this->prices[0])) ? $this->prices[0] : Yii::$app->currency->convert($this->priceMin);

        return $this->_currentPriceMin;
    }

    /**
     * @return mixed
     */
    public function getCurrentMaxPrice()
    {
        if ($this->_currentPriceMax !== null)
            return $this->_currentPriceMax;

        $this->_currentPriceMax = (isset($this->prices[1])) ? $this->prices[1] : Yii::$app->currency->convert($this->priceMax);

        return $this->_currentPriceMax;
    }
}
