<?php

namespace panix\mod\shop\components;

use panix\engine\CMS;
use panix\mod\shop\models\Category;
use Yii;
use yii\helpers\Url;
use yii\web\Response;
use panix\engine\Html;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\Product;
use panix\engine\controllers\WebController;

/**
 * Class FilterController
 *
 * @property array $activeAttributes
 * @property \panix\mod\shop\models\query\ProductQuery $query
 * @property \panix\mod\shop\models\query\ProductQuery $currentQuery
 * @property integer $per_page
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
    public $itemView = '_view_grid';
    public $per_page;

    public function beforeAction($action)
    {

        Url::remember();
        if (Yii::$app->request->get('view')) {
            if (in_array(Yii::$app->request->get('view'), ['list', 'grid'])) {
                $this->itemView = '_view_' . Yii::$app->request->get('view');
            }
        }


        $this->allowedPageLimit = explode(',', Yii::$app->settings->get('shop', 'per_page'));


        $this->per_page = (int)$this->allowedPageLimit[0];
        if (Yii::$app->request->get('per_page') && in_array($_GET['per_page'], $this->allowedPageLimit)) {
            $this->per_page = (int)Yii::$app->request->get('per_page');
        }


        if (Yii::$app->request->get('price')) {
            $this->prices = explode(',', Yii::$app->request->get('price'));
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
     * @return string min price
     */
    public function getMinPrice()
    {
        if ($this->_minPrice !== null)
            return $this->_minPrice;

        // if ($this->currentQuery) {
        $result = $this->currentQuery->aggregatePrice('MIN')->asArray()->one();
        if (isset($result['aggregation_price'])) {
            return $result['aggregation_price'];
        }
        // }

        return $this->_minPrice;
    }

    /**
     * @return string max price
     */
    public function getMaxPrice()
    {
        $result = $this->currentQuery->aggregatePrice('MAX')->asArray()->one();
        if (isset($result['aggregation_price'])) {
            return $result['aggregation_price'];
        }
        return $this->_maxPrice;
    }


    /**
     * @return mixed
     */
    public function getCurrentMinPrice()
    {
        if ($this->_currentMinPrice !== null)
            return $this->_currentMinPrice;

        $this->_currentMinPrice = (isset($this->prices[0])) ? trim($this->prices[0]) : Yii::$app->currency->convert($this->getMinPrice());

        return $this->_currentMinPrice;
    }

    /**
     * @return mixed
     */

    /**
     * @return string
     */
    public function getCurrentMaxPrice()
    {
        if ($this->_currentMaxPrice !== null)
            return $this->_currentMaxPrice;

        $this->_currentMaxPrice = (isset($this->prices[1])) ? trim($this->prices[1]) : Yii::$app->currency->convert($this->getMaxPrice());
        // $this->_currentMaxPrice = (isset($this->prices[1])) ? trim($this->prices[1]) : Yii::$app->currency->convert($this->_maxPrice);

        return $this->_currentMaxPrice;
    }

    public function getEavAttributes()
    {
        if (is_array($this->_eavAttributes))
            return $this->_eavAttributes;

        //CMS::dump($this->currentQuery);die;
        // Find category types
        $queryCategoryTypes = Product::find();
        // $queryCategoryTypes = $this->currentQuery;
        if ($this->dataModel instanceof Category) {
            $queryCategoryTypes->applyCategories($this->dataModel);
        } elseif ($this->dataModel instanceof Manufacturer) {
            $queryCategoryTypes->applyManufacturers($this->dataModel->id);
        }

        //$queryCategoryTypes->published();
        $queryCategoryTypes->select(Product::tableName() . '.type_id');
        $queryCategoryTypes->groupBy(Product::tableName() . '.type_id');
        $queryCategoryTypes->distinct(true);
//echo $queryCategoryTypes->createCommand()->rawSql;die;
        $typesIds = $queryCategoryTypes->createCommand()->queryColumn();

        // print_r($typesIds);die;
        /*$typesIds = Product::getDb()->cache(function () use ($queryCategoryTypes) {
            return $queryCategoryTypes->createCommand()->queryColumn();
        }, 3600);*/

        // Find attributes by type
        /* $query = Attribute::getDb()->cache(function () use ($typesIds) {
             return Attribute::find()
                 ->andWhere(['IN', TypeAttribute::tableName() . '.type_id', $typesIds])
                 ->useInFilter()
                 ->addOrderBy(['ordern' => SORT_DESC])
                 ->joinWith(['types', 'options'])
                 ->all();
         }, 3600);*/
        $query = Attribute::find()
            //->where(['IN', '`types`.`type_id`', $typesIds])
            ->useInFilter()
            ->sort()
            ->andWhere(['IN', '`type`.`type_id`', $typesIds])
            ->joinWith(['types type', 'options']);


//echo $query->createCommand()->rawSql;die;
        $result = $query->all();

        $this->_eavAttributes = [];
        foreach ($result as $attr)
            $this->_eavAttributes[$attr->name] = $attr;
        return $this->_eavAttributes;
    }

    public function getActiveAttributes()
    {
        $data = [];
        foreach (array_keys($_GET) as $key) {
            if (array_key_exists($key, $this->eavAttributes)) {

                // if (empty($_GET[$key]) && isset($_GET[$key])) {
                //	 throw new CHttpException(404, Yii::t('shop/default', 'NOFIND_CATEGORY'));
                // }

                if ((boolean)$this->eavAttributes[$key]->select_many === true) {
                    $data[$key] = explode(',', $_GET[$key]);
                } else {
                    $data[$key] = [$_GET[$key]];
                }
            } else {
                //  $this->error404(Yii::t('shop/default', 'NOFIND_CATEGORY1'));
            }
        }
        return $data;
    }

    public function getActiveAttributes2()
    {
        $data = [];
        $sss = (Yii::$app->request->post('filter')) ? Yii::$app->request->post('filter') : $_GET;
        foreach (array_keys($sss) as $key) {
            if (array_key_exists($key, $this->eavAttributes)) {

                // if (empty($_GET[$key]) && isset($_GET[$key])) {
                //	 throw new CHttpException(404, Yii::t('shop/default', 'NOFIND_CATEGORY'));
                // }

                if ((boolean)$this->eavAttributes[$key]->select_many === true) {
                    $data[$key] = (is_array($sss[$key])) ? $sss[$key] : explode(',', $sss[$key]);
                } else {
                    $data[$key] = [$params[$key]];
                }
            } else {
                //  $this->error404(Yii::t('shop/default', 'NOFIND_CATEGORY1'));
            }
        }
        return $data;
    }

    /**
     * Get active/applied filters to make easier to cancel them.
     */
    public function getActiveFilters()
    {
        $request = Yii::$app->request;
        // Render links to cancel applied filters like prices, manufacturers, attributes.
        $menuItems = [];


        if ($this->route == 'shop/catalog/view' || $this->route == 'shop/search/index') {
            $manufacturers = array_filter(explode(',', $request->getQueryParam('manufacturer')));
            $manufacturers = Manufacturer::getDb()->cache(function ($db) use ($manufacturers) {
                return Manufacturer::findAll($manufacturers);
            }, 3600);
        }

        //$manufacturersIds = array_filter(explode(',', $request->getQueryParam('manufacturer')));


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
            if (!empty($manufacturers)) {
                $menuItems['manufacturer'] = [
                    'name' => 'manufacturer',
                    'label' => Yii::t('shop/default', 'FILTER_BY_MANUFACTURER') . ':',
                    'itemOptions' => ['id' => 'current-filter-manufacturer']
                ];
                foreach ($manufacturers as $id => $manufacturer) {
                    $menuItems['manufacturer']['items'][] = [
                        'value' => $manufacturer->id,
                        'label' => $manufacturer->name,
                        'linkOptions' => [
                            'class' => 'remove',
                            'data-name' => 'manufacturer',
                            'data-target' => '#filter_manufacturer_' . $manufacturer->id
                        ],
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
        $filterData = $this->getActiveFilters();
        unset($filterData['price']);
        $result = [];
        $name = '';
        $breadcrumbs = false;
        foreach ($filterData as $filterKey => $filterItems) {
            if ($filterKey == 'manufacturer') {
                $manufacturerNames = [];

                if (isset($filterItems['items'])) {
                    $i = 0;
                    foreach ($filterItems['items'] as $mKey => $mItems) {
                        $manufacturerNames[] = $mItems['label'];
                        if ($i == 3) break;
                        $i++;
                    }
                    $sep = (count($manufacturerNames) > 2) ? ', ' : ' ' . Yii::t('shop/default', 'AND') . ' ';
                    $name .= ' ' . implode($sep, $manufacturerNames);
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
                    $prefix = isset($filterData['manufacturer']) ? '; ' : ', ';

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


    public function _render($view = '@shop/views/catalog/view')
    {
        $activeFilters = $this->getActiveFilters();
        $render = $this->renderPartial('@shop/views/catalog/listview', [
            'provider' => $this->provider,
            'itemView' => $this->itemView
        ]);
        if (Yii::$app->request->isAjax) {
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
                    'currentFiltersData' => ($activeFilters) ? $this->renderPartial('@shop/widgets/filtersnew/views/current', [ //'@shop/widgets/filtersnew/views/current', '@app/widgets/filters/current'
                        'dataModel' => $this->dataModel,
                        'active' => $activeFilters,
                        'url' => $url
                    ]) : null
                ];
            } else {
                return $render;
            }
        }
        return $this->render($view, [
            'provider' => $this->provider,
            'itemView' => $this->itemView,

        ]);
    }
}