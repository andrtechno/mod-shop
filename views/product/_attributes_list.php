<?php

use yii\helpers\Html;

/**
 * @var $data \panix\mod\shop\models\Attribute
 * @var $model \panix\mod\shop\models\Product
 * @var $this \yii\web\View
 */

?>

<table class="table table-striped" id="attributes-list">
    <?php foreach ($data as $key => $result) { ?>
        <tr>
            <td><?= $result['title']; ?>:</td>
            <td>
                <strong><?= ($result['use_in_filter']) ? Html::a(Html::encode($result['value']), ['/shop/catalog/view', 'slug' => ($model->mainCategory) ? $model->mainCategory->full_path : '', $key => $result['id']]) : Html::encode($result['value']); ?></strong>
            </td>
        </tr>
    <?php } ?>
</table>