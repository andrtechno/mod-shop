<?php
use yii\helpers\Url;
use panix\engine\Html;

/**
 * @var $site_name string
 * @var $product \panix\mod\shop\models\Product
 */

$thStyle = 'border-color:#D8D8D8; border-width:1px; border-style:solid;';
$currency = Yii::$app->currency;
?>

Здравствуйте!<br/>
<p>
    Магазин <strong>&laquo;<?= $site_name; ?>&raquo;</strong> уведомляет Вас о том,
    что появился в наличии товар:
<h4>


</h4>


<table border="0" width="100%" cellspacing="1" cellpadding="5" style="border-spacing: 0;border-collapse: collapse;">
    <tr>
        <th colspan="2" style="<?= $thStyle; ?>"><?= Yii::t('cart/default', 'MAIL_TABLE_TH_PRODUCT') ?></th>
        <th style="<?= $thStyle; ?>"><?= Yii::t('cart/default', 'MAIL_TABLE_TH_PRICE_FOR') ?></th>
    </tr>
    <tr>
        <td style="<?= $thStyle; ?>width:10%" align="center">
            <?= Html::img(Url::to($product->getMainImage('100x')->url, true), ['alt' => $product->name]); ?>
        </td>
        <td style="<?= $thStyle; ?>">
            <?= Html::a($product->name, Url::to($product->getUrl(), true), ['target' => '_blank']); ?>
        </td>

        <td style="<?= $thStyle; ?>" align="center">
            <strong><?= $currency->number_format($currency->convert($product->price, $product->currency_id)); ?></strong>
            <sup><?= $currency->active->symbol ?></sup></td>
    </tr>
</table>