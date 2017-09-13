<?php

use yii\helpers\Url;
use panix\mod\shop\widgets\categories\CategoriesWidget;
use panix\mod\shop\widgets\filters\FiltersWidget;
use yii\helpers\Html;
?>



<div class="col-sm-3">
    <?= CategoriesWidget::widget([]) ?>
    <?=
    FiltersWidget::widget([
        'model' => $this->context->dataModel,
        'attributes' => $this->context->eavAttributes,
    ])
    ?>




</div>   
<div class="col-sm-9">
    <?= Html::a('back', Url::previous()); ?>
    <h1><?= $this->context->dataModel->name ?></h1>
    <?php
    echo \yii\widgets\ListView::widget([
        'dataProvider' => $provider,
        'itemView' => $itemView,
        'layout' => '{summary}{items}{pager}',
        'emptyText' => 'Empty',
        'options' => ['class' => 'row list-view'],
        'itemOptions' => ['class' => 'item'],
        'pager' => [
            'class' => \kop\y2sp\ScrollPager::className(),
            'triggerTemplate' => '<div class="ias-trigger" style="text-align: center; cursor: pointer;"><a href="javascript:void(0)">{text}</a></div>'
        ],
        'emptyTextOptions' => ['class' => 'alert alert-info']
    ]);
    ?>
</div>   