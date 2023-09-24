<?php

use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * @var $attributes array
 */

foreach ($attributes as $attrData) {

    if (count($attrData['filters']) > 1 && $attrData['totalCount'] > 1) {
        echo Html::hiddenInput('attributes[]',$attrData['key']);
        ?>

        <div class="card filter-block" id="filter-attributes-<?= $attrData['key']; ?>">
            <a class="card-header collapsed h5" data-toggle="collapse"
               href="#collapse-<?= $attrData['key']; ?>" aria-expanded="true"
               aria-controls="collapse-<?= $attrData['key']; ?>">
                <?= Html::encode($attrData['title']) ?>
            </a>
            <div class="card-collapse collapse in" id="collapse-<?= $attrData['key'] ?>">

                <?php if ($this->context->searchItem > 0 && $attrData['filtersCount'] >= $this->context->searchItem && !in_array($attrData['type'],[\panix\mod\shop\models\Attribute::TYPE_COLOR])) { ?>
                    <input type="text" name="search-filter"
                           onkeyup="filterSearchInput(this,'filter-<?= $attrData['key']; ?>')" class="form-control" placeholder="<?=Yii::t('shop/default','SEARCH_BY', mb_strtolower($attrData['title']));?>">
                <?php } ?>
                <div class="card-body">
                    <ul class="filter-list list-unstyled" id="filter-<?= $attrData['key']; ?>">
                        <?php
                        foreach ($attrData['filters'] as $filter) {
                            $queryData = explode(',', Yii::$app->request->getQueryParam($attrData['key']));
                            $checkBoxOptions=[];
                            $checkBoxOptions['value'] = $filter['id'];
                            $checkBoxOptions['class']='custom-control-input';
                            $checkBoxOptions['id'] = 'filter_' . $attrData['key'] . '_' . $filter['id'];
                            if (!$filter['count'] && !in_array($filter['id'], $queryData)) {
                                $checkBoxOptions['disabled']='disabled';
                            }

                            if ($filter['count'] > 0) {
                                $url = Yii::$app->urlManager->addUrlParam('/' . Yii::$app->requestedRoute, [$attrData['key'] => $filter['id']], $attrData['selectMany']);
                                //} else {
                                //     $url = 'javascript:void(0)';
                                //


                                echo Html::beginTag('li');
                                // Filter link was selected.

                                if (in_array($filter['id'], $queryData)) {
                                    $checked = true;
                                    // Create link to clear current filter
                                    $url = Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, $attrData['key'], $filter['id']);
                                    //echo Html::a($filter['title'], $url, array('class' => 'active'));
                                } else {
                                    $checked = false;
                                    //echo Html::a($filter['title'], $url);
                                }

                                if ($attrData['type'] == \panix\mod\shop\models\Attribute::TYPE_COLOR) {
                                    $css = $this->context->generateGradientCss($filter['data']);

                                    $checkedHtml = ($checked) ? '<span class="filter-color-checked"></span>' : '<span></span>';
                                    echo Html::label(Html::checkBox('filter[' . $attrData['key'] . '][]', $checked, ['class' => '', 'value' => $filter['id'], 'id' => 'filter_' . $attrData['key'] . '_' . $filter['id']]) . $checkedHtml, 'filter_' . $attrData['key'] . '_' . $filter['id'], ['class' => 'filter-color', 'title' => $filter['title'] . ' (' . trim(strip_tags($this->context->getCount($filter))) . ')', 'style' => $css]);


                                } else {



                                    if($attrData['selectMany']){
                                        echo '<div class="custom-control custom-checkbox">';
                                        echo Html::checkBox('filter[' . $attrData['key'] . '][]', $checked, $checkBoxOptions);
                                        echo Html::label($filter['title'].(($checked)?'':$this->context->getCount($filter)), 'filter_' . $attrData['key'] . '_' . $filter['id'], ['class' => 'custom-control-label','data-search'=>$filter['title']]);
                                        echo '</div>';
                                    }else{
                                        echo '<div class="radio">';
                                        echo Html::label(Html::radio('filter[' . $attrData['key'] . '][]', $checked, ['class' => '', 'value' => $filter['id'], 'id' => 'filter_' . $attrData['key'] . '_' . $filter['id']]).$filter['title'].(($checked)?'':$this->context->getCount($attrData['key'], $filter)), 'filter_' . $attrData['key'] . '_' . $filter['id'], ['class' => '']);
                                        echo '</div>';
                                    }


                                    //var_dump($checked);

                                   // echo $this->context->getCount($filter);
                                }

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
