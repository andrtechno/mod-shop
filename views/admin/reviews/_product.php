<?php

use panix\engine\Html;
use panix\engine\CMS;

/**
 * @var $product \panix\mod\shop\models\Product
 */

$product = $model->product;
?>
<div class="card">
    <div class="card-header">
        <h5><?= $product->name; ?></h5>
    </div>
    <div class="card-body p-3">
        <div class="row">
            <div class="col-sm-6">
                <?= Html::img($product->getMainImage()->url, ['class' => 'img-thumbnail img-fluid']); ?>
            </div>
            <div class="col-sm-6">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th><?= $product->getAttributeLabel('price'); ?></th>
                        <td><?= $product->price; ?></td>
                    </tr>
                    <tr>
                        <th><?= $product->getAttributeLabel('sku'); ?></th>
                        <td><?= $product->sku; ?></td>
                    </tr>
                    <tr>
                        <th><?= $product->getAttributeLabel('main_category_id'); ?></th>
                        <td><?= $product->mainCategory->name; ?></td>
                    </tr>
                </table>
                <?= Html::a('Просмотр товара',$product->getUrl(),['class'=>'btn btn-sm btn-info','target'=>'_blank']); ?>
                <?= Html::a('Ред. товара',$product->getUpdateUrl(),['class'=>'btn btn-sm btn-info']); ?>
            </div>
        </div>

    </div>
</div>



