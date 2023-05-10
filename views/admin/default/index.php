<?php
//nicolab/php-ftp-client
use yii\web\UploadedFile;
use FtpClient\FtpClient;
use panix\engine\Html;

/*
$ftp = new FtpClient;
//$ftp->connect('optikon.com.ua');
//$ftp->login('optikonc', 'pH5yZ2aA3v');

$ftp->connect('178.212.194.135');
$ftp->login('ftp', 'ftp33212312312312');


$handle = fopen('D:\OSPanel\domains\test.jpg', 'r');


//$file->saveAs('uploads/' . $file->baseName . '.' . $file->extension);



$upload = $ftp->fput('testftp.loc/testu-load.jpg', $handle, FTP_IMAGE);
if ($upload) {
    echo "Файл success успешно загружен\n";
} else {
    echo "При загрузке failt произошла проблема\n";
}


//$delete = $ftp->delete('testftp.loc/0134c — копия.jpg');
//var_dump($delete);

$list  = $ftp->nlist('testftp.loc');

$ftp->close();
fclose($handle);
\panix\engine\CMS::dump($list);*/

/*
$data = Yii::$app->cache->get('catalog-3');
var_dump($data);

                                                            $cacheData = Yii::$app->cache->get(Yii::$app->language.'-'.'catalog-' . $data['key']);
                                                            if ($cacheData) {
                                                                foreach ($cacheData as $item) {
                                                                   <div class="nav-item"><?= Html::a($item['title'], $item['url']); ?></div>

                                                                }
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

