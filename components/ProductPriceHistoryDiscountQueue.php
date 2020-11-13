<?php

namespace panix\mod\shop\components;

use panix\mod\shop\models\Product;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use Yii;

class ProductPriceHistoryDiscountQueue extends BaseObject implements JobInterface
{
    public $items;
    public $type = 0;
    public $value = 0;
    public $q_event;

    public function execute($queue)
    {

        $command = Product::getDb()->createCommand();
        $data = [];

        //  $items = Product::find()->where(['id'=>$this->items])->all();
        $items = Product::findAll(['id' => $this->items]);
        //  print_r($items);die;
        foreach ($items as $item) {
            // echo $item . PHP_EOL;


            // if ($item->hasMarkup) {

            // } else {
            //     $price = $item->price - $item->price * ((double)$this->value) / 100;
            // }
            if ($this->q_event == 'switch_off') {
                $price = $item->price;
            } else {
                $price = $item->price - $item->price * ((double)$this->value) / 100;
            }

            if ($this->q_event == 'switch_off') {
                // $price = $item->price_purchase;
            } else {
                // $price = $item->price_purchase - $item->price_purchase * ((double)$this->markup) / 100;
            }

            $data[] = [
                'product_id' => $item->id,
                'currency_id' => ($item->currency_id) ? $item->currency_id : NULL,
                'currency_rate' => ($item->currency_id) ? Yii::$app->currency->currencies[$item->currency_id]['rate'] : NULL,
                'price' => $price,
                'price_purchase' => $item->price_purchase,
                'created_at' => time(),
                'type' => $this->type,
                'event' => 'discount'
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