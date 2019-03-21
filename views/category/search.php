<?php

use yii\helpers\Html;
use panix\mod\shop\widgets\filters\FiltersWidget;

if (($q = Yii::$app->request->get('q')))
    $result = Html::encode($q);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">

            <?php
            echo FiltersWidget::widget([
                'model' => $this->context->dataModel,
                'attributes' => $this->context->eavAttributes,
            ]);

            ?>
        </div>
        <div class="col-md-8">
            <h1><?=
                Yii::t('shop/default', 'SEARCH_RESULT', [
                    'result' => $result,
                    'count' => $provider->totalCount
                ]);
                ?></h1>

            <div class="col">
                <?php
                echo \yii\widgets\ListView::widget([
                    'dataProvider' => $provider,
                    'itemView' => $itemView,
                    'layout' => '{summary}{items}{pager}',
                    'emptyText' => 'Empty',
                    'options' => ['class' => 'row'],
                    'itemOptions' => ['class' => 'col-sm-4'],
                    'pager' => ['class' => \kop\y2sp\ScrollPager::class],
                    'emptyTextOptions' => ['class' => 'alert alert-info']
                ]);
                ?>
            </div>
        </div>
    </div>
</div>