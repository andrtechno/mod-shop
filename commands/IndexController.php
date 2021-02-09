<?php

namespace panix\mod\shop\commands;

use panix\mod\images\models\Image;
use Yii;
use yii\helpers\Console;
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
     * generate google feed XML
     */
    public function actionGoogleFeed()
    {
        //https://support.google.com/merchants/answer/6324484?hl=ru&ref_topic=6324338
        $ns = 'http://base.google.com/ns/1.0';
        $xml = new SimpleXMLExtended('<rss xmlns:g="' . $ns . '" version="2.0" />', 0, false, "g", 'http://base.google.com/ns/1.0');
        $channel = $xml->addChild('channel');


        $channel->addChildWithCDATA('link', 'https://chika.in.ua');
        $channel->addChildWithCDATA('title', Yii::$app->settings->get('app', 'sitename'));
        $channel->addChildWithCDATA('description', Yii::$app->settings->get('app', 'sitename'));
        $products = Product::find()
           // ->limit(100)
           // ->where(['switch' => 1])
            //->andWhere(['use_configurations' => 1])
            //->andWhere(['availability'=>1])
            ->all();
        $count=count($products);
        $i=0;
        Console::startProgress($i, $count);

        foreach ($products as $i => $product) {
            $item = $channel->addChild('item');
            $item->addChild('id', $product->id, $ns);
            $item->addChildWithCDATA('title', $product->name, $ns);
            $item->addChildWithCDATA('description', $product->full_description, $ns);


            $item->addChild('link', Url::to($product->getUrl(), true), $ns);


            $images = Image::find()
                ->where(['object_id' => $product->id, 'handler_hash' => $product->getHash()])
                ->all();

            foreach ($images as $image) {

                if (file_exists(Yii::getAlias($image->path) . DIRECTORY_SEPARATOR . $image->filePath)) {
                    if ($image->is_main) {
                        $item->addChild('image_link', Url::to('/uploads/store/product/' . $image->filePath, true), $ns);
                    } else {
                        $item->addChild('additional_image_link', '/uploads/store/product/' . $image->filePath, $ns);
                    }
                }

            }


            $item->addChild('price', $product->price . " UAH", $ns);
            $item->addChild('condition', "new", $ns);


            //in_stock [в_наличии]
            //out_of_stock [нет_в_наличии]
            //preorder [предзаказ]
            if ($product->availability == 1) { //Есть в наличии
                $item->addChild('availability', "in_stock", $ns);
            } elseif ($product->availability == 3) { //Нет в наличии
                $item->addChild('gavailability', "out_of_stock", $ns);
            } elseif ($product->availability == 2) {
                $item->addChild('availability', "preorder", $ns);
            }

            //$item->addChild('g:adult', "no", $ns);
            //$item->addChild('g:identifier_exists', "yes", $ns);
            if ($product->manufacturer_id) {
                $brand = $product->manufacturer;
                if ($brand) {
                    $item->addChild('brand', $brand->name, $ns);
                }
            }

            //Bonus program
            $loyalty_points = $item->addChild('loyalty_points', null, $ns);
            $loyalty_points->addChild('name', "Бонусная программа", $ns);
            $loyalty_points->addChild('points_value', $product->price * Yii::$app->settings->get('user', 'bonus_ratio'), $ns);
            $loyalty_points->addChild('ratio', Yii::$app->settings->get('user', 'bonus_ratio'), $ns);


            //Доставка
            $shipping = $item->addChild('shipping', null, $ns);
            $shipping->addChild('country', "US", $ns);
            $shipping->addChild('region', "MA", $ns);
            $shipping->addChild('service', "Наземная доставка", $ns);
            $shipping->addChild('price', "6.49 USD", $ns);
            $shipping = $item->addChild('shipping', null, $ns);
            $shipping->addChild('country', "UA", $ns);
            $shipping->addChild('region', "UA", $ns);
            $shipping->addChild('postal_code', 65000, $ns);
            $shipping->addChild('service', "Новая почта", $ns);
            $shipping->addChild('price', "65.00 UAH", $ns);


            if ($product->use_configurations) {
                $configuration = $product->getConfigurations(true);
                if ($configuration) {
                    sort($configuration); //generate unique hash configuration
                    $item->addChild('item_group_id', implode('-', $configuration), $ns);
                }
            }


            $item->addChild('ships_from_country', 'UA', $ns);


            foreach ($product->getDataAttributes() as $data) {
                foreach ($data as $key => $attribute) {
                    $item->addChild($key, $attribute['value'], $ns);
                }

            }
            $i++;
            Console::updateProgress($i, $count);

        }
        Console::endProgress(false);
        return $xml->saveXML(Yii::getAlias('@runtime').DIRECTORY_SEPARATOR.'google-feed.xml');
    }


}
