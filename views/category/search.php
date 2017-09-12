<?php

use yii\helpers\Html;

if (($q = Yii::$app->request->get('q')))
    $result = Html::encode($q);
?>


<h1><?=
    Yii::t('shop/default', 'SEARCH_RESULT', [
        'result' => $result,
        'count' => $provider->totalCount
    ]);
    ?></h1>

<div class="row">
    <?php
    echo \yii\widgets\ListView::widget([
        'dataProvider' => $provider,
        'itemView' => $itemView,
        'layout' => '{summary}{items}{pager}',
        'emptyText' => 'Empty',
        'options' => ['class' => 'row'],
        'itemOptions' => ['class' => 'col-sm-4'],
        'pager' => ['class' => \kop\y2sp\ScrollPager::className()],
        'emptyTextOptions' => ['class' => 'alert alert-info']
    ]);
    ?>
</div>
