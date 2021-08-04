<?php

use yii\helpers\Html;
?>
<?php if($brands['filters']) { ?>
<div class="card filter-block">
    <div class="card-header" data-toggle="collapse" data-target="#collapse-<?=md5('brand')?>" aria-expanded="true" aria-controls="collapse-<?=md5('brand')?>">
        <h5><?= Yii::t('shop/default', 'FILTER_BY_MANUFACTURER') ?></h5>
    </div>
    <div class="card-collapse collapse in" id="collapse-<?=md5('brand')?>">
    <div class="card-body">
        <ul class="filter-list">
            <?php
            foreach ($brands['filters'] as $filter) {
                $url = Yii::$app->urlManager->addUrlParam('/' . Yii::$app->requestedRoute, array($filter['queryKey'] => $filter['queryParam']), $brands['selectMany']);
                $queryData = explode(',', Yii::$app->request->getQueryParam($filter['queryKey']));

                echo Html::beginTag('li');



                // Filter link was selected.
                if (in_array($filter['queryParam'], $queryData)) {
                    // Create link to clear current filter
                    $url = Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, $filter['queryKey'], $filter['queryParam']);
                    echo Html::a($filter['title'], $url, array('class' => 'active'));
                } else {
                    echo Html::a($filter['title'], $url);
                }
                echo $this->context->getCount($filter);
                echo Html::endTag('li');
            }
            ?>
        </ul>
    </div>
    </div>
</div>
<?php } ?>
