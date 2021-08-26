<?php

use panix\engine\Html;

?>
<div class="autocomplete-item">
    <div class="row">
        <div class="col-sm-2">
            <div class="autocomplete-img">
                <?= Html::img($model->getMainImage('50x50')->url, ['alt' => $model->name, 'class' => 'img-thumbnail']); ?>
            </div>
        </div>
        <div class="col-sm-10">
            <div class="autocomplete-info">
                <div><?= Html::a($model->name, $model->getUrl(), []); ?></div>
                <div><?= $model->getFrontPrice() ?> <?= Yii::$app->currency->active['symbol'] ?></div>
            </div>
        </div>
    </div>
</div>