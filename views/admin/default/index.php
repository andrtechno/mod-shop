<?php

use app\components\ImgFixerQueue;
use panix\mod\forsage\components\ProductByIdQueue;
use panix\mod\shop\models\ProductImage;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\UploadedFile;
use FtpClient\FtpClient;
use panix\engine\Html;


$client = new \app\components\PromApi('33d6e6625b6104faf872880b117bb4c313cf4161');


//$order = $client->get_product_list();
// echo var_dump($order);
//\panix\engine\CMS::dump($order);

$data['id'] = 1895944445;
$data['price'] = 3333;
$data["presence"] = "available";
$data["status"] = "on_display";
$data["name"] = " 1111asd asdd sa dsad sa";
$data["keywords"] = "tag1, tag2, dddd, dsa dsa";
$data["description"] = "dsad asd ";
$params[] = $data;
\panix\engine\CMS::dump($params);
//$order2 = $client->get_product_edit($params);

$data=[];

$data['url'] = 'https://docs.google.com/spreadsheets/d/15TRwLaWp4Y_yTR0mv6d04uBxcrQaKhpBxSlqZD1X-es';
//$data['url'] = 'https://static.mirodejdy.com.ua/export-products.xlsx';
$data["force_update"] = true;
$data["only_available"] = false;
$data["mark_missing_product_as"] = "none";
$data["updated_fields"] = [
    "price",
    "presence"
];
$params=[];
$params[] = $data;
$import = $client->import_url($data);


\panix\engine\CMS::dump($import);


/*
$client = new \yii\httpclient\Client();

$response = $client->createRequest()
    ->setMethod('POST')
    ->setUrl('https://my.prom.ua/api/v1/products/edit')
    ->setData($data)
    ->setHeaders(['content-type' => 'application/json'])
    ->addHeaders(['Authorization' => 'Bearer 33d6e6625b6104faf872880b117bb4c313cf4161'])
    ->send();

print_r($response->content);
*/
die;


$module = Yii::$app->getModule('shop');
/*
$product_id = 1;

$array = [];
$ftpClient = ftp_connect($module->ftp['server']);
ftp_login($ftpClient, $module->ftp['login'], $module->ftp['password']);
@ftp_pasv($ftpClient, true);

$assetPather = "/assets/product/{$product_id}";
foreach ($array as $f) {
    //$deleted = @ftp_delete($ftpClient, "/uploads/product/{$product_id}_{$f}");
   // $deleted = @ftp_delete($ftpClient, $assetPather . "/medium__{$f}");
    //$deleted = @ftp_delete($ftpClient, $assetPather . "/small__{$f}");
}

@ftp_rename($ftpClient,$assetPather."/small__hpq3sgtjrt.jpg",$assetPather."/small_hpq3sgtjrt.jpg");
@ftp_rename($ftpClient,$assetPather."/medium__hpq3sgtjrt.jpg",$assetPather."/medium_hpq3sgtjrt.jpg");
/*
$assetsList = @ftp_nlist($ftpClient, $assetPather);
sort($assetsList);
unset($assetsList[0], $assetsList[1]); //remove list ".."
if (!$assetsList) {
    //@ftp_rmdir($ftpClient, $assetPather);
}

ftp_close($ftpClient);*/
/*
$limit = 50;
$query = ProductImage::find();
$total = $query->count();

$total_pages = ceil($total / $limit);
echo 'total pages: ' . $total_pages . PHP_EOL;
for ($page_number = 1; $page_number <= $total_pages; $page_number++) {
    Yii::$app->queue->push(new ImgFixerQueue([
        'limit' => $limit,
        'page' => $page_number
    ]));
    //break; //for test
}
*/

?>

<div class="row">
    <?php foreach ($items as $item) { ?>
        <?php if (isset($item['visible']) && $item['visible'] == true) { ?>
            <?php if (isset($item['url'])) { ?>
                <div class="col-sm-3">
                    <div class="img-thumbnail text-center mb-4">
                        <?= Html::icon($item['icon'], ['style' => 'font-size:40px']); ?>
                        <h4><?= Html::a($item['label'], $item['url']); ?></h4>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    <?php } ?>
</div>

