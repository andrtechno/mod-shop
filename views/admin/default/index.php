<?php
//nicolab/php-ftp-client
use yii\helpers\Json;
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



//$result2 = Yii::$app->elasticsearch->get('_cat/indices/'.Yii::$app->getModule('shop')->elasticIndex.'?v=true&s=index', '','');

//$result = Yii::$app->elasticsearch->get('_cat/indices?v=true&s=index', '','');
$result = Yii::$app->elasticsearch->get('_cat/indices/'.Yii::$app->getModule('shop')->elasticIndex, '','');
if($result){
    $explode = explode(' ',$result[0]); ?>
    <h1>Elastic index "<?= Yii::$app->getModule('shop')->elasticIndex; ?>" indicate</h1>
    <table class="table table-striped">
        <tr>
            <th>health</th>
            <th>status</th>
            <th>index</th>
            <th>uuid</th>
            <th>primary shards</th>
            <th>replics</th>

            <th>docs count</th>
            <th>docs deleted</th>
            <th>store size</th>
            <th>primary store size</th>
        </tr>
        <tr>
    <?php
    foreach ($explode as $key=>$server){
        if($key ==0){
            if($server =='yellow'){
                $class='bg-warning';
            }elseif($server =='red'){
                $class='bg-danger';
            }else{
                $class='bg-success';
            }
            $server = '<span class="badge '.$class.'" style="text-indent: -9999px;width:10px;height:10px;border-radius:50%"> </span>';
        }
         ?>

            <td><?= $server; ?></td>

        <?php } ?>
        </tr>
        </table>
            <?php

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

