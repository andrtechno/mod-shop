<?php


use yii\helpers\Html;
use yii\helpers\Inflector;


foreach ($attributes as $attrData) {
    if (count($attrData['filters']) > 0) {
        ?>

        <div class="card filter-block" id="filter-attributes-<?= Inflector::slug($attrData['title']); ?>">
            <div class="card-header collapsed" data-toggle="collapse"
                 data-target="#collapse-<?= md5($attrData['title']) ?>" aria-expanded="true"
                 aria-controls="collapse-<?= md5($attrData['title']) ?>">
                <h5><?= Html::encode($attrData['title']) ?></h5>
            </div>
            <div class="card-collapse collapse in" id="collapse-<?= md5($attrData['title']) ?>">
                <div class="card-body overflow">
                    <ul class="filter-list">
                        <?php
                        foreach ($attrData['filters'] as $filter) {


                            // if ($filter['count'] > 0) {
                            $url = Yii::$app->urlManager->addUrlParam('/' . Yii::$app->requestedRoute, array($filter['queryKey'] => $filter['queryParam']), $attrData['selectMany']);
                            //} else {
                            //     $url = 'javascript:void(0)';
                            // }

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
        <?php
    }
}
?>
