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
 * @package panix\mod\shop\widgets\filters
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

        if (Yii::$app->request->get('price')) {
            $this->prices = explode('-', Yii::$app->request->get('price'));
        }

        FilterAsset::register($view);
    }


    /**
     * @return array of attributes used in category
     */

    public $brands = [];
    public $context;
    public function run()
    {

        $active = $this->data->getActiveFilters();
        if(Yii::$app->controller->route != 'shop/brand/view'){
            $this->brands = $this->data->categoryBrands;
        }


        $attributes = $this->data->getAttributes();
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
            'brands' => $this->brands
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
    public function convertCurrency($sum)
    {
        $cm = Yii::$app->currency;
        if ($cm->active->id != $cm->main->id)
            return $cm->activeToMain($sum);
        return $sum;
    }

    public function getCount($key=false,$filter)
    {
        //$this->tagCountOptions=[];
        if ($key) {
            $this->tagCountOptions['id'] = 'filter-count-' . $key . '-' . $filter['id'];
        }
        $result = ($filter['count'] > 0) ? $filter['count'] : 0;
        if (Yii::$app->getModule('shop')->filterClass == 'panix\mod\shop\components\FilterElastic') {
            return ($this->count) ? ' ' . Html::tag($this->tagCount, $result, $this->tagCountOptions) : '';
        } else {
            return '';
        }


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
