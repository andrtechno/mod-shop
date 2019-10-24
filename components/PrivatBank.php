<?php

namespace panix\mod\shop\components;

use yii\httpclient\Client;

class PrivatBank
{

    public static function exchange()
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('https://api.privatbank.ua/p24api/exchange_rates')
            ->setData(['json' => 'true', 'date' => date('d.m.Y', time())])
            ->send();
        if ($response->isOk) {
            return $response->data;
        } else {
            return $response;
        }
    }
}