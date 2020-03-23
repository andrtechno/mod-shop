<?php

use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * @var $attributes array
 */

foreach ($attributes as $attrData) {

    if (count($attrData['filters']) > 1 && $attrData['totalCount'] > 1) {

        ?>

        <div class="card filter-block" id="filter-attributes-<?= $attrData['key']; ?>">
            <a class="card-header collapsed h5" data-toggle="collapse"
               href="#collapse-<?= $attrData['key']; ?>" aria-expanded="true"
               aria-controls="collapse-<?= $attrData['key']; ?>">
                <?= Html::encode($attrData['title']) ?>
            </a>
            <div class="card-collapse collapse in" id="collapse-<?= $attrData['key'] ?>">

                <?php if ($attrData['filtersCount'] >= 20 && !in_array($attrData['type'],[\panix\mod\shop\models\Attribute::TYPE_COLOR])) { ?>
                    <input type="text" name="search-filter"
                           onkeyup="filterSearchInput(this,'filter-<?= $attrData['key']; ?>')" class="form-control" placeholder="<?=Yii::t('shop/default','SEARCH');?>">
                <?php } ?>
                <div class="card-body overflow">
                    <ul class="filter-list" id="filter-<?= $attrData['key']; ?>">
                        <?php
                        foreach ($attrData['filters'] as $filter) {


                            if ($filter['count'] > 0) {
                                $url = Yii::$app->urlManager->addUrlParam('/' . Yii::$app->requestedRoute, [$attrData['key'] => $filter['queryParam']], $attrData['selectMany']);
                                //} else {
                                //     $url = 'javascript:void(0)';
                                //

                                $queryData = explode(',', Yii::$app->request->getQueryParam($attrData['key']));

                                echo Html::beginTag('li');
                                // Filter link was selected.

                                if (in_array($filter['queryParam'], $queryData)) {
                                    $checked = true;
                                    // Create link to clear current filter
                                    $url = Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, $attrData['key'], $filter['queryParam']);
                                    //echo Html::a($filter['title'], $url, array('class' => 'active'));
                                } else {
                                    $checked = false;
                                    //echo Html::a($filter['title'], $url);
                                }
                                //var_dump($checked);
                                echo '<div class="custom-control custom-checkbox">';
                                echo Html::checkBox('filter[' . $attrData['key'] . '][]', $checked, ['class' => 'custom-control-input', 'value' => $filter['queryParam'], 'id' => 'filter_' . $attrData['key'] . '_' . $filter['queryParam']]);
                                echo Html::label($filter['title'], 'filter_' . $attrData['key'] . '_' . $filter['queryParam'], ['class' => 'custom-control-label']);
                                echo $this->context->getCount($filter);
                                echo '</div>';

                                echo Html::endTag('li');
                            }
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
