<?php

use yii\helpers\Html;
?>

<div class="clearfix filters-container">
    <div class="row">



        <div class="col-sm-5 col-md-5 col-lg-5">

            <span class=""><?= Yii::t('shop/default', 'VIEW'); ?> </span>
            <?php
            $sorter[Yii::$app->urlManager->removeUrlParam('/shop/category/view', 'sort')] = Yii::t('shop/default', 'SORT');
            $sorter[Yii::$app->urlManager->addUrlParam('/shop/category/view', array('sort' => 'price'))] = Yii::t('shop/default', 'SORT_BY_PRICE_ASC');
            $sorter[Yii::$app->urlManager->addUrlParam('/shop/category/view', array('sort' => '-price'))] = Yii::t('shop/default', 'SORT_BY_PRICE_DESC');
            $sorter[Yii::$app->urlManager->addUrlParam('/shop/category/view', array('sort' => '-date_create'))] = Yii::t('shop/default', 'SORT_BY_DATE_DESC');
            $active = Yii::$app->urlManager->addUrlParam('/shop/category/view', array('sort' => Yii::$app->request->get('sort')));

            echo Html::dropDownList('sorter', $active, $sorter, ['onChange' => 'window.location = $(this).val()','class'=>'custom-select','style'=>'width:auto;']);
            ?>


        </div><!-- /.col -->
        <div class="col-sm-3 col-md-4 col-lg-4">



            <?php
            $limits = array(Yii::$app->urlManager->removeUrlParam('/shop/category/view', 'per_page') => $this->context->allowedPageLimit[0]);
            array_shift($this->context->allowedPageLimit);
            foreach ($this->context->allowedPageLimit as $l) {
                $active = Yii::$app->urlManager->addUrlParam('/shop/category/view', array('per_page' => Yii::$app->request->get('per_page')));
                $limits[Yii::$app->urlManager->addUrlParam('/shop/category/view', array('per_page' => $l))] = $l;
            }
            ?>
            <span class=""><?= Yii::t('shop/default', 'OUTPUT_ON'); ?> </span>
            <?php
            echo Html::dropDownList('per_page', $active, $limits, ['onChange' => 'window.location = $(this).val()','class'=>'custom-select','style'=>'width:auto;']);
            ?>
            <span class=""><?= Yii::t('shop/default', 'товаров'); ?></span>

        </div>


        <div class="col-sm-4 col-md-3 col-lg-3">

            <div class="btn-group">
                <a class="btn btn-xs <?php if ($itemView === '_view_grid') echo 'btn-info active'; ?>" href="<?= Yii::$app->urlManager->removeUrlParam('/shop/category/view', 'view') ?>"><i class="icon-grid"></i><span class=""> Сеткой</span></a></li>
                <a class="btn btn-sm <?php if ($itemView === '_view_list') echo 'btn-info active'; ?>" href="<?= Yii::$app->urlManager->addUrlParam('/shop/category/view', array('view' => 'list')) ?>"><i class="icon-menu"></i><span class=""> Списком</span></a></li>
                <a class="btn btn-sm <?php if ($itemView === '_view_table') echo 'btn-info active'; ?>" href="<?= Yii::$app->urlManager->addUrlParam('/shop/category/view', array('view' => 'table')) ?>"><i class="icon-table"></i><span class=""> Таблицей</span></a></li>
            </div>
        </div>
    </div>
</div>
