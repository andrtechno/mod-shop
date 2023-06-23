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

$product_id = 34668;

$array = [];
$ftpClient = ftp_connect($module->ftp['server']);
ftp_login($ftpClient, $module->ftp['login'], $module->ftp['password']);
@ftp_pasv($ftpClient, true);

$assetPather = "/assets/product/{$product_id}";
foreach ($array as $f) {
    $deleted = @ftp_delete($ftpClient, "/uploads/product/{$product_id}_{$f}");
    $deleted = @ftp_delete($ftpClient, $assetPather . "/medium__{$f}");
    $deleted = @ftp_delete($ftpClient, $assetPather . "/small__{$f}");
}


$assetsList = @ftp_nlist($ftpClient, $assetPather);
sort($assetsList);
unset($assetsList[0], $assetsList[1]); //remove list ".."
if (!$assetsList) {
    //@ftp_rmdir($ftpClient, $assetPather);
}
\panix\engine\CMS::dump($assetsList);
ftp_close($ftpClient);

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

