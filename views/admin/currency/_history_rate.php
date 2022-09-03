<?php

use yii\db\Query;
use panix\ext\highcharts\Highcharts;


$query = (new Query())->where(['currency_id' => $model->id])->orderBy(['created_at'=>SORT_ASC])
    ->from('{{%shop__currency_history}}');
$currencies = $query->all();

$categories=[];
$series=[];
foreach ($currencies as $k => $p) {

    $value = (double)$p['rate'];

    $parentFlag = false;
    $symbol = 'triangle';
    $marker = [
        'fillColor' => '#c0c0c0',
    ];

    if (isset($currencies[$k - 1]['rate'])) {
        $marker = [
            'fillColor' => ($currencies[$k - 1]['rate'] > $value) ? 'red' : 'green',
            'symbol' => ($currencies[$k - 1]['rate'] < $value) ? 'triangle' : 'triangle-down'
        ];
    }
    $name = '';
    if ($p['user_id']) {
        $user = \panix\mod\user\models\User::findOne($p['user_id']);
        $name = 'Изменено пользователем '.$user->getDisplayName();
    }

    $series[] = [
        'name' => $name,
        'value' => $value,
        'y' => $value,
        'marker' => $marker
    ];
    $categories[] = \panix\engine\CMS::date($p['created_at'], false);
}

if($categories){
echo Highcharts::widget([
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
        'title' => ['text' => 'История изменений курса валюты '.$model->iso],
        'subtitle' => [
            'text' => 'График курса'
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
                    'format' => '{point.value:.2f}'
                ]
            ],

        ],
        'tooltip' => [
            'enabled' => true,
            'headerFormat' => '<table border="1">',
            'pointFormat' => '<tr><td><span style="font-size:11px">{series.name}</span></td></tr><span style="color:{point.color}">{point.name}</span>: <strong>{point.value:.2f}</strong>',
            'footerFormat' => '</table>',
            'shared' => true,
            'crosshairs' => true,
            'useHTML' => true
            //'valueSuffix' => ' кол.'
        ],
        'series' => [
            [
                'name' => 'Курс',
                'colorByPoint' => true,
                'tooltip' => [
                    'pointFormat' => '<tr><td><span style="font-weight: bold; color: {series.color}">{series.name} ({point.name})</span>: {point.value:.2f}</td></tr>'
                ],
                'data' => $series
            ],
        ],
    ]
]);
}