<?php

use yii\helpers\Html;

?>
<?php if ($brands['filters'] && count($brands['filters']) > 1) { ?>
    <div class="card filter-block">
        <a class="card-header h5" data-toggle="collapse" href="#collapse-<?= md5('brand') ?>"
           aria-expanded="true" aria-controls="collapse-<?= md5('brand') ?>">
            <?= Yii::t('shop/default', 'FILTER_BY_MANUFACTURER') ?>
        </a>
        <div class="card-collapse collapse in" id="collapse-<?= md5('brand') ?>">
            <?php if (count($brands['filters']) >= 20 && $this->context->searchItem > 0) { ?>
                <input type="text" name="search-filter"
                       onkeyup="filterSearchInput(this,'filter-brand')" class="form-control" placeholder="<?=Yii::t('shop/default','SEARCH');?>">
            <?php } ?>
            <div class="card-body">
                <ul class="filter-list list-unstyled" id="filter-brand">
                    <?php
                    foreach ($brands['filters'] as $filter) {
                        $url = Yii::$app->urlManager->addUrlParam('/' . Yii::$app->requestedRoute, [$filter['key'] => $filter['queryParam']], $brands['selectMany']);
                        $queryData = explode(',', Yii::$app->request->getQueryParam($filter['key']));

                        echo Html::beginTag('li');


                        // Filter link was selected.
                        if (in_array($filter['queryParam'], $queryData)) {
                            // Create link to clear current filter
                            $checked = true;
                            $url = Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, $filter['key'], $filter['queryParam']);
                            //echo Html::a($filter['title'], $url, array('class' => 'active'));
                        } else {
                            $checked = false;
                            //echo Html::a($filter['title'], $url);
                        }
                        echo '<div class="custom-control custom-checkbox">';
                        echo Html::checkBox('filter[' . $filter['key'] . '][]', $checked, ['class' => 'custom-control-input', 'value' => $filter['queryParam'], 'id' => 'filter_' . $filter['key'] . '_' . $filter['queryParam']]);
                        echo Html::label($filter['title'], 'filter_' . $filter['key'] . '_' . $filter['queryParam'],['class' => 'custom-control-label']);


                        echo $this->context->getCount($filter);
                        echo '</div>';
                        echo Html::endTag('li');
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php } ?>
