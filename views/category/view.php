<?php

use yii\helpers\Url;
use panix\mod\shop\widgets\categories\CategoriesWidget;
use panix\mod\shop\widgets\filters\FiltersWidget;
use yii\helpers\Html;

?>



<div class="col-sm-3">
    <?= CategoriesWidget::widget([]) ?>
    <?= FiltersWidget::widget([
        'model'=>$this->context->dataModel,
    ]) ?>
    

    

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
        'options' => ['class' => 'row'],
        'itemOptions' => ['class' => 'col-sm-4'],
          'pager' => ['class' => \kop\y2sp\ScrollPager::className()],
        'emptyTextOptions' => ['class' => 'alert alert-info']
    ]);
    ?>
</div>   