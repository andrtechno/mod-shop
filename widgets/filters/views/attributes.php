<?php

use yii\helpers\Html;

echo \yii\helpers\Inflector::slug('как дела');
foreach ($attributes as $attrData) {


    if (count($attrData['filters']) > 0) {

        ?>

        <div class="panel panel-default filter-block" id="filter-attributes-<?= \yii\helpers\Inflector::slug($attrData['title']); ?>">
            <div class="panel-heading" data-toggle="collapse" data-target="#collapse-<?=md5($attrData['title'])?>" aria-expanded="false" aria-controls="collapse-<?=md5($attrData['title'])?>">
                <div class="panel-title"><?= Html::encode($attrData['title']) ?></div>
            </div>
            <div class="panel-body collapse in" id="collapse-<?=md5($attrData['title'])?>">
                <ul class="filter-list">
                    <?php
                    foreach ($attrData['filters'] as $filter) {


                        // if ($filter['count'] > 0) {
                        $url = Yii::$app->urlManager->addUrlParam('/shop/category/view', array($filter['queryKey'] => $filter['queryParam']), $attrData['selectMany']);
                        //} else {
                        //     $url = 'javascript:void(0)';
                        // }

                        $queryData = explode(',', Yii::$app->request->getQueryParam($filter['queryKey']));

                        echo Html::beginTag('li');
                        // Filter link was selected.
                        if (in_array($filter['queryParam'], $queryData)) {
                            // Create link to clear current filter
                            $url = Yii::$app->urlManager->removeUrlParam('/shop/category/view', $filter['queryKey'], $filter['queryParam']);
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

        <?php
    }
}
?>
