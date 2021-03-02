<?php
use yii\db\Query;
use panix\engine\CMS;


//CMS::dump(Yii::$app->queue->isDone(2207));

if (!$model->isNewRecord) {


    /*if($findLast['currency_rate'] <> $current_rate){
        Yii::$app->getDb()->createCommand()->insert('{{%shop__product_price_history}}', [
            'product_id' => $model->id,
            'currency_id' => $model->currency_id,
            'currency_rate' => Yii::$app->currency->currencies[$model->currency_id]['rate'],
            'price' => $model->price,
            'price_purchase' => $model->price_purchase,
            'created_at' => time(),
            'type' => ($findLast['currency_rate'] < $current_rate) ? 1 : 0
        ])->execute();
    }*/


    $pricesHistory = (new Query())
        ->where(['product_id' => $model->id])
        ->from('{{%shop__product_price_history}}');
    $prices = $pricesHistory->all();


    /* $currencyHistory = (new Query())
        ->from('{{%shop__currency_history}}');
    $currencies = $currencyHistory->all();
    $seriesCurrency = [];
    $seriesCurrencyList = [];
    foreach ($currencies as $c) {
        $seriesCurrencyList[date('Ymd', $c['created_at'])] = [
            'y' => (float)$c['rate'],
            'value' => (double)$c['rate'],
        ];
    }*/


    // $last = end($seriesCurrencyList);
    $series = [];
    $categories = [];
    $currenciesList = [];

    foreach ($prices as $k => $p) {
        //$series[]=(double) $p['price_purchase'];
        if ($p['currency_id']) {
            $priceValue = (double)$p['price'] * $p['currency_rate'];
        } else {
            $priceValue = (double)$p['price'];
        }
        $parentFlag = false;
        $symbol = 'triangle';
        $marker = [
            'fillColor' => '#c0c0c0',
        ];
        if (isset($prices[$k - 1]['price'])) {
            $parentFlag = true;
            if ($prices[$k - 1]['currency_id']) {
                $parentPriceValue = (double)$prices[$k - 1]['price'] * $prices[$k - 1]['currency_rate'];
            } else {
                $parentPriceValue = (double)$prices[$k - 1]['price'];
            }

            $marker = [
                'fillColor' => ($parentPriceValue > $priceValue) ? 'red' : 'green',
                'symbol' => ($parentPriceValue < $priceValue) ? 'triangle' : 'triangle-down'
            ];
        }
        /*if ($p['event'] == 'discount') {
            $marker = [
                'width' => '16',
                'height' => '16',
                'symbol' => 'url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNTEyLjAzNSA1MTIuMDM1IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMDM1IDUxMi4wMzU7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIHN0eWxlPSJmaWxsOiNGNDQzMzY7IiBkPSJNNDg4LjUwMiwyNTYuMDM1bDIyLjQ2NC01OC41OTJjMi40NjQtNi40NjQsMC41NDQtMTMuNzkyLTQuODY0LTE4LjE3NmwtNDguNzA0LTM5LjQ4OGwtOS44NTYtNjEuOTg0DQoJYy0xLjA4OC02Ljg0OC02LjQ2NC0xMi4xOTItMTMuMzEyLTEzLjI4bC02MS45ODQtOS44NTZMMzMyLjc5LDUuOTIzYy00LjM1Mi01LjQwOC0xMS44NC03LjMyOC0xOC4xNDQtNC44NjRsLTU4LjYyNCwyMi40OTYNCglMMTk3LjQzLDEuMDkxYy02LjQ5Ni0yLjQ5Ni0xMy43Ni0wLjUxMi0xOC4xNDQsNC44NjRsLTM5LjQ4OCw0OC43MzZsLTYxLjk4NCw5Ljg1NmMtNi44MTYsMS4wODgtMTIuMTkyLDYuNDY0LTEzLjI4LDEzLjI4DQoJbC05Ljg1Niw2MS45ODRMNS45NDIsMTc5LjI5OWMtNS4zNzYsNC4zNTItNy4zMjgsMTEuNjgtNC44NjQsMTguMTQ0bDIyLjQ2NCw1OC41OTJMMS4wNzgsMzE0LjYyNw0KCWMtMi40OTYsNi40NjQtMC41MTIsMTMuNzkyLDQuODY0LDE4LjE0NGw0OC43MzYsMzkuNDU2bDkuODU2LDYxLjk4NGMxLjA4OCw2Ljg0OCw2LjQzMiwxMi4yMjQsMTMuMjgsMTMuMzEybDYxLjk4NCw5Ljg1Ng0KCWwzOS40ODgsNDguNzA0YzQuMzg0LDUuNDQsMTEuNzEyLDcuMzYsMTguMTc2LDQuODY0bDU4LjU2LTIyLjQzMmw1OC41OTIsMjIuNDY0YzEuODU2LDAuNzA0LDMuNzc2LDEuMDU2LDUuNzI4LDEuMDU2DQoJYzQuNzA0LDAsOS4zNDQtMi4wOCwxMi40NDgtNS45NTJsMzkuNDU2LTQ4LjcwNGw2MS45ODQtOS44NTZjNi44NDgtMS4wODgsMTIuMjI0LTYuNDY0LDEzLjMxMi0xMy4zMTJsOS44NTYtNjEuOTg0bDQ4LjcwNC0zOS40NTYNCgljNS40MDgtNC4zODQsNy4zMjgtMTEuNjgsNC44NjQtMTguMTQ0TDQ4OC41MDIsMjU2LjAzNXoiLz4NCjxnPg0KCTxwYXRoIHN0eWxlPSJmaWxsOiNGQUZBRkE7IiBkPSJNMjA4LjAyMiwyMjQuMDM1Yy0yNi40NjQsMC00OC0yMS41MzYtNDgtNDhzMjEuNTM2LTQ4LDQ4LTQ4czQ4LDIxLjUzNiw0OCw0OA0KCQlTMjM0LjQ4NiwyMjQuMDM1LDIwOC4wMjIsMjI0LjAzNXogTTIwOC4wMjIsMTYwLjAzNWMtOC44MzIsMC0xNiw3LjE2OC0xNiwxNnM3LjE2OCwxNiwxNiwxNnMxNi03LjE2OCwxNi0xNg0KCQlTMjE2Ljg1NCwxNjAuMDM1LDIwOC4wMjIsMTYwLjAzNXoiLz4NCgk8cGF0aCBzdHlsZT0iZmlsbDojRkFGQUZBOyIgZD0iTTMwNC4wMjIsMzg0LjAzNWMtMjYuNDY0LDAtNDgtMjEuNTM2LTQ4LTQ4czIxLjUzNi00OCw0OC00OHM0OCwyMS41MzYsNDgsNDgNCgkJUzMzMC40ODYsMzg0LjAzNSwzMDQuMDIyLDM4NC4wMzV6IE0zMDQuMDIyLDMyMC4wMzVjLTguOCwwLTE2LDcuMi0xNiwxNnM3LjIsMTYsMTYsMTZzMTYtNy4yLDE2LTE2DQoJCVMzMTIuODIyLDMyMC4wMzUsMzA0LjAyMiwzMjAuMDM1eiIvPg0KCTxwYXRoIHN0eWxlPSJmaWxsOiNGQUZBRkE7IiBkPSJNMTc2LjAyMiwzODQuMDM1Yy0zLjIzMiwwLTYuNDY0LTAuOTYtOS4yOC0yLjk3NmMtNy4yLTUuMTUyLTguODY0LTE1LjEzNi0zLjcxMi0yMi4zMzZsMTYwLTIyNA0KCQljNS4xNTItNy4yLDE1LjEzNi04Ljg2NCwyMi4zMzYtMy43MTJjNy4yLDUuMTIsOC44MzIsMTUuMTM2LDMuNzEyLDIyLjMwNGwtMTYwLDIyNA0KCQlDMTg1LjkxLDM4MS42OTksMTgxLjAxNCwzODQuMDM1LDE3Ni4wMjIsMzg0LjAzNXoiLz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K)'
            ];
        } elseif ($p['event'] == 'product_currency') {
            $marker = [
                'fillColor' => '#c0c0c0',
                'width' => '16',
                'height' => '16',
                'symbol' => 'url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjM5NC42NTNweCIgaGVpZ2h0PSIzOTQuNjUzcHgiIHZpZXdCb3g9IjAgMCAzOTQuNjUzIDM5NC42NTMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDM5NC42NTMgMzk0LjY1MzsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxnPg0KCQkJPHBhdGggZD0iTTE4MC4yNTYsMjUwLjk5MmMtOS41OTYtMi41ODQtMTguMTg2LTcuMzEyLTI0LjE4OC0xMy4zMWMtMi41OTgtMi41OTMtNi4wNTEtNC4wMjEtOS43MjMtNC4wMjENCgkJCQljLTMuNjcyLDAtNy4xMjUsMS40MzEtOS43MjUsNC4wMjZsLTEuMDUxLDEuMDVjLTIuNTksMi41ODctNC4wNTksNi4xNzMtNC4wMzMsOS44NGMwLjAyOSwzLjY2MSwxLjU1NSw3LjIyNCw0LjE4OCw5Ljc3NA0KCQkJCWMxMS43MTMsMTEuMzQ2LDI4LjI1OCwxOS41NDksNDUuMzkzLDIyLjUwNGwxLjY2LDAuMjg3djEyLjk4N2MwLDcuNTg0LDYuMTcsMTMuNzU2LDEzLjc1NiwxMy43NTZoMS41NTkNCgkJCQljNy41ODQsMCwxMy43NTQtNi4xNzIsMTMuNzU0LTEzLjc1NlYyODAuODRsMS42MjctMC4zMDljMjguNDQxLTUuNDE1LDQ1LjQyNC0yMy4xOTEsNDUuNDI0LTQ3LjUzMw0KCQkJCWMwLjI0OC0zMy4xMDMtMjQuMjcxLTQ1LjE4Ny00NS42NTYtNTIuMDE2bC0xLjM5NS0wLjQ0NFYxMzUuOTVsMi40MywwLjUzMWM2LjMwNSwxLjM3OSwxMS45MzQsMy40ODYsMTYuMjc5LDYuMDk2DQoJCQkJYzIuMTQxLDEuMjgyLDQuNTgyLDEuOTU5LDcuMDY0LDEuOTU5YzQuNzgxLDAsOS4xNDUtMi40MTcsMTEuNjc2LTYuNDY3bDAuNjk3LTEuMTE3YzEuOTM4LTMuMTAyLDIuNTYyLTYuOTIsMS43MTUtMTAuNDczDQoJCQkJYy0wLjg0OC0zLjU1OS0zLjEyOS02LjY4Ny02LjI1OC04LjU4MWMtOC43NjgtNS4zMDUtMjAuMDk4LTkuMjIyLTMxLjkwNi0xMS4wMjhsLTEuNjk3LTAuMjZ2LTkuODINCgkJCQljMC03LjU4NS02LjE3LTEzLjc1Ny0xMy43NTQtMTMuNzU3aC0xLjU1OWMtNy41ODYsMC0xMy43NTYsNi4xNzEtMTMuNzU2LDEzLjc1N3YxMC41NDhsLTEuNjA1LDAuMzI0DQoJCQkJYy0yNi4yODMsNS4zMDEtNDEuOTc1LDIxLjc5MS00MS45NzUsNDQuMTA5YzAsMjMuODg5LDEzLjgxMSw0MC4xODIsNDIuMjIzLDQ5LjgxMmwxLjM1NywwLjQ2djQ5LjYyNkwxODAuMjU2LDI1MC45OTJ6DQoJCQkJIE0yMTEuODQ2LDIxMS4zM2wyLjgwNywxLjIyOWMxMS4xNDYsNC44OTEsMTUuMjYyLDEwLjM3NSwxNS4xNzYsMjAuMjYzYzAsOC4yMzUtNS4xNjYsMTQuMTA3LTE1LjM1NSwxNy40NTZsLTIuNjI1LDAuODYNCgkJCQlMMjExLjg0NiwyMTEuMzNMMjExLjg0NiwyMTEuMzN6IE0xNzkuODMzLDE2OS4yNjZjLTguMzI2LTQuNDQ4LTExLjU3LTkuMzUzLTExLjU3LTE3LjQ5NGMwLTYuMTg4LDMuOTc5LTEwLjc5MiwxMS44MjItMTMuNjg0DQoJCQkJbDIuNjkxLTAuOTkzdjMzLjc0MkwxNzkuODMzLDE2OS4yNjZ6Ii8+DQoJCQk8cGF0aCBkPSJNMzg5LjgzNCw3NS44NThjLTQuMDU1LTMuMjQ0LTkuNjY0LTMuNzI2LTE0LjIxNS0xLjIyMWwtMjcuMDI1LDE0Ljg4MkMzMTQuOTY5LDM3Ljg1MywyNTcuNTMxLDYuMzI3LDE5NS4yMTcsNi4zMjcNCgkJCQljLTEwMC45MDYsMC0xODMsODIuMDkzLTE4MywxODNjMCwxMS4wNDYsOC45NTUsMjAsMjAsMjBjMTEuMDQ3LDAsMjAtOC45NTQsMjAtMjBjMC03OC44NTEsNjQuMTUtMTQzLDE0My0xNDMNCgkJCQljNDcuNjkzLDAsOTEuNzQ4LDIzLjYyOSwxMTguMjM0LDYyLjU0M2wtMjUuMzE0LDEzLjkzOWMtNC41NDksMi41MDUtNy4xNDEsNy41MDMtNi41NjQsMTIuNjY1DQoJCQkJYzAuNTcxLDUuMTYxLDQuMTk5LDkuNDY5LDkuMTg4LDEwLjkxNGw2Ny44MjgsMTkuNjU1YzEuMTcyLDAuMzQsMi4zNzMsMC41MDcsMy41NzIsMC41MDdjMi4xNDYsMCw0LjI3OS0wLjUzOCw2LjE5Mi0xLjU5Mg0KCQkJCWMyLjk4MS0xLjY0Myw1LjE5LTQuNDAzLDYuMTQtNy42NzNsMTkuNjU0LTY3LjgyOEMzOTUuNTksODQuNDY5LDM5My44ODksNzkuMTAyLDM4OS44MzQsNzUuODU4eiIvPg0KCQkJPHBhdGggZD0iTTM2Mi40MzYsMTg1LjMyN2MtMTEuMDQ3LDAtMjAsOC45NTQtMjAsMjBjMCw3OC44NTItNjQuMTQ5LDE0My0xNDMuMDAxLDE0M2MtNDcuNjkyLDAtOTEuNzQ4LTIzLjYyOS0xMTguMjMzLTYyLjU0Mw0KCQkJCWwyNS4zMTQtMTMuOTM5YzQuNTQ5LTIuNTA0LDcuMTQxLTcuNTAyLDYuNTY2LTEyLjY2NGMtMC41NzItNS4xNjEtNC4xOTktOS40Ny05LjE4OC0xMC45MTNsLTY3LjgyNy0xOS42NTUNCgkJCQljLTEuMTcyLTAuMzQxLTIuMzczLTAuNTA3LTMuNTcyLTAuNTA3Yy0yLjE0NiwwLTQuMjc5LDAuNTM3LTYuMTkzLDEuNTkyYy0yLjk4MiwxLjY0My01LjE5MSw0LjQwMi02LjEzOSw3LjY3M0wwLjUwOCwzMDUuMTk4DQoJCQkJYy0xLjQ0NSw0Ljk4NywwLjI1NiwxMC4zNTQsNC4zMTEsMTMuNTk5YzQuMDU1LDMuMjQ0LDkuNjY0LDMuNzI4LDE0LjIxNSwxLjIyMmwyNy4wMjUtMTQuODgyDQoJCQkJYzMzLjYyNCw1MS42NjQsOTEuMDYyLDgzLjE5LDE1My4zNzUsODMuMTljMTAwLjkwNiwwLDE4My4wMDEtODIuMDkzLDE4My4wMDEtMTgzDQoJCQkJQzM4Mi40MzYsMTk0LjI4MSwzNzMuNDgsMTg1LjMyNywzNjIuNDM2LDE4NS4zMjd6Ii8+DQoJCTwvZz4NCgk8L2c+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==)'
            ];
        }*/

        $series[] = [
            'name' => $p['event'],
            'value' => Yii::$app->currency->number_format($priceValue) . ' грн.',
            'y' => $priceValue,
            'marker' => $marker
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
                        'format' => '{point.value:.2f}'
                    ]
                ],

            ],
            'tooltip' => [
                'enabled' => true,
                'headerFormat' => '<table border="1">',
                'pointFormat' => '<tr><td><span style="font-size:11px">{series.name}</span></td></tr><span style="color:{point.color}">{point.name}</span>: <strong>{point.value:.2f} грн.</strong>',
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
                        'pointFormat' => '<tr><td><span style="font-weight: bold; color: {series.color}">{series.name} ({point.name})</span>: {point.value:.2f}</td></tr>'
                    ],
                    'data' => $series
                ],
            ],
        ]
    ]);

}