<?php

use yii\helpers\Html;

?>
<?php if ($brands['filters'] && count($brands['filters']) > 1) { ?>
    <div class="card filter-block">
        <a class="card-header h5" data-toggle="collapse" href="#collapse-<?= md5('brand') ?>"
           aria-expanded="true" aria-controls="collapse-<?= md5('brand') ?>">
            <?= Yii::t('shop/default', 'FILTER_BY_BRAND') ?>
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
                        // Filter link was selected.

                        $checkBoxOptions=[];
                        $checkBoxOptions['class'] = 'custom-control-input';
                        $checkBoxOptions['value'] = $filter['queryParam'];
                        $checkBoxOptions['id'] = 'filter_' . $filter['key'] . '_' . $filter['queryParam'];
                        $disableClass='';
                        if (in_array($filter['queryParam'], $queryData)) {
                            // Create link to clear current filter
                            $checked = true;

                            $url = Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, $filter['key'], $filter['queryParam']);
                            //echo Html::a($filter['title'], $url, array('class' => 'active'));
                        } else {

                            $checked = false;
                            //echo Html::a($filter['title'], $url);
                        }
                        if(!$filter['count']){
                            $disableClass='disabled';
                            $checkBoxOptions['disabled'] = 'disabled';
                        }
                        echo Html::beginTag('li',['class'=>$disableClass]);



                        echo '<div class="custom-control custom-checkbox">';
                        echo Html::checkBox('filter[' . $filter['key'] . '][]', $checked, $checkBoxOptions);
                        echo Html::label($filter['title'].(($checked)?'':$this->context->getCount($filter)), 'filter_' . $filter['key'] . '_' . $filter['queryParam'],['class' => 'custom-control-label', 'data-search' => $filter['title']]);


                       // echo $this->context->getCount($filter);
                        echo '</div>';
                        echo Html::endTag('li');
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php } ?>
