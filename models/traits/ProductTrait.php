<?php

namespace panix\mod\shop\models\traits;

use Yii;
use panix\mod\shop\models\search\ProductSearch;

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
                'url' => ['/admin/shop/default/sortable']
            ],
            [
                'class' => 'panix\engine\grid\columns\CheckboxColumn',
                'customActions' => [
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_ACTIVE'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'icon-eye',
                        'options' => [
                            'onClick' => 'return setProductsStatus(1, this);',
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_DEACTIVE'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'icon-eye-close',
                        'options' => [
                            'onClick' => 'return setProductsStatus(0, this);',
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_SETCATEGORY'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'icon-folder-open',
                        'options' => [
                            'onClick' => 'return showCategoryAssignWindow(this);',
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_COPY'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'icon-copy',
                        'options' => [
                            'onClick' => 'return showDuplicateProductsWindow(this);',
                        ],
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'GRID_OPTION_SETPRICE'),
                        'url' => 'javascript:void(0)',
                        'icon' => 'icon-currencies',
                        'options' => [
                            'onClick' => 'return setProductsPrice(this);',
                        ],
                    ]
                ]
            ]
        ];

        return $columns;
    }

}
