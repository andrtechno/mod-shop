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
                <?php
                if ($result['type'] == Attribute::TYPE_COLOR) {
                    $colors = \yii\helpers\Json::decode($result['data'], true);
                    echo Html::tag('span', '', ['class' => 'attribute-color-box', 'style' => Attribute::generateGradientCss($colors)]);
                }
                ?>
                <?php
                if ($result['hasUrl']) {
                    echo Html::a(Html::encode($result['value']), ['/shop/catalog/view', 'slug' => ($model->mainCategory) ? $model->mainCategory->full_path : '', $key => $result['id']]);
                } else {
                    echo Html::encode($result['value']);
                }
                ?>
            </td>
        </tr>
    <?php } ?>
</table>
