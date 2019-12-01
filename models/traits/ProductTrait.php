<?php

namespace panix\mod\shop\models\traits;

use panix\mod\shop\models\Category;
use Yii;
use panix\engine\Html;
use panix\engine\CMS;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Product;
use yii\helpers\ArrayHelper;

/**
 * Trait ProductTrait
 * @package panix\mod\shop\models\traits
 */
trait ProductTrait
{
    public static function getAvailabilityItems()
    {
        return [
            1 => self::t('AVAILABILITY_1'),
            2 => self::t('AVAILABILITY_2'),
            3 => self::t('AVAILABILITY_3'),
            4 => self::t('AVAILABILITY_4'),
        ];
    }

    public function getGridColumns()
    {
        $columns = [];
        $columns['image'] = [
            'class' => 'panix\engine\grid\columns\ImageColumn',
            'attribute' => 'image',
            // 'filter'=>true,
            'value' => function ($model) {
                /** @var $model Product */
                return $model->renderGridImage();
            },
        ];
        $columns['name'] = [
            'attribute' => 'name',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-left'],
            'value' => function ($model) {
                /** @var $model Product */
                if ($model->name) {
                    $html = Html::a($model->name, $model->getUrl());
                    if ($model->views > 0) {
                        $html .= " <small>(" . Yii::t('app', 'VIEWS', ['n' => $model->views]) . ")</small>";
                    }
                    if (true) {

                        $labels = [];
                        foreach ($model->labels() as $label) {
                            $labels[] = Html::tag('span', $label['value'], [
                                'class' => 'badge badge-' . $label['class'],
                                'data-toggle' => 'tooltip',
                                'title' => $label['tooltip']
                            ]);
                        }


                        $html .= '<br/>' . implode('', $labels);
                    }


                    return $html;
                }
                return null;

            },
        ];
        $columns['type_id']=[
            'attribute' => 'type_id',
        ];
        $columns['price'] = [
            'attribute' => 'price',
            'format' => 'raw',
            'class' => 'panix\engine\grid\columns\jui\SliderColumn',
            'max' => (int)Product::find()->aggregatePrice('MAX'),
            'min' => (int)Product::find()->aggregatePrice('MIN'),
            'prefix' => '<sup>' . Yii::$app->currency->main['symbol'] . '</sup>',
            'contentOptions' => ['class' => 'text-center', 'style' => 'position:relative'],
            'value' => function ($model) {
                /** @var $model Product */
                $ss = '';
                if ($model->appliedDiscount) {
                    $price = $model->discountPrice;
                    $ss = '<del class="text-secondary">' . Yii::$app->currency->number_format($model->originalPrice) . '</del> / ';
                } else {
                    $price = $model->price;
                }
                if ($model->currency_id) {
                    $priceHtml = $price;
                    $symbol = Html::tag('sup', Yii::$app->currency->currencies[$model->currency_id]['symbol']);
                } else {
                    $priceHtml = Yii::$app->currency->convert($price, $model->currency_id);
                    $symbol = Html::tag('sup', Yii::$app->currency->main['symbol']);
                }
                //$ss .= '<span class="badge badge-danger position-absolute" style="top:0;right:0;">123</span>';
                return $ss . Html::tag('span', Yii::$app->currency->number_format($priceHtml), ['class' => 'text-success font-weight-bold']) . ' ' . $symbol;
            }
        ];
        $columns['categories'] = [
            'header' => static::t('Категории'),
            'attribute' => 'categories',
            'format' => 'html',
            'contentOptions' => ['style' => 'max-width:180px'],
            'filter' => Html::dropDownList('category_id', null, Category::flatTree(),
                [
                    'class' => 'form-control',
                    'prompt' => html_entity_decode('&mdash; выберите категорию &mdash;')
                ]
            ),
            'value' => function ($model) {
                /** @var $model Product */
                $result = '';
                foreach ($model->categories as $category) {
                    $options = [];
                    if ($category->id == $model->main_category_id) {
                        $options['class'] = 'badge badge-secondary';
                        $options['title'] = 'Main category';
                    } else {
                        $options['class'] = 'badge badge-light';
                    }
                    $result .= Html::a($category->name, $category->getUrl(), $options);
                }
                return $result;
            }
        ];
        $columns['commentsCount'] = [
            'header' => static::t('COMMENTS_COUNT'),
            'attribute' => 'commentsCount',
            'format' => 'html',
            'filter' => true,
            'value' => function ($model) {
                return Html::a($model->commentsCount, ['/admin/comments/default/index', 'CommentsSearch[object_id]' => $model->primaryKey]);
            }
        ];
        $columns['created_at'] = [
            'attribute' => 'created_at',
            'class' => 'panix\engine\grid\columns\jui\DatepickerColumn',
        ];
        $columns['updated_at'] = [
            'attribute' => 'updated_at',
            'class' => 'panix\engine\grid\columns\jui\DatepickerColumn',
        ];


        /*$query2 = Attribute::find()
            ->cache(3600)
            ->displayOnFront()
            ->sort()
            //->where(['IN', 'name', array_keys($this->_attributes)])
            ->all();*/


        $db = Attribute::getDb();
        $query = $db->cache(function () {
            return Attribute::find()
                ->displayOnFront()
                ->sort()
                ->all();
        }, 3600);


        $get = Yii::$app->request->get('ProductSearch');
        foreach ($query as $m) {

            $columns['' . $m->name] = [
                //'class' => 'panix\mod\shop\components\EavColumn',
                'attribute' => 'eav_' . $m->name,
                'header' => $m->title,
                'filter' => Html::dropDownList(
                    'ProductSearch[eav][' . $m->name . ']',
                    (isset($get['eav'][$m->name])) ? $get['eav'][$m->name] : null,
                    ArrayHelper::map($m->options, 'id', 'value'),
                    ['class' => 'custom-select w-auto', 'prompt' => html_entity_decode('&mdash; ' . $m->title . ' &mdash;')]
                ),
                //'filter'=>true,
                'contentOptions' => ['class' => 'text-center'],
                'filterOptions' => ['class' => 'text-center'],

            ];
        }


        $columns['DEFAULT_CONTROL'] = [
            'class' => 'panix\engine\grid\columns\ActionColumn',
        ];
        $columns['DEFAULT_COLUMNS'] = [
            [
                'class' => \panix\engine\grid\sortable\Column::class,
                'url' => ['/shop/product/sortable']
            ],
            [
                'class' => 'panix\engine\grid\columns\CheckboxColumn',
                'customActions' => [
                    [
                        'label' => self::t('GRID_OPTION_ACTIVE'),
                        'url' => '#',
                        'icon' => 'eye',
                        'linkOptions' => [
                            'onClick' => 'return setProductsStatus(1, this);',
                            'data-confirm' => self::t('CONFIRM_SHOW'),
                            'data-pjax' => 0
                        ],
                    ],
                    [
                        'label' => self::t('GRID_OPTION_DEACTIVE'),
                        'url' => '#',
                        'icon' => 'eye-close',
                        'linkOptions' => [
                            'onClick' => 'return setProductsStatus(0, this);',
                            'data-confirm' => self::t('CONFIRM_HIDE'),
                            'data-pjax' => 0
                        ],
                    ],
                    /*[
                        'label' => self::t('GRID_OPTION_SETCATEGORY'),
                        'url' => '#',
                        'icon' => 'folder-open',
                        'linkOptions' => [
                            'onClick' => 'return showCategoryAssignWindow(this);',
                            'data-confirm' => self::t('CONFIRM_CATEGORY'),
                            'data-pjax' => 0
                        ],
                    ],*/
                    [
                        'label' => self::t('GRID_OPTION_COPY'),
                        'url' => '#',
                        'icon' => 'copy',
                        'linkOptions' => [
                            'onClick' => 'return showDuplicateProductsWindow(this);',
                            'data-confirm' => self::t('CONFIRM_COPY'),
                            'data-pjax' => 0
                        ],
                    ],
                    [
                        'label' => self::t('GRID_OPTION_SETPRICE'),
                        'url' => '#',
                        'icon' => 'currencies',
                        'linkOptions' => [
                            'onClick' => 'return setProductsPrice(this);',
                            'data-confirm' => self::t('CONFIRM_PRICE'),
                            'data-pjax' => 0
                        ],
                    ],
                    [
                        'label' => self::t('GRID_OPTION_UPDATE_VIEWS'),
                        'url' => '#',
                        'icon' => 'refresh',
                        'linkOptions' => [
                            'onClick' => 'return updateProductsViews(this);',
                            'data-confirm' => self::t('CONFIRM_UPDATE_VIEWS'),
                            'data-pjax' => 0
                        ],
                    ]
                ]
            ]
        ];

        return $columns;
    }


    public function getDataAttributes(){


        /** @var \panix\mod\shop\components\EavBehavior $attributes */
        $attributes = $this->getEavAttributes();
        $data = [];
        $groups = [];
        $models = [];

        // $query = Attribute::getDb()->cache(function () {
        $query = Attribute::find()
            ->where(['IN', 'name', array_keys($attributes)])
            ->displayOnFront()
            ->sort()
            ->all();
        // }, 3600);


       foreach ($query as $m)
           $models[$m->name] = $m;




        foreach ($models as $model) {
            /** @var Attribute $model */
            $abbr = ($model->abbreviation) ? ' ' . $model->abbreviation : '';

            $value = $model->renderValue($attributes[$model->name]) . $abbr;

            if ($model->group && Yii::$app->settings->get('shop', 'group_attribute')) {
                $groups[$model->group->name][] = [
                    'id' => $model->id,
                    'name' => $model->title,
                    'hint' => $model->hint,
                    'value' => $value
                ];

            }
           // $data[$model->title] = $value;
            $data[$model->name]['name'] = $model->title;
            $data[$model->name]['value'] = $value;

        }

        return [
            'data' => $data,
            'groups' => $groups,
        ];
    }

    /**
     * Convert price to current currency
     *
     * @param string $attr
     * @return mixed
     */
    public function toCurrentCurrency($attr = 'price')
    {
        return Yii::$app->currency->convert($this->$attr);
    }

    public function getProductAttributes()
    {
        /** @var $this Product */
        //Yii::import('mod.shop.components.AttributesRender');
        $attributes = new \panix\mod\shop\components\AttributesRender;
        return $attributes->getData($this);
    }


    public function description()
    {
        $description = $this->name;
        /** @var $this Product */
        /*if ($this->mainCategory) {
            if (!empty($this->mainCategory->seo_product_description)) {
                return $this->replaceMeta($this->mainCategory->seo_product_description);
            } else {

                $parent = $this->mainCategory->parent()->one();
                if ($parent) {
                    return $this->replaceMeta($parent->seo_product_description);
                }
            }
        }*/
        if($this->type){
            if($this->type->product_description){
                $description = $this->replaceMeta($this->type->product_description);
            }

        }
          return $description;
    }
    public function title()
    {
        $title = $this->name;
        /** @var $this Product */
        /*if ($this->mainCategory) {
            if (!empty($this->mainCategory->seo_product_title)) {
                $title = $this->replaceMeta($this->mainCategory->seo_product_title);
            } else {
                $parent = $this->mainCategory->parent()->one();
                if ($parent) {
                    if ($parent->seo_product_title)
                        $title = $this->replaceMeta($parent->seo_product_title);
                }

            }
        }*/


        if($this->type){
            if($this->type->product_title){
                $title = $this->replaceMeta($this->type->product_title);
            }

        }

        return $title;
    }


    public function replaceMeta($text)
    {
        /** @var $this Product */
        $attrArray = [];
        foreach ($this->dataAttributes['data'] as $k => $attr) {
            $attrArray['{eav_' . $k . '_value}'] = $attr['value'];
            $attrArray['{eav_' . $k . '_name}'] = $attr['name'];
        }
        $replace = ArrayHelper::merge([
            "{product_id}" => $this->id,
            "{product_name}" => $this->name,
            "{product_price}" => $this->getFrontPrice(),
            "{product_sku}" => $this->sku,
            "{product_manufacturer}" => (isset($this->manufacturer)) ? $this->manufacturer->name : null,
            "{product_category}" => (isset($this->mainCategory)) ? $this->mainCategory->name : null,
            "{currency.symbol}" => Yii::$app->currency->active['symbol'],
            "{currency.iso}" => Yii::$app->currency->active['iso'],
        ], $attrArray);

        return CMS::textReplace($text, $replace);
    }


}
