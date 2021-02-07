<?php

use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;

?>
<div class="items">
    <?php
    $pr = \panix\mod\shop\models\Product::find()->limit(50)->all();
    foreach ($pr as $p) { ?>
        <div class="item" style="">
            <div class="container">

                <table>
                    <tr>
                        <td>
                            <div style="margin: 4px 0">OOO "Panix"</div>
                        </td>
                        <td style="text-align: right"><small><?= date('d.m.Y'); ?></small></td>
                    </tr>
                    <tr>

                        <td colspan="2">
                            <?= $p->mainCategory->name; ?> <?= $p->manufacturer->name; ?>
                        </td>

                    </tr>
                    <tr>
                        <td style="height: 140px">
                            Артикул: <?= $p->sku; ?>

                        </td>
                        <td style="height: 140px;text-align: right">
                            <?php if (Yii::$app->hasModule('discounts') && $p->hasDiscount) { ?>
                                <div class="price-old">
                                    <?= Yii::$app->currency->number_format(Yii::$app->currency->convert($p->originalPrice, $p->currency_id),2) ?>
                                </div>
                                <div class="sep"></div>
                            <?php } ?>
                            <div class="price">
                                <?= Yii::$app->currency->number_format($p->getFrontPrice(),2); ?>
                            </div>
                            <?= Yii::$app->currency->active['symbol']; ?>
                        </td>
                    </tr>
                    <tr>

                        <td colspan="2" style="border-top:2px solid red;">
                            <small>Акция: c 01.01.2021 по 01.05.2021</small>
                        </td>

                    </tr>
                </table>
            </div>
        </div>
    <?php } ?>
</div>
