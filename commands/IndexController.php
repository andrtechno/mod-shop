<?php

namespace panix\mod\shop\commands;

use Yii;
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
        $ns = 'http://base.google.com/ns/1.0';
        $xml = new SimpleXMLExtended('<rss charset="utf-8" xmlns:g="' . $ns . '" version="2.0" />', 0, false, "g", 'http://base.google.com/ns/1.0');
        $channel = $xml->addChild('channel');


        $channel->addChildWithCDATA('link', "https://ultradruk.com");
        $channel->addChildWithCDATA('title', "name");
        $channel->addChildWithCDATA('description', "no");
        $products = Product::find()
            ->limit(10)
            ->where(['switch' => 1])
            //->andWhere(['availability'=>1])
            ->all();
        foreach ($products as $i => $product) {
            $item = $channel->addChild('item');
            $item->addChild('g:id', $product->id, $ns);
            $item->addChildWithCDATA('g:title', $product->name, $ns);
            $item->addChildWithCDATA('g:description', $product->full_description, $ns);


            $item->addChild('g:link', Url::to($product->getUrl(), true), $ns);
            $item->addChild('g:image_link', Url::to($product->getImageData('500x500')->url, true), $ns);
            $item->addChild('g:price', $product->price . " UAH", $ns);
            $item->addChild('g:condition', "new", $ns);
            $item->addChild('g:availability', "in stock", $ns);
            $item->addChild('g:adult', "no", $ns);
            $item->addChild('g:identifier_exists', "yes", $ns);

            $item->addChild('g:google_product_category', "yes", $ns);

            foreach ($product->getDataAttributes() as $data) {
                foreach ($data as $key => $attribute) {
                    $item->addChild($key, 'привет', $ns);//$attribute['value']
                }
            }
        }

        return $xml->saveXML(Yii::getAlias('@runtime').DIRECTORY_SEPARATOR.'google-feed.xml');
    }


}
