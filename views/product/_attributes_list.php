<?php

use yii\helpers\Html;

/**
 * @var $data \panix\mod\shop\models\Attribute
 * @var $model \panix\mod\shop\models\Product
 * @var $this \yii\web\View
 */
//print_r($model);
//die;
?>

<table class="table table-striped" id="attributes-list">
    <?php foreach ($data as $key => $result) { ?>

        <tr>
            <td><strong><?= $result['title']; ?>:</strong></td>
            <td><?= ($result['hasUrl']) ? Html::a(Html::encode($result['value']), ['/shop/catalog/view', 'slug' => $model->mainCategory->full_path, $key => $result['id']]) : Html::encode($result['value']); ?></td>
        </tr>

    <?php } ?>
</table>
