<?php
//nicolab/php-ftp-client
use yii\web\UploadedFile;
use FtpClient\FtpClient;


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
\panix\engine\CMS::dump($list);


