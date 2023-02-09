<?php

namespace panix\mod\shop\components;

use panix\engine\CMS;
use panix\mod\shop\models\Category;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Response;
use panix\engine\Html;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Product;
use panix\engine\controllers\WebController;

/**
 * Class FilterController
 *
 * @property array $activeAttributes
 * @property \panix\mod\shop\models\query\ProductQuery $query
 * @property \panix\mod\shop\models\query\ProductQuery $currentQuery
 * @property integer $per_page
 * @property Filter $filter
 *
 * @package panix\mod\shop\components
 */
class FilterController extends WebController
{

    public $filterQuery;
    /**
     * Sets page limits
     * @var array
     */
    public $allowedPageLimit;

    /**
     * @var \panix\mod\shop\models\query\ProductQuery
     */
    public $query;

    /**
     * @var \panix\mod\shop\models\query\ProductQuery
     */
    public $currentQuery;
    public $prices;
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

    public $currentUrl;
    public $refreshUrl;
    public $itemView = '_view_grid';
    public $per_page;
    public $filter;
    public $filterClass;

    public function __construct($id, $module, $config = [])
    {
        $this->filterClass = Yii::$app->getModule('shop')->filterClass;
        parent::__construct($id, $module, $config);
    }

    public function actionFilterCallback()
    {

        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $query = $productModel::find();
        $query->published();

        /*
                if ($category_id) {
                    $category = Category::findOne($category_id);
                    if (!$category)
                        $this->error404();
                    $query->applyCategories($category);
                }*/


        if (Yii::$app->request->post('filter')) {
            if (isset(Yii::$app->request->post('filter')['brand'])) {
                $query->applyBrands(Yii::$app->request->post('filter')['brand']);
            }
            //unset(Yii::$app->request->post('filter')['brand']);
            $query->getFindByEavAttributes2(Yii::$app->request->post('filter'));
        }


        $filter = new $this->filterClass($query, $category);
        //print_r($f->getPostActiveAttributes());die;
        $attributes = $filter->getCategoryAttributesCallback();
        $brands = $filter->getCategoryBrandsCallback();
        $total = 0;
        $results = ArrayHelper::merge($attributes, ['brand' => $brands]);
        /*$firstItem = array_key_first(Yii::$app->request->post('filter'));
        foreach ($results as $att) {
            if ($att['filters']) {
                foreach ($att['filters'] as $filterName => $filter) {
if($filterName == $firstItem){
                    $total += $filter['count'];
}
                }
            }
        }*/
        $total = $query->count();
//CMS::dump(ArrayHelper::merge($ss,['brand'=>$f->getCategoryBrands()]));die;
        return $this->asJson([
            'first' => array_key_first(Yii::$app->request->post('filter')),
            'textTotal' => "Показать " . Yii::t('shop/default', 'PRODUCTS_COUNTER', $total),
            'totalCount' => $total,
            'filters' => $results
        ]);
        // return $this->asJson(ArrayHelper::merge($ss, ['brand' => $f->getCategoryBrands()]));
    }

    public function beforeAction($action)
    {

        Url::remember();
        if (Yii::$app->request->get('view')) {
            if (in_array(Yii::$app->request->get('view'), Yii::$app->getModule('shop')->viewList)) {
                $this->itemView = '_view_' . Yii::$app->request->get('view');
            }
        }

        $this->allowedPageLimit = explode(',', Yii::$app->settings->get('shop', 'per_page'));

        $this->per_page = (int)$this->allowedPageLimit[0];
        if (Yii::$app->request->get('per-page') && in_array(Yii::$app->request->get('per-page'), $this->allowedPageLimit)) {
            $this->per_page = (int)Yii::$app->request->get('per-page');
        }

        if (Yii::$app->request->get('price')) {
            if (preg_match('/^[0-9\-]+$/', Yii::$app->request->get('price'))) {
                $this->prices = explode('-', Yii::$app->request->get('price'));
            } else {
                $this->error404();
            }

            //foreach ($this->prices as $key=>$price) {
            // $this->prices[]=$price;
            //}
        }
        //print_r(Yii::$app->request->get('price'));die;
        $this->view->registerJs("
        var penny = '" . Yii::$app->currency->active['penny'] . "';
        var separator_thousandth = '" . Yii::$app->currency->active['separator_thousandth'] . "';
        var separator_hundredth = '" . Yii::$app->currency->active['separator_hundredth'] . "';
     ", yii\web\View::POS_HEAD, 'numberformat');
        return parent::beforeAction($action);
    }

    /**
     * Get active/applied filters to make easier to cancel them.
     */
    public function getActiveFilters()
    {
        $request = Yii::$app->request;
        // Render links to cancel applied filters like prices, brands, attributes.
        $menuItems = [];


        if ($this->route == 'shop/catalog/view' || $this->route == 'shop/search/index') {
            $brands = array_filter(explode(',', $request->getQueryParam('brand')));
            $brands = Brand::getDb()->cache(function ($db) use ($brands) {
                return Brand::findAll($brands);
            }, 3600);
        }

        //$brandsIds = array_filter(explode(',', $request->getQueryParam('brand')));


        if ($request->getQueryParam('price')) {
            $menuItems['price'] = [
                'name' => 'price',
                'label' => Yii::t('shop/default', 'FILTER_BY_PRICE') . ':',
                'itemOptions' => ['id' => 'current-filter-prices']
            ];
        }
        if (isset(Yii::$app->controller->prices[0])) {
            if ($this->getCurrentMinPrice() > 0) {
                $menuItems['price']['items'][] = [
                    // 'name'=>'min_price',
                    'value_url' => number_format($this->getCurrentMinPrice(), 0, '', ''),
                    'value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()),
                    'label' => Html::decode(Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()), 'currency' => Yii::$app->currency->active['symbol']])),
                    'linkOptions' => ['class' => 'remove', 'data-price' => 'min_price'],
                    'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'price', Yii::$app->controller->prices[0])
                ];
            }
        }

        if (isset(Yii::$app->controller->prices[1])) {
            if ($this->getCurrentMaxPrice() > 0) {
                $menuItems['price']['items'][] = [
                    // 'name'=>'max_price',
                    'value_url' => number_format($this->getCurrentMaxPrice(), 0, '', ''),
                    'value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()),
                    'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MAX', ['value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active['symbol']]),
                    'linkOptions' => array('class' => 'remove', 'data-price' => 'max_price'),
                    'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'price', Yii::$app->controller->prices[1])
                ];
            }
        }


        /*if ($request->getQueryParam('min_price') || $request->getQueryParam('min_price')) {
            $menuItems['price'] = [
                'name' => 'price',
                'label' => Yii::t('shop/default', 'FILTER_BY_PRICE') . ':',
                'itemOptions' => ['id' => 'current-filter-prices']
            ];
        }
        if ($request->getQueryParam('min_price')) {
            $menuItems['price']['items'][] = [
                // 'name'=>'min_price',
                'value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()),
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()), 'currency' => Yii::$app->currency->active['symbol']]),
                'linkOptions' => ['class' => 'remove', 'data-price' => 'min_price'],
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'min_price')
            ];
        }

        if ($request->getQueryParam('max_price')) {
            $menuItems['price']['items'][] = [
                // 'name'=>'max_price',
                'value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()),
                'label' => Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MAX', ['value' => Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active['symbol']]),
                'linkOptions' => array('class' => 'remove', 'data-price' => 'max_price'),
                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'max_price')
            ];
        }*/

        if ($this->route == 'shop/catalog/view') {
            if (!empty($brands)) {
                $menuItems['brand'] = [
                    'name' => 'brand',
                    'label' => Yii::t('shop/default', 'FILTER_BY_BRAND') . ':',
                    'itemOptions' => ['id' => 'current-filter-brand']
                ];
                foreach ($brands as $id => $brand) {
                    $menuItems['brand']['items'][] = [
                        'value' => $brand->id,
                        'label' => $brand->name,
                        'options' => [
                            'class' => 'remove',
                            'data-name' => 'brand',
                            'data-target' => '#filter_brand_' . $brand->id
                        ],
                        'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'brand', $brand->id)
                    ];
                }
            }
        }

        // Process eav attributes
        $activeAttributes = $this->filter->activeAttributes;
        if (!empty($activeAttributes)) {
            foreach ($activeAttributes as $attributeName => $value) {
                if (isset($this->eavAttributes[$attributeName])) {
                    $attribute = $this->eavAttributes[$attributeName];
                    $menuItems[$attributeName] = [
                        'name' => $attribute->name,
                        'label' => $attribute->title . ':',
                        'itemOptions' => ['id' => 'current-filter-' . $attribute->name]
                    ];
                    foreach ($attribute->options as $option) {
                        if (isset($activeAttributes[$attribute->name]) && in_array($option->id, $activeAttributes[$attribute->name])) {
                            $menuItems[$attributeName]['items'][] = [
                                'value' => $option->id,
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

    /*todo: no used*/
    public function applyPricesFilter()
    {
        $minPrice = (isset($this->prices[0])) ? $this->prices[0] : 0;
        $maxPrice = (isset($this->prices[1])) ? $this->prices[1] : 0;

        $cm = Yii::$app->currency;
        if ($cm->active['id'] !== $cm->main['id'] && ($minPrice > 0 || $maxPrice > 0)) {
            $minPrice = $cm->activeToMain($minPrice);
            $maxPrice = $cm->activeToMain($maxPrice);
        }

        if ($minPrice > 0)
            $this->query->applyPrice($minPrice, '>=');
        if ($maxPrice > 0)
            $this->query->applyPrice($maxPrice, '<=');
    }


    public function applyPricesFilter_OLD()
    {
        $minPrice = Yii::$app->request->get('min_price');
        $maxPrice = Yii::$app->request->get('max_price');

        $cm = Yii::$app->currency;
        if ($cm->active['id'] !== $cm->main['id'] && ($minPrice > 0 || $maxPrice > 0)) {
            $minPrice = $cm->activeToMain($minPrice);
            $maxPrice = $cm->activeToMain($maxPrice);
        }

        if ($minPrice > 0)
            $this->query->applyPrice($minPrice, '>=');
        if ($maxPrice > 0)
            $this->query->applyPrice($maxPrice, '<=');
    }

    public function smartNames()
    {
        $filterData = $this->filter->getActiveFilters();
        unset($filterData['price']);
        $result = [];
        $name = '';
        $breadcrumbs = false;
        foreach ($filterData as $filterKey => $filterItems) {
            if ($filterKey == 'brand') {
                $brandNames = [];

                if (isset($filterItems['items'])) {
                    $i = 0;
                    foreach ($filterItems['items'] as $mKey => $mItems) {
                        $brandNames[] = $mItems['label'];
                        if ($i == 3) break;
                        $i++;
                    }
                    $sep = (count($brandNames) > 2) ? ', ' : ' ' . Yii::t('shop/default', 'AND') . ' ';
                    $name .= ' ' . implode($sep, $brandNames);
                }
            } else {
                $attributesNames[$filterKey] = [];
                if (isset($filterItems['items'])) {
                    $i = 0;
                    foreach ($filterItems['items'] as $mKey => $mItems) {
                        $attributesNames[$filterKey][] = $mItems['label'];
                        if ($i == 3) break;
                        $i++;
                        //$attributesNames[$filterKey]['url'][]=$mItems['value'];
                    }
                    $prefix = isset($filterData['brand']) ? '; ' : ', ';

                    $sep = (count($attributesNames[$filterKey]) > 2) ? ', ' : ' ' . Yii::t('shop/default', 'AND') . ' ';
                    $breadcrumbs .= ' ' . $filterItems['label'] . ' ' . implode($sep, $attributesNames[$filterKey]);
                    $name .= $prefix . ' ' . $breadcrumbs;
                }
            }
        }

        $result['breadcrumbs'] = $breadcrumbs;
        $result['title'] = $name;
        return $result;
    }


    public function _render($view = '@shop/views/catalog/view', array $params = [])
    {
        $activeFilters = $this->filter->getActiveFilters();

        if (Yii::$app->request->isAjax) {
            $render = $this->renderPartial('@shop/views/catalog/listview', ArrayHelper::merge([
                'provider' => $this->provider,
                'itemView' => $this->itemView,
                'filter' => $this->filter,
            ], $params));
            if (Yii::$app->request->headers->has('filter-ajax')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $url = ($this->dataModel) ? $this->dataModel->getUrl() : ['/' . Yii::$app->requestedRoute];

                return [
                    //'currentFilters' => $filterData,
                    //'full_url' => Url::to($this->currentUrl),
                    'currentUrl' => Yii::$app->request->getUrl(),
                    'items' => $render,
                    'i' => $this->itemView,
                    'pageName' => $this->pageName,
                    'totalCount' => $this->provider->totalCount,
                    'currentFiltersData' => ($activeFilters) ? $this->renderPartial($this->module->filterViewCurrent, [ //'@shop/widgets/filtersnew/views/current', '@app/widgets/filters/current'
                        'dataModel' => $this->dataModel,
                        'active' => $activeFilters,
                        'url' => $this->refreshUrl
                    ]) : null
                ];
            } else {
                return $render;
            }
        }
        return $this->render($view, ArrayHelper::merge([
            'provider' => $this->provider,
            'itemView' => $this->itemView,
            'filter' => $this->filter,
        ], $params));
    }


}
