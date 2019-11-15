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
                    ['class' => 'custom-select w-auto', 'prompt' => '--- ' . $m->title . ' ---']
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
                            'data-confirm' => self::t('CONFIRM_SHOW')
                        ],
                    ],
                    [
                        'label' => self::t('GRID_OPTION_DEACTIVE'),
                        'url' => '#',
                        'icon' => 'eye-close',
                        'linkOptions' => [
                            'onClick' => 'return setProductsStatus(0, this);',
                            'data-confirm' => self::t('CONFIRM_HIDE')
                        ],
                    ],
                    [
                        'label' => self::t('GRID_OPTION_SETCATEGORY'),
                        'url' => '#',
                        'icon' => 'folder-open',
                        'linkOptions' => [
                            'onClick' => 'return showCategoryAssignWindow(this);',
                            'data-confirm' => self::t('CONFIRM_CATEGORY')
                        ],
                    ],
                    [
                        'label' => self::t('GRID_OPTION_COPY'),
                        'url' => '#',
                        'icon' => 'copy',
                        'linkOptions' => [
                            'onClick' => 'return showDuplicateProductsWindow(this);',
                            'data-confirm' => self::t('CONFIRM_COPY')
                        ],
                    ],
                    [
                        'label' => self::t('GRID_OPTION_SETPRICE'),
                        'url' => '#',
                        'icon' => 'currencies',
                        'linkOptions' => [
                            'onClick' => 'return setProductsPrice(this);',
                            'data-confirm' => self::t('CONFIRM_PRICE')
                        ],
                    ],
                    [
                        'label' => self::t('GRID_OPTION_UPDATE_VIEWS'),
                        'url' => '#',
                        'icon' => 'refresh',
                        'linkOptions' => [
                            'onClick' => 'return updateProductsViews(this);',
                            'data-confirm' => self::t('CONFIRM_UPDATE_VIEWS')
                        ],
                    ]
                ]
            ]
        ];

        return $columns;
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
        /** @var $this Product */
        if ($this->mainCategory) {
            if (!empty($this->mainCategory->seo_product_description)) {
                return $this->replaceMeta($this->mainCategory->seo_product_description);
            } else {

                $parent = $this->mainCategory->parent()->one();
                if ($parent) {
                    return $this->replaceMeta($parent->seo_product_description);
                }
            }
        }
        //  return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_products_description'));
    }

    public function title()
    {
        $title = $this->name;
        /** @var $this Product */
        if ($this->mainCategory) {
            if (!empty($this->mainCategory->seo_product_title)) {
                $title = $this->replaceMeta($this->mainCategory->seo_product_title);
            } else {
                $parent = $this->mainCategory->parent()->one();
                if ($parent) {
                    if ($parent->seo_product_title)
                        $title = $this->replaceMeta($parent->seo_product_title);
                }

            }
        }
        return $title;
    }


    public function replaceMeta($text)
    {
        /** @var $this Product */
        $attrArray = [];
        foreach ($this->getProductAttributes() as $k => $attr) {
            $attrArray['{eav_' . $k . '_value}'] = $attr->value;
            $attrArray['{eav_' . $k . '_name}'] = $attr->name;
        }

        $replace = ArrayHelper::merge([
            "{product_name}" => $this->name,
            "{product_price}" => $this->price,
            "{product_sku}" => $this->sku,
            "{product_brand}" => (isset($this->manufacturer)) ? $this->manufacturer->name : null,
            "{category_name}" => (isset($this->mainCategory)) ? $this->mainCategory->name : null,
            "{current_currency}" => Yii::$app->currency->active['symbol'],
        ], $attrArray);
        return CMS::textReplace($text, $replace);
    }


}
