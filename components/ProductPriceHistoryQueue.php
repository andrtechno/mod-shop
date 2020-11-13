<?php

namespace panix\mod\shop\components;

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
        $data=[];
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
            $data[]=[
                'product_id' => $item['id'],
                'currency_id' => $this->currency_id,
                'currency_rate' => $this->currency_rate,
                'price' => $item['price'],
                'price_purchase' => $item['price_purchase'],
                'created_at' => time(),
                'type' => $this->type,
                'event'=>'currency'
            ];
        }



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



        echo basename(__CLASS__).' done!' . PHP_EOL;
        return true;
    }
}