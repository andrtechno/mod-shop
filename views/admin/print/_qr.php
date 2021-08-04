<?php

use chillerlan\QRCode\{QRCode, QROptions};
use yii\helpers\Html;


$options = new QROptions([
    //'version' =>7,
'versionMin'=>1,
'versionMax'=>10,
    'outputType' => QRCode::OUTPUT_IMAGE_JPG,
    'eccLevel' => QRCode::ECC_H,
    'jpegQuality' => 100,
    'quietzoneSize' => 1,
    'scale' => 4,
    'moduleValues' => [
    // finder
    1536 => '#A71111', // dark (true)
    6    => '#FFBFBF', // light (false)
    // alignment
    2560 => '#A70364',
    10   => '#FFC9C9',
    // timing
    3072 => '#98005D',
    12   => '#FFB8E9',
    // format
    3584 => '#003804',
    14   => '#00FB12',
    // version
    4096 => '#650098',
    16   => '#E0B8FF',
    // data
    1024 => '#4A6000',
    4    => '#ECF9BE',
    // darkmodule
    512  => '#080063',
    // separator
    8    => '#DDDDDD',
    // quietzone
    18   => '#DDDDDD',
]
]);

$code = '44-15-55110-'.$product->id;
$ph = Yii::$app->security->generatePasswordHash($code);
//var_dump(Yii::$app->security->validatePassword($code,$ph));die
?>

<div style="text-align: center;background: #c1c1c1;max-height:226px;height:226px;overflow: hidden">
<?php echo Html::img((new QRCode($options))->render($ph), []); ?>

    <div><?= \panix\engine\CMS::hash($code); ?></div>
    <div>motivation.com</div>
    <?php echo Html::img('/uploads/qr-code.png', ['width'=>50]); ?>

</div>

