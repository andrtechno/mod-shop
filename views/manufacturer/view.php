<?php

use yii\helpers\Html;

/**
 * @var $provider \panix\engine\data\ActiveDataProvider
 */
?>

<div class="col-lg-3">
    <?= \panix\mod\shop\widgets\filtersnew\FiltersWidget::widget([
        'model' => $this->context->dataModel,
        'attributes' => $this->context->eavAttributes,
    ]); ?>
</div>
<div class="col-lg-9">
    <h1><?= Html::encode(($this->h1) ? $this->h1 : Yii::t('shop/default', 'MANUFACTURER') . ' ' . $this->context->pageName); ?></h1>
    <?php if (!empty($model->description)) { ?>
        <div>
            <?php echo $model->description ?>
        </div>
    <?php } ?>
    <?php echo $this->render('@shop/views/catalog/_sorting', ['itemView' => $this->context->itemView]); ?>
    <div id="listview-ajax">
        <?php
        echo $this->render('@shop/views/catalog/listview', [
            'provider' => $provider,
            'itemView' => $this->context->itemView
        ]);
        ?>
    </div>
</div>

