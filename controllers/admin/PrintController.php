<?php

namespace panix\mod\shop\controllers\admin;

use Mpdf\Mpdf;
use panix\engine\CMS;
use panix\mod\images\models\Image;
use panix\mod\shop\components\SimpleXMLExtended;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Product;
use Yii;
use panix\engine\controllers\AdminController;
use yii\filters\ContentNegotiator;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\XmlResponseFormatter;

class PrintController extends AdminController
{

    public $icon = 'print';


    public function actionIndex()
    {
        $mpdf = new Mpdf([
            // 'debug' => true,
            'mode' => 'utf-8',
            'default_font_size' => 9,
            'default_font' => 'verdana',
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_footer' => 0,
            'margin_header' => 0,
            'format' => 'A4',
            //  'autoPageBreak' => false
        ]);
        //$mpdf->mirrorMargins = true;
        // $mpdf->autoPageBreak = false;
        //  $mpdf->SetDisplayMode('real', 'two');
        $mpdf->SetCreator(Yii::$app->name);
        $mpdf->SetAuthor(Yii::$app->user->getDisplayName());

        //$mpdf->SetProtection(['copy','print'], 'asdsad', 'MyPassword');
        //$mpdf->SetTitle('asdasd');
        // $mpdf->AddPage();
        $mpdf->WriteHTML(file_get_contents(Yii::getAlias('@shop/views/admin/print/test.css')), 1);
        //$mpdf->WriteHTML($this->renderPartial('_pdf_order', ['model' => $model]), 2);
        $mpdf->WriteHTML($this->renderPartial('index', []), 2);
        echo $mpdf->Output("sdadasd.pdf", 'I');
        die;
    }

    public function actionTermo()
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [58, 40],
            'margin_top' => 1,
            'margin_bottom' => 0,
            'margin_left' => 1,
            'margin_right' => 1,
            'margin_footer' => 0,
            'margin_header' => 0,
            'mirrorMargins' => false,
            'extrapagebreak' => false,
        ]);
        // $mpdf->extrapagebreak=false;
        $pr = \panix\mod\shop\models\Product::find()->limit(15)->all();
        foreach ($pr as $k => $p) {
            $mpdf->AddPage();
            // $mpdf->WriteHTML('<pagebreak sheet-size="58mm 40mm" />');
            $mpdf->WriteHTML($this->renderPartial('_termo', ['product' => $p]));
        }


        $mpdf->Output();
        die;
    }

    public function behaviors()
    {
        return [
            [
                'class' => ContentNegotiator::class,
                'only' => ['xml-google2'],
                'formats' => [
                    'text/xml' => Response::FORMAT_XML,
                ],
                /* 'response' => [
                     'class' => Response::class,
                     'formatters' => [
                         Response::FORMAT_XML => [
                             'class' => XmlResponseFormatter::className(),
                             'rootTag' => 'sssssss'
                         ]
                     ],

                 ]*/
            ],
        ];
    }

    public function actionXmlGoogle()
    {
        //https://support.google.com/merchants/answer/6324497?hl=ru&ref_topic=6324338
        $test = [];

        $ns = 'http://base.google.com/ns/1.0';
        $xml = new SimpleXMLExtended('<rss xmlns:g="' . $ns . '" version="2.0" />', 0, false, "g", 'http://base.google.com/ns/1.0');
        $channel = $xml->addChild('channel');


        $channel->addChildWithCDATA('link', 'https://' . Yii::$app->request->serverName);
        $channel->addChildWithCDATA('title', Yii::$app->settings->get('app', 'sitename'));
        $channel->addChildWithCDATA('description', Yii::$app->settings->get('app', 'sitename'));
        $products = Product::find()
            ->limit(10)
            ->where(['switch' => 1])
            ->andWhere(['use_configurations' => 1])
            //->andWhere(['availability'=>1])
            ->all();
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


            $item->addChild('price', $product->getFrontPrice() . " UAH", $ns);
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
            $loyalty_points->addChild('points_value', $product->getFrontPrice() * Yii::$app->settings->get('user', 'bonus_ratio'), $ns);
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
        }

        /*Yii::$app->response->formatters = [
            Response::FORMAT_XML => [
                'class' => XmlResponseFormatter::class,
                'rootTag' => false,
                //'itemTag' => false,
                //'useObjectTags' => true,
                //'useTraversableAsArray'=>true
            ]
        ];
        Yii::$app->response->format = Response::FORMAT_XML;*/
        $test['rss'] = 'version="2.0" xmlns:g="http://base.google.com/ns/1.0"';
        Yii::$app->response->format = Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;

        $headers->set('Content-Type', 'text/xml');

        return $xml->asXML();
    }
}
