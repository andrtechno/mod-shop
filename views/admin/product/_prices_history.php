<?php
use yii\db\Query;

if (!$model->isNewRecord) {
    $pricesHistory = (new Query())
        ->where(['product_id' => $model->id])
        ->from('{{%shop__product_price_history}}');
    $prices = $pricesHistory->all();


    $currencyHistory = (new Query())
        ->from('{{%shop__currency_history}}');
    $currencies = $currencyHistory->all();
    $seriesCurrency = [];
    $seriesCurrencyList = [];
    foreach ($currencies as $c) {
        $seriesCurrencyList[date('Ymd', $c['created_at'])] = [
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
            'value' => (double)$p['price_purchase'] . ' грн.',
            'y' => (double)$p['price_purchase'],
            'marker' => [
                'symbol' => ($p['type']) ? 'url(https://kurs.com.ua/storage/images/up.png)' : 'url(https://kurs.com.ua/storage/images/down.png)'
            ]
        ];

        $seriesCurrency[] = [
            'name' => 'test',
            'value' => (isset($seriesCurrencyList[date('Ymd', $p['created_at'])])) ? $seriesCurrencyList[date('Ymd', $p['created_at'])]['y'] : $last['y'],
            'y' => (isset($seriesCurrencyList[date('Ymd', $p['created_at'])])) ? $seriesCurrencyList[date('Ymd', $p['created_at'])]['y'] : $last['y'],
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
                'backgroundColor' => 'rgba(255, 255, 255, 1)',
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
                        'pointFormat' => '<tr><td><span style="font-weight: bold; color: {series.color}">{series.name}</span>: {point.value}</td><td>dsadsa</td></tr>'
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