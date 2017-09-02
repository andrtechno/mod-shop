<?php

use yii\helpers\Url;
use panix\mod\shop\widgets\categories\CategoriesWidget;
use panix\mod\shop\widgets\filters\FiltersWidget;
use yii\helpers\Html;
use panix\mod\shop\models\ShopProduct;
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
        'emptyTextOptions' => ['class' => 'alert alert-info']
    ]);
    ?>
</div>   