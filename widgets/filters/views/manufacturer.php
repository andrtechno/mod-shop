<?php

use yii\helpers\Html;
?>

<div class="panel panel-default filter-block">
    <div class="panel-heading">
        <div class="panel-title"><?= Yii::t('shop/default', 'FILTER_BY_MANUFACTURER') ?></div>
    </div>
    <div class="panel-body">
        <ul class="filter-list">
            <?php
            foreach ($manufacturers['filters'] as $filter) {
                $url = Yii::$app->request->addUrlParam('/shop/category/view', array($filter['queryKey'] => $filter['queryParam']), $manufacturers['selectMany']);
                $queryData = explode(',', Yii::$app->request->getQueryParam($filter['queryKey']));

                echo Html::beginTag('li');



                // Filter link was selected.
                if (in_array($filter['queryParam'], $queryData)) {
                    // Create link to clear current filter
                    $url = Yii::$app->request->removeUrlParam('/shop/category/view', $filter['queryKey'], $filter['queryParam']);
                    echo Html::a($filter['title'], $url, array('class' => 'active'));
                } else {
                    echo Html::a($filter['title'], $url);
                }
                echo $filter['count'];
                echo Html::endTag('li');
            }
            ?>
        </ul>
    </div>
</div>