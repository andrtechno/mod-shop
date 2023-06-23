<?php

use app\components\ImgFixerQueue;
use panix\mod\shop\models\ProductImage;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\UploadedFile;
use FtpClient\FtpClient;
use panix\engine\Html;
/*
$query = ProductImage::find();
$total = $query->count();

$query->where(['product_id'=>35737]);
$module = Yii::$app->getModule('shop');

$ftpClient = ftp_connect($module->ftp['server']);
ftp_login($ftpClient, $module->ftp['login'], $module->ftp['password']);
ftp_pasv($ftpClient, true);

foreach ($query->all() as $img) {
    $img->ftp = $ftpClient;
    $original2 = $img->createVersionFtp('small', ['watermark' => false]);
    $original3 = $img->createVersionFtp('medium', ['watermark' => false]);
    $original3 = $img->createVersionFtp('preview', ['watermark' => false]);

    if ($ftpClient) {
        $ftpPath = "/uploads/product/{$img->product_id}";
        if (!ftp_mkdir($ftpClient,$ftpPath)) {
            echo "Не удалось создать директорию";
        }
        $versionPath = Yii::getAlias("@uploads/store/product/{$img->product_id}/{$img->filename}");
        echo $versionPath;
        $upload = ftp_put($ftpClient,"$ftpPath/{$img->filename}", $versionPath, FTP_IMAGE);
    }

}
ftp_close($ftpClient);*/
/*
Yii::$app->queue->push(new ImgFixerQueue([
    'limit' => 10,
    'page' => 1
]));*/


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

