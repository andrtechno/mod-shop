<?php

use app\components\ImgFixerQueue;
use panix\mod\forsage\components\ProductByIdQueue;
use panix\mod\shop\models\ProductImage;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\UploadedFile;
use FtpClient\FtpClient;
use panix\engine\Html;



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

