<?php

namespace panix\mod\shop\commands;

use panix\engine\CMS;
use panix\mod\discounts\models\Discount;
use panix\mod\shop\models\Currency;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductCategoryRef;
use Yii;
use panix\engine\console\controllers\ConsoleController;
use yii\helpers\Console;
use yii\httpclient\Client;


/**
 * Currency updater
 * @package panix\mod\shop\commands
 */
class CurrencyController extends ConsoleController
{

    /**
     * Update currencies by NB
     */
    public function actionNb()
    {
        $client = new Client(['baseUrl' => 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json']);
        $response = $client->createRequest()
            ->setMethod('GET')
            ->send();

        if ($response->isOk) {
            $list = $response->data;
        } else {
            $list = false;
        }
        if ($list) {
            foreach ($list as $currency) {
                $currencyModel = Currency::findOne(['iso' => $currency['cc'], 'switch' => 1]);
                if ($currencyModel) {
                    $currencyModel->rate = $currency['rate'];
                    $currencyModel->save(false);
                    echo $this->action->id.' Update currency rate: ' . $this->ansiFormat($currency['cc'], Console::FG_GREEN) . ' ' . $this->ansiFormat($currency['rate'], Console::FG_PURPLE) . PHP_EOL;
                }
            }
        }
    }


    /**
     * Update currencies by Privat bank
     */
    public function actionPb()
    {
        $client = new Client(['baseUrl' => 'https://api.privatbank.ua/p24api/pubinfo?json&exchange']);

        $response = $client->createRequest()
            ->setMethod('GET')
            ->setData([
                'coursid' => 5,
            ])
            ->send();

        if ($response->isOk) {
            $list = $response->data;
        } else {
            $list = false;
        }
        if ($list) {
            foreach ($list as $currency) {
                $currencyModel = Currency::findOne(['iso' => $currency['ccy'], 'switch' => 1]);
                if ($currencyModel) {
                    $currencyModel->rate = $currency['sale'];
                    $currencyModel->save(false);
                    echo $this->action->id.' Update currency rate: ' . $this->ansiFormat($currency['ccy'], Console::FG_GREEN) . ' ' . $this->ansiFormat($currency['sale'], Console::FG_PURPLE) . PHP_EOL;
                }
            }
        }
    }


}
