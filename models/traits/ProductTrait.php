<?php

namespace panix\mod\shop\models\traits;

use Yii;
use panix\engine\Html;
use panix\engine\CMS;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Product;
use yii\helpers\ArrayHelper;

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
            'format' => 'html',
            'class' => 'panix\engine\grid\columns\jui\SliderColumn',
            'max' => (int)Product::find()->aggregatePrice('MAX'),
            'min' => (int)Product::find()->aggregatePrice('MIN'),
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                /** @var $model Product */
                $ss = '';
                if ($model->appliedDiscount) {
                    $price = $model->discountPrice;
                    $ss = '<del class="text-secondary font-italic">' . $model->originalPrice . '</del> / ';
                } else {
                    $price = $model->price;
                }
                if ($model->currency_id) {
                    $priceHtml = Yii::$app->currency->number_format($price);
                    $symbol = Html::tag('sup', Yii::$app->currency->currencies[$model->currency_id]['symbol']);
                } else {
                    $priceHtml = Yii::$app->currency->number_format(Yii::$app->currency->convert($price, $model->currency_id));
                    $symbol = Html::tag('sup', Yii::$app->currency->main['symbol']);
                }
                return $ss . Html::tag('span', $priceHtml, ['class' => 'text-success font-weight-bold']) . ' ' . $symbol;
            }
        ];
        $columns['commentsCount'] = [
            'header' => static::t('COMMENTS_COUNT'),
            'attribute' => 'commentsCount',
            'format' => 'html',
            //'filter' => false,
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
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_ACTIVE'),
                        'url' => '#',
                        'icon' => 'eye',
                        'linkOptions' => [
                            'onClick' => 'return setProductsStatus(1, this);',
                            'data-question' => self::t('CONFIRM_SHOW')
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_DEACTIVE'),
                        'url' => '#',
                        'icon' => 'eye-close',
                        'linkOptions' => [
                            'onClick' => 'return setProductsStatus(0, this);',
                            'data-question' => self::t('CONFIRM_HIDE')
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_SETCATEGORY'),
                        'url' => '#',
                        'icon' => 'folder-open',
                        'linkOptions' => [
                            'onClick' => 'return showCategoryAssignWindow(this);',
                            'data-question' => self::t('CONFIRM_CATEGORY')
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_COPY'),
                        'url' => '#',
                        'icon' => 'copy',
                        'linkOptions' => [
                            'onClick' => 'return showDuplicateProductsWindow(this);',
                            'data-question' => self::t('CONFIRM_COPY')
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_SETPRICE'),
                        'url' => '#',
                        'icon' => 'currencies',
                        'linkOptions' => [
                            'onClick' => 'return setProductsPrice(this);',
                            'data-question' => self::t('CONFIRM_PRICE')
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
        /** @var $this Product */
        if ($this->mainCategory) {
            if (!empty($this->mainCategory->seo_product_title)) {
                return $this->replaceMeta($this->mainCategory->seo_product_title);
            } else {
                $parent = $this->mainCategory->parent()->one();
                if ($parent) {
                    return $this->replaceMeta($parent->seo_product_title);
                }

            }
        }
        // return $this->name;
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
