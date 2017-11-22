<?php

namespace panix\mod\shop\models\traits;

use Yii;
use panix\mod\shop\models\search\ProductSearch;
use panix\engine\CMS;
use yii\helpers\ArrayHelper;
trait ProductTrait {

    public function getGridColumns() {
        $columns = [];
        $attributesColumns = new \panix\mod\shop\components\AttributesColumns();
        $attributesList = $attributesColumns->getList($this);
        //    print_r($attributesList);die;


        $columns[] = [
            'attribute' => 'image',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function($model) {
        return $model->renderGridImage('50x50');
    },
        ];
        $columns[] = 'name';
        $columns[] = [
            'attribute' => 'price',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
        return $model::formatPrice($model->price) . ' ' . Yii::$app->currency->main->symbol;
    }
        ];
        $columns[] = [
            'attribute' => 'date_create',
            'format' => 'raw',
            'filter' => \yii\jui\DatePicker::widget([
                'model' => new ProductSearch(),
                'attribute' => 'date_create',
                'dateFormat' => 'yyyy-MM-dd',
                'options' => ['class' => 'form-control']
            ]),
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
        return Yii::$app->formatter->asDatetime($model->date_create, 'php:d D Y H:i:s');
    }
        ];
        $columns[] = [
            'attribute' => 'date_update',
            'format' => 'raw',
            'filter' => \yii\jui\DatePicker::widget([
                'model' => new ProductSearch(),
                'attribute' => 'date_update',
                'dateFormat' => 'yyyy-MM-dd',
                'options' => ['class' => 'form-control']
            ]),
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
        return Yii::$app->formatter->asDatetime($model->date_update, 'php:d D Y H:i:s');
    }
        ];

        foreach ($attributesList as $at) {
            $columns[] = [
                'class' => $at['class'],
                'attribute' => $at['attribute'],
                'header' => $at['header'],
                'contentOptions' => ['class' => 'text-center']
            ];
        }

        $columns['DEFAULT_CONTROL'] = [
            'class' => 'panix\engine\grid\columns\ActionColumn',
        ];
        $columns['DEFAULT_COLUMNS'] = [
            [
                'class' => \panix\engine\grid\sortable\Column::className(),
                'url' => ['/admin/shop/product/sortable'],
                'successMessage'=>Yii::t('shop/admin','SORT_PRODUCT_SUCCESS_MESSAGE')
            ],
            [
                'class' => 'panix\engine\grid\columns\CheckboxColumn',
                'customActions' => [
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_ACTIVE'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'eye',
                        'options' => [
                            'onClick' => 'return setProductsStatus(1, this);',
                             'data-question'=>self::t('COMFIRM_SHOW')
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_DEACTIVE'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'eye-close',
                        'options' => [
                            'onClick' => 'return setProductsStatus(0, this);',
                            'data-question'=>self::t('COMFIRM_HIDE')
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_SETCATEGORY'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'folder-open',
                        'options' => [
                            'onClick' => 'return showCategoryAssignWindow(this);',
                            'data-question'=>self::t('COMFIRM_CATEGORY')
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_COPY'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'copy',
                        'options' => [
                            'onClick' => 'return showDuplicateProductsWindow(this);',
                            'data-question'=>self::t('COMFIRM_COPY')
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_SETPRICE'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'currencies',
                        'options' => [
                            'onClick' => 'return setProductsPrice(this);',
                            'data-question'=>self::t('COMFIRM_PRICE')
                        ],
                    ]
                ]
            ]
        ];

        return $columns;
    }
    
    
        public function getProductAttributes() {
        //Yii::import('mod.shop.components.AttributesRender');
        $attributes = new \panix\mod\shop\components\AttributesRender;
        return $attributes->getData($this);
    }
    
    public function keywords() {
        return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_products_keywords'));
    }

    public function description() {
        return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_products_description'));
    }

    public function title() {
        return $this->replaceMeta(Yii::$app->settings->get('shop', 'seo_products_title'));
    }

    public function replaceMeta($text) {
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
