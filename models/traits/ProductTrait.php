<?php

namespace panix\mod\shop\models\traits;

use panix\mod\shop\models\Attribute;
use Yii;
use panix\mod\shop\models\search\ProductSearch;
use panix\engine\CMS;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;

trait ProductTrait
{
    public static function getAvailabilityItems()
    {
        return array(
            1 => self::t('AVAILABILITY_1'),
            2 => self::t('AVAILABILITY_2'),
            3 => self::t('AVAILABILITY_3'),
            4 => self::t('AVAILABILITY_4'),
        );
    }

    public function getGridColumns()
    {
        $columns = [];
        $columns['image'] = [
            'attribute' => 'image',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function ($model) {
                return $model->renderGridImage('50x50');
            },
        ];
        $columns['name'] = 'name';
        $columns['price'] = [
            'attribute' => 'price',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return Yii::$app->currency->number_format($model->price) . ' ' . Yii::$app->currency->main->symbol;
            }
        ];
        $columns['date_create'] = [
            'attribute' => 'date_create',
            'format' => 'raw',
            'filter' => DatePicker::widget([
                'model' => new ProductSearch(),
                'attribute' => 'date_create',
                'dateFormat' => 'yyyy-MM-dd',
                'options' => ['class' => 'form-control']
            ]),
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return ($model->date_create) ? Yii::$app->formatter->asDate($model->date_create) . ' '.Yii::t('app','IN').' ' . Yii::$app->formatter->asTime($model->date_create) : null;
            }
        ];
        $columns['date_update'] = [
            'attribute' => 'date_update',
            'format' => 'raw',
            'filter' => DatePicker::widget([
                'model' => new ProductSearch(),
                'attribute' => 'date_update',
                'dateFormat' => 'yyyy-MM-dd',
                'options' => ['class' => 'form-control']
            ]),
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return ($model->date_update) ? Yii::$app->formatter->asDate($model->date_update) . ' '.Yii::t('app','IN').' ' . Yii::$app->formatter->asTime($model->date_update) : null;
            }
        ];


        $query = Attribute::find()
            ->displayOnFront()
            ->sorting()
            //->where(['IN', 'name', array_keys($this->_attributes)])
            ->all();

        foreach ($query as $m) {
            $columns['' . $m->name] = [
                'class' => 'panix\mod\shop\components\EavColumn',
                'attribute' => 'eav_'.$m->name,
                'header' => $m->title,
               // 'filter'=>ArrayHelper::map($m->options, 'id', 'id'),
                'filter'=>true,
                'contentOptions' => ['class' => 'text-center'],
            ];
        }


        $columns['DEFAULT_CONTROL'] = [
            'class' => 'panix\engine\grid\columns\ActionColumn',
        ];
        $columns['DEFAULT_COLUMNS'] = [
            [
                'class' => \panix\engine\grid\sortable\Column::class,
                'url' => ['/admin/shop/product/sortable'],
                'successMessage' => Yii::t('shop/admin', 'SORT_PRODUCT_SUCCESS_MESSAGE')
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
        //Yii::import('mod.shop.components.AttributesRender');
        $attributes = new \panix\mod\shop\components\AttributesRender;
        return $attributes->getData($this);
    }

    public function keywords()
    {
        return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_products_keywords'));
    }

    public function description()
    {
        return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_products_description'));
    }

    public function title()
    {
        return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_products_title'));
    }

    public function replaceMeta($text)
    {
        $attrArray = array();
        foreach ($this->getProductAttributes() as $k => $attr) {
            $attrArray['{eav_' . $k . '_value}'] = $attr->value;
            $attrArray['{eav_' . $k . '_name}'] = $attr->name;
        }

        $replace = ArrayHelper::merge([
            "{product_name}" => $this->name,
            "{product_price}" => $this->price,
            "{product_sku}" => $this->sku,
            "{product_pcs}" => $this->pcs,
            "{product_brand}" => (isset($this->manufacturer)) ? $this->manufacturer->name : null,
            "{product_main_category}" => (isset($this->mainCategory)) ? $this->mainCategory->name : null,
            "{current_currency}" => Yii::$app->currency->active->symbol,
        ], $attrArray);
        return CMS::textReplace($text, $replace);
    }


}
