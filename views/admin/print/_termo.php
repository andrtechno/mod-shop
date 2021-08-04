
<div style="text-align: center;">
    <?= $product->mainCategory->name; ?> <?= $product->brand->name; ?>
</div>
<div style="position: absolute;bottom: 14mm;">
    <table style="width: 100%;">
        <tr>
            <td style="font-size: 12px;">Арт.: <?= $product->sku; ?></td>
            <td style="text-align: right">
                <strong><?= $product->getFrontPrice(); ?> <?= Yii::$app->currency->active['symbol']; ?></strong></td>
        </tr>
    </table>
</div>

<div style="text-align: center;position: absolute;bottom: 5px;left:0;right:0;">
    <barcode code="2000000000053" size="1" height="0.45" type="EAN13" text="0"/>

</div>
