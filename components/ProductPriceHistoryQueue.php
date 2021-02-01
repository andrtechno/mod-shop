<?php

namespace panix\mod\shop\components;

use Yii;
use panix\mod\shop\models\Product;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ProductPriceHistoryQueue extends BaseObject implements JobInterface
{
    public $items;
    public $currency_id;
    public $currency_rate;
    public $type;

    public function execute($queue)
    {
        // print_r($this->currency->oldAttributes);
//print_r($this->currency->attributes);die;
        $command = Product::getDb()->createCommand();
        $data = [];
        $settings_key = 'QUEUE_CHANNEL_default';
        $count = count($this->items);
        $totoc = (int)Yii::$app->settings->get('app', $settings_key);
        echo $totoc.PHP_EOL;
        echo $count.PHP_EOL;
        foreach ($this->items as $item) {
            /*$command->insert('{{%shop__product_price_history}}', [
                'product_id' => $item['id'],
                'currency_id' => $this->currency_id,
                'currency_rate' => $this->currency_rate,
                'price' => $item['price'],
                'price_purchase' => $item['price_purchase'],
                'created_at' => time(),
                'type' => $this->type
            ])->execute();*/
            $data[] = [
                'product_id' => $item['id'],
                'currency_id' => $this->currency_id,
                'currency_rate' => $this->currency_rate,
                'price' => $item['price'],
                'price_purchase' => $item['price_purchase'],
                'created_at' => time(),
                'type' => $this->type,
                'event' => 'currency'
            ];
        }
        Yii::$app->settings->set('app', [$settings_key => ($count - $totoc)]);
        echo ($count - $totoc).PHP_EOL;
        echo ($totoc - $count).PHP_EOL;

        $command->batchInsert('{{%shop__product_price_history}}', [
            'product_id',
            'currency_id',
            'currency_rate',
            'price',
            'price_purchase',
            'created_at',
            'type',
            'event'
        ], $data)->execute();

        if (!Yii::$app->settings->get('app', $settings_key)) {
            echo 'del';
            Yii::$app->settings->delete('app', $settings_key);
        }


        echo basename(__CLASS__) . ' done!' . PHP_EOL;
        return true;
    }
}