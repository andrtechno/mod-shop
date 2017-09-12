<?php

use panix\engine\Html;
?>
<div class="autocomplete-item">
    <div class="autocomplete-img">
        <?= Html::img($image,['alt'=>$name,'class'=>'img-thumbnail']); ?>
    </div>
    <div class="autocomplete-info">
        <div><?= Html::a($name,$url,[]); ?></div>
        <div><?=$price?> <?= Yii::$app->currency->active->symbol ?></div>
    </div>
</div>