<?php

use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

?>

<?php
$pr = \panix\mod\shop\models\Product::find()->limit(50)->all();
foreach ($pr as $p) {
    ?>
    <table style="width: 100%;margin: 0;padding: 0;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:70mm;height:49.5mm;border-top:1px dashed #c1c1c1;border-bottom:1px dashed #c1c1c1">
                <table style="width:100%;height: 100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td colspan="2" style="height:25mm;text-align: center;vertical-align: top">
                            <div style="margin: 4px 0">OOO "Panix"</div>
                            <span style="font-size: 15px;"><?= $p->mainCategory->name; ?> <?= $p->manufacturer->name; ?></span>

                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px">Артикул: <?= $p->sku; ?></td>
                    </tr>
                    <tr>
                        <td style="border-top:2px solid #c1c1c1;text-align: right;height: 15mm;padding-left: 3px;">
                            <barcode code="2000000000053" size="1" height="0.45" type="EAN13" text="0"/>
                        </td>
                        <td style="border-top:2px solid #c1c1c1;text-align: right;height: 15mm">
                            <span style="font-size: 25px;font-weight: bold"><?= $p->getFrontPrice(); ?></span>
                            <br/>
                            <?= Yii::$app->currency->active['symbol']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px;border-top:2px solid #c1c1c1;border-right:1px solid #c1c1c1;width: 70%;font-size: 11px">Акция: c 01.01.2021 по 01.05.2021</td>
                        <td style="padding: 2px;border-top:2px solid #c1c1c1;border-left:1px solid #c1c1c1;width: 30%;text-align: right;font-size: 11px"><?= date('d.m.Y');?></td>
                    </tr>
                </table>

            </td>
            <td style="width:70mm;height:49.5mm;border:1px dashed #c1c1c1;">
                <table style="width:100%;height: 100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td colspan="2" style="height:25mm;text-align: center;">
                            <span style="font-size: 15px;"><?= $p->mainCategory->name; ?> <?= $p->manufacturer->name; ?> 1л.</span>

                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px">Артикул: <?= $p->sku; ?></td>
                    </tr>
                    <tr>
                        <td style="border-top:2px solid #c1c1c1;text-align: right;height: 15mm" colspan="2">
                            <span style="font-size: 25px;font-weight: bold"><?= $p->getFrontPrice(); ?></span>
                            <br/>
                            <?= Yii::$app->currency->active['symbol']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px;border-top:2px solid #c1c1c1;border-right:1px solid #c1c1c1;width: 50%"></td>
                        <td style="padding: 2px;border-top:2px solid #c1c1c1;border-left:1px solid #c1c1c1;width: 50%;text-align: right;font-size: 11px"><?= date('d.m.Y');?></td>
                    </tr>
                </table>
            </td>
            <td style="width:70mm;height:49.5mm;border-top:1px dashed #c1c1c1;border-bottom:1px dashed #c1c1c1">
                <table style="width:100%;height: 100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td colspan="2" style="height:25mm;text-align: center;">
                            <span style="font-size: 15px;"><?= $p->mainCategory->name; ?> <?= $p->manufacturer->name; ?></span>

                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px">Артикул: <?= $p->sku; ?></td>
                    </tr>
                    <tr>
                        <td style="border-top:2px solid #c1c1c1;text-align: right;height: 15mm" colspan="2">
                            <span style="font-size: 25px;font-weight: bold"><?= $p->getFrontPrice(); ?></span>
                            <br/>
                            <?= Yii::$app->currency->active['symbol']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px;border-top:2px solid #c1c1c1;border-right:1px solid #c1c1c1;width: 50%"></td>
                        <td style="padding: 2px;border-top:2px solid #c1c1c1;border-left:1px solid #c1c1c1;width: 50%;text-align: right;font-size: 11px"><?= date('d.m.Y');?></td>
                    </tr>
                </table>
            </td>
        </tr>


    </table>
    <?php
} ?>

