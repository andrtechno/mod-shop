<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;
//use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\ProductType;

?>
<?php if (!$model->isNewRecord) { ?>
    <div class="row d-none">
        <div class="col-sm-4">

        </div>
        <div class="col-sm-4">

            <span class="badge badge-secondary"><?= $model->views; ?> просмотров</span>
        </div>
        <div class="col-sm-4">
            <span class="badge badge-secondary">Товар скрыт</span>
        </div>
    </div>
<?php } ?>
    <div class="card">
        <div class="card-header">
            <h5><?= Html::encode($this->context->pageName) ?></h5>
        </div>


        <?php
        if (!$model->isNewRecord && Yii::$app->settings->get('shop', 'auto_gen_url')) {
            echo Yii::t('shop/admin', 'ENABLE_AUTOURL_MODE');
        }


        $typesList = ProductType::find()->all();
        if (count($typesList) > 0) {
            // If selected `configurable` product without attributes display error
            if ($model->isNewRecord && $model->use_configurations == true && empty($model->configurable_attributes))
                $attributeError = true;
            else
                $attributeError = false;

            if ($model->isNewRecord && !$model->type_id || $attributeError === true) {


                echo Html::beginForm('', 'GET');
                panix\mod\shop\bundles\admin\ProductAsset::register($this);

                if ($attributeError) {
                    echo '<div class="alert alert-danger">' . Yii::t('shop/admin', 'SELECT_ATTRIBUTE_PRODUCT') . '</div>';
                }
                ?>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-sm-4"><?= Html::activeLabel($model, 'type_id', ['class' => 'control-label']); ?></div>
                        <div class="col-sm-8">
                            <?php echo Html::activeDropDownList($model, 'type_id', ArrayHelper::map($typesList, 'id', 'name'), ['class' => 'form-control']); ?>
                        </div>
                    </div>
                    <?php if (false) { ?>
                        <div class="form-group row">
                            <div class="col-sm-4"><?= Html::activeLabel($model, 'use_configurations', ['class' => 'control-label']); ?></div>
                            <div class="col-sm-8">
                                <?php echo Html::activeDropDownList($model, 'use_configurations', [0 => Yii::t('app/default', 'NO'), 1 => Yii::t('app/default', 'YES')], ['class' => 'form-control']); ?>
                            </div>
                        </div>

                        <div id="availableAttributes" class="form-group d-none"></div>
                    <?php } ?>

                </div>
                <div class="card-footer text-center">
                    <?= Html::submitButton(Yii::t('app/default', 'CREATE', 0), ['name' => false, 'class' => 'btn btn-success']); ?>
                </div>
                <?php
                echo Html::endForm();

            } else {


                $form = ActiveForm::begin([
                    'id' => 'product-form',
                    'options' => [
                        'enctype' => 'multipart/form-data'
                    ]
                ]);
                ?>
                <div class="card-body">
                    <?php

                    $tabs = [];


                    $tabs[] = [
                        'label' => $model::t('TAB_MAIN'),
                        'content' => $this->render('tabs/_main', ['form' => $form, 'model' => $model]),
                        'active' => true,
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];
                    $tabs[] = [
                        'label' => $model::t('TAB_WAREHOUSE'),
                        'content' => $this->render('tabs/_warehouse', ['form' => $form, 'model' => $model]),
                        'headerOptions' => [],
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];
                    $tabs[] = [
                        'label' => $model::t('TAB_IMG'),
                        'content' => $this->render('tabs/_images', ['form' => $form, 'model' => $model]),
                        'headerOptions' => [],
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];
                    $tabs[] = [
                         'label' => $model::t('TAB_REL'),
                         'content' => $this->render('tabs/_related', ['exclude' => $model->id, 'form' => $form, 'model' => $model]),
                         'headerOptions' => [],
                         'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                     ];
                    /*  $tabs[] = [
                         'label' => $model::t('TAB_KIT'),
                         'content' => $this->render('tabs/_kit', ['exclude' => $model->id, 'form' => $form, 'model' => $model]),
                         'headerOptions' => [],
                         'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                         //'visible' => false,
                     ];*/
                     $tabs[] = [
                         'label' => $model::t('TAB_VARIANTS'),
                         'content' => $this->render('tabs/_variations', ['model' => $model]),
                         'headerOptions' => [],
                         'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                     ];

                    $tabs[] = [
                        'label' => Yii::t('seo/default', 'TAB_SEO'),
                        'content' => $this->render('@seo/views/admin/default/_module_seo', ['model' => $model]),
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];


                    $tabs[] = [
                        'label' => $model::t('TAB_CATEGORIES'),
                        'content' => $this->render('tabs/_tree', ['exclude' => $model->id, 'form' => $form, 'model' => $model]),
                        'headerOptions' => [],
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];
                    $tabs[] = [
                        'label' => (isset($this->context->tab_errors['attributes'])) ? Html::icon('warning', ['class' => 'text-danger']) . ' Характеристики' : 'Характеристики',
                        'encode' => false,
                        'content' => $this->render('tabs/_attributes', ['form' => $form, 'model' => $model]),
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];


                    if ($model->use_configurations) {
                        $tabs[] = [
                            'label' => 'UPDATE_PRODUCT_TAB_CONF',
                            'content' => $this->render('tabs/_configurations', ['product' => $model]),
                            'headerOptions' => [],
                            'itemOptions' => ['class' => 'flex-sm-fill text-center nav-item'],
                            'visible' => false,
                        ];
                    }

                    echo \panix\engine\bootstrap\Tabs::widget([
                        //'encodeLabels'=>true,
                        'options' => [
                            'class' => 'nav-pills flex-column flex-sm-row nav-tabs-static'
                        ],
                        'items' => $tabs,
                    ]);

                    ?>


                </div>
                <div class="card-footer text-center">
                    <?= $model->submitButton(); ?>
                </div>
                <?php
                ActiveForm::end();
            }
        } else {
            echo '<div class="alert alert-danger">' . Yii::t('shop/admin', 'SELECT_TYPE_PRODUCT') . '</div>';
        }
        ?>


    </div>


<?php
if (!$model->isNewRecord) {
    $pricesHistory = (new \yii\db\Query())
        ->where(['product_id' => $model->id])
        ->from('{{%shop__product_price_history}}');
    $prices = $pricesHistory->all();


    $currencyHistory = (new \yii\db\Query())
        ->from('{{%shop__currency_history}}');
    $currencies = $currencyHistory->all();
    $seriesCurrency=[];
    $seriesCurrencyList = [];
    foreach ($currencies as $c) {
        $seriesCurrencyList[date('Ymd',$c['created_at'])] = [
            'y' => (double)$c['rate'],

        ];
    }


    $last = end($seriesCurrencyList);
    $series = [];
    $categories = [];
    $currenciesList = [];
    foreach ($prices as $p) {
        //$series[]=(double) $p['price_purchase'];
        $series[] = [
            'name' => 'test',
            'value' => (double)$p['price_purchase'].' грн.',
            'y' => (double)$p['price_purchase'],
            'marker' => [
                'symbol' => ($p['updated_at'])?'url(https://kurs.com.ua/storage/images/up.png)': 'url(https://kurs.com.ua/storage/images/down.png)'
            ]
        ];

        $seriesCurrency[] = [
            'name' => 'test',
            'value' => (isset($seriesCurrencyList[date('Ymd',$p['created_at'])]))?$seriesCurrencyList[date('Ymd',$p['created_at'])]['y']:$last['y'],
            'y' => (isset($seriesCurrencyList[date('Ymd',$p['created_at'])]))?$seriesCurrencyList[date('Ymd',$p['created_at'])]['y']:$last['y'],
        ];
        $categories[] = \panix\engine\CMS::date($p['created_at'], false);
    }

    echo \panix\ext\highcharts\Highcharts::widget([
        'scripts' => [
            // 'highcharts-more', // enables supplementary chart types (gauge, arearange, columnrange, etc.)
            'modules/exporting',
            // 'modules/drilldown',
        ],
        'options' => [
            'chart' => [
                'height' => 300,
                'type' => 'spline', //areaspline
                'plotBackgroundColor' => null,
                'plotBorderWidth' => null,
                'plotShadow' => false,
                'backgroundColor' => 'rgba(255, 255, 255, 0)',
            ],
            'title' => ['text' => $model->name],
            'subtitle' => [
                'text' => 'График цены'
            ],
            'xAxis' => [

                'type' => 'category',
                //'categories' => range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year))
                'categories' => $categories
            ],
            'yAxis' => [
                'min' => 0,
                'title' => false,
                //  'labels' => [
                //      'overflow' => 'justify'
                //  ],

                //    'title' => ['text' => 'Сумма']
            ],

            'plotOptions' => [
                'areaspline' => [
                    'fillOpacity' => 0.5
                ],
                //'column' => [
                //'pointPadding' => 0.1,
                //'borderWidth' => 0.0
                //],
                'spline' => [
                    'dataLabels' => [
                        'enabled' => true
                    ]
                ],

                'series' => [
                    //'borderWidth' => 1,
                    'dataLabels' => [
                        'enabled' => true,
                        'format' => '{point.value}'
                    ]
                ],

            ],
            'tooltip' => [
                'enabled' => true,
                'headerFormat' => '<table border="1">',
                'pointFormat' => '<tr><td><span style="font-size:11px">{series.name}</span></td></tr><span style="color:{point.color}">{point.name}</span>: <strong>{point.value} грн. Продано товаров: {point.products}</strong>',
                'footerFormat' => '</table>',
                'shared' => true,
                'crosshairs' => true,
                'useHTML' => true
                //'valueSuffix' => ' кол.'
            ],
            'series' => [
                [
                    'name' => 'Цена',
                    'colorByPoint' => true,
                    'tooltip' => [
                        'pointFormat' => '<tr><td><span style="font-weight: bold; color: {series.color}">{series.name}</span>: 123<br/><b>Продано товаров: {point.products}<br/></b></td><td>dsadsa</td></tr>'
                    ],

                    'data' => $series
                ],
                [
                    'name' => 'Валюта',
                    'colorByPoint' => true,
                    'tooltip' => [
                        'pointFormat' => '<tr><td><span style="font-weight: bold; color: {series.color}">{series.name}</span>: 123<br/><b>Продано товаров: {point.products}<br/></b></td><td>dsadsa</td></tr>'
                    ],

                    'data' => $seriesCurrency
                ],
            ],
        ]
    ]);

}
