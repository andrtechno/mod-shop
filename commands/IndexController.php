<?php

namespace panix\mod\shop\commands;

use panix\engine\CMS;
use panix\mod\images\models\Image;
use Yii;
use yii\helpers\Console;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use panix\mod\shop\components\SimpleXMLExtended;
use panix\mod\shop\models\Product;
use panix\engine\console\controllers\ConsoleController;


/**
 * IndexController
 * @package panix\mod\shop\commands
 */
class IndexController extends ConsoleController
{

    /**
     * Generate google feed XML.
     * @param string $host Host for url
     * @param integer $minify Minimization file content: use 1 or 0
     * @param string $path Alias path save file: default '@runtime'
     * @return mixed
     */
    public function actionGoogleFeed($host = 'no-host.com', $minify = 1, $path = '@runtime')
    {
        //https://support.google.com/merchants/answer/6324484?hl=ru
        $ns = 'http://base.google.com/ns/1.0';
        $fullURL = 'https://' . $host;
        $xml = new SimpleXMLExtended('<rss xmlns:g="' . $ns . '" version="2.0" />');

        $main_iso = Yii::$app->currency->main['iso'];
        $currencies = Yii::$app->currency->getCurrencies();

        $channel = $xml->addChild('channel');

        $channel->addChildWithCDATA('link', $fullURL);
        $channel->addChildWithCDATA('title', Yii::$app->settings->get('app', 'sitename'));
        $channel->addChildWithCDATA('description', Yii::$app->settings->get('app', 'sitename'));
        $products = Product::find()
            //->limit(50)
             ->where(['switch' => 1])
            //->andWhere(['use_configurations' => 1])
            //->andWhere(['availability'=>1])
            //->andWhere(['id' => [9087,9086,9088]])
            ->all();
        $count = count($products);
        $i = 0;
        Console::startProgress($i, $count);

        foreach ($products as $i => $product) {

            /** @var Product $product */
            $item = $channel->addChild('item');
            $item->addChild('id', $product->id, $ns);
            $item->addChildWithCDATA('title', StringHelper::truncate($product->name, 150 - 3), $ns);
            $item->addChildWithCDATA('description', StringHelper::truncate($product->full_description, 5000 - 3), $ns);


            $item->addChild('link', $fullURL . Url::to($product->getUrl()), $ns);


            $images = Image::find()
                ->where(['object_id' => $product->id, 'handler_hash' => $product->getHash()])
                ->all();
            foreach ($images as $image) {
                if (file_exists(Yii::getAlias($image->path) . DIRECTORY_SEPARATOR . $product->id.DIRECTORY_SEPARATOR.$image->filePath)) {
                    if ($image->is_main) {
                        $item->addChild('image_link', $fullURL . '/uploads/store/product/'.$product->id.'/' . $image->filePath, $ns);
                    } else {
                        $item->addChild('additional_image_link', $fullURL . '/uploads/store/product/'.$product->id.'/' . $image->filePath, $ns);
                    }
                }
            }


            $custom_label = 0;
            foreach ($product->labels() as $key => $label) {
                $item->addChild('custom_label_' . $custom_label, $label['value'], $ns);
                $custom_label++;
            }

            //$priceValue = '';
            //$priceSymbol = '';
            if ($product->currency_id) {
                $priceValue = $product->price * $currencies[$product->currency_id]['rate'];
            } else {
                $priceValue = $product->price;
            }


            if ($product->hasDiscount) {
                $item->addChild('price_sale', number_format((($product->currency_id) ? $product->discountPrice * $currencies[$product->currency_id]['rate'] : $product->discountPrice),2,'.','') . " " . $main_iso, $ns);

                if (isset($product->discountEndDate)) {
                    //date('Y-m-d\TH:i:sO');
                    //$item->addChild('sale_price_effective_date', "2016-02-24T13:00-0800/2016-02-29T15:30-0800", $ns);
                }

            }


            /*if ($product->discount) {
                $sum = $product->discount;
                if ('%' === substr($sum, -1, 1)) {
                    $sum = $priceValue * ((double)$sum) / 100;
                }
                $discountPrice = $priceValue - $sum;
                $item->addChild('price_sale', (($product->currency_id) ? $discountPrice * $currencies[$product->currency_id]['rate'] : $discountPrice) . " " . $main_iso, $ns);

                if (isset($product->discountEndDate)) {
                    //date('Y-m-d\TH:i:sO');
                    //$item->addChild('sale_price_effective_date', "2016-02-24T13:00-0800/2016-02-29T15:30-0800", $ns);
                }

            }*/



            $item->addChild('price', number_format($priceValue,2,'.','') . " " . $main_iso, $ns);
            $item->addChild('condition', "new", $ns);


            if ($product->availability == 1) { //Есть в наличии
                $item->addChild('availability', "in_stock", $ns);
            } elseif ($product->availability == 3) { //Нет в наличии
                $item->addChild('availability', "out_of_stock", $ns);
            } elseif ($product->availability == 2) { //предзаказ
                $item->addChild('availability', "preorder", $ns);
            }


            //$item->addChild('g:adult', "no", $ns);
            //$item->addChild('g:identifier_exists', "yes", $ns);
            if ($product->manufacturer_id) {
                $brand = $product->manufacturer;
                if ($brand) {
                    $item->addChild('brand', StringHelper::truncate($brand->name, 70 - 3), $ns);
                }
            }

            //Bonus program
            $loyalty_points = $item->addChild('loyalty_points', null, $ns);
            $loyalty_points->addChild('name', StringHelper::truncate("Бонусы", 15,''), $ns);
            $loyalty_points->addChild('points_value', floor($priceValue * Yii::$app->settings->get('user', 'bonus_ratio')), $ns);
            $loyalty_points->addChild('ratio', Yii::$app->settings->get('user', 'bonus_ratio'), $ns);


            //Доставка
            /*$shipping = $item->addChild('shipping', null, $ns);
            $shipping->addChild('country', "US", $ns);
            $shipping->addChild('region', "MA", $ns);
            $shipping->addChild('service', "Наземная доставка", $ns);
            $shipping->addChild('price', "6.49 USD", $ns);
            $shipping = $item->addChild('shipping', null, $ns);
            $shipping->addChild('country', "UA", $ns);
            $shipping->addChild('region', "UA", $ns);
            $shipping->addChild('postal_code', 65000, $ns);
            $shipping->addChild('service', "Новая почта", $ns);
            $shipping->addChild('price', "65.00 UAH", $ns);*/

            $configuration = $product->getConfigurations(true);
            if ($configuration) {
                    sort($configuration); //generate unique hash configuration
                    $item->addChild('item_group_id', CMS::hash(implode('-', $configuration)), $ns);
            }


            $item->addChild('ships_from_country', 'UA', $ns);
            foreach ($product->getDataAttributes() as $data) {
                foreach ($data as $key => $attribute) {
                    $product_detail = $item->addChild('product_detail', null, $ns);
                    //$product_detail->addChild('section_name', 'Общие сведения', $ns);
                    $product_detail->addChild('attribute_name', $attribute['name'], $ns);
                    $product_detail->addChild('attribute_value', $attribute['value'], $ns);
                }

            }
            $i++;
            Console::updateProgress($i, $count);

        }
        Console::endProgress(false);
        // $xml = html_entity_decode($xml, ENT_NOQUOTES, 'UTF-8');

        $dom = new \DOMDocument();
        $dom->loadXML($xml->asXML());
        $dom->encoding = 'UTF-8';
        if (!$minify) {
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
        }
        $xml = $dom->saveXML();
        $xml = new SimpleXMLExtended($xml, 0, false, "g", 'http://base.google.com/ns/1.0');

        return $xml->saveXML(Yii::getAlias($path) . DIRECTORY_SEPARATOR . 'google-feed.xml');
    }

}
