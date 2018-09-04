<?php

use yii\helpers\Url;
use panix\mod\shop\widgets\categories\CategoriesWidget;
use panix\mod\shop\widgets\filters\FiltersWidget;
use yii\helpers\Html;
?>


<div class="row">
    <div class="col-sm-3">
        <?= CategoriesWidget::widget([]) ?>
        <?=
        FiltersWidget::widget([
            'model' => $this->context->dataModel,
            'attributes' => $this->context->eavAttributes,
        ]);

        print_r($_GET);
        ?>




    </div>   
    <div class="col-sm-9">
        <?= Html::a('back', Url::previous(),['class'=>'btn btn-default']); ?>
        <h1><?= $this->context->dataModel->name ?></h1>
        <?php echo $this->render('_sorting', array('itemView' => $itemView)); ?>
        <?php
        echo \yii\widgets\ListView::widget([
            'dataProvider' => $provider,
            'itemView' => $itemView,
            'layout' => '{sorter}{summary}{items}{pager}',
            'emptyText' => 'Empty',
            'options' => ['class' => 'row list-view'],
            'itemOptions' => ['class' => 'item'],
            'sorter' => [
                //'class' => \yii\widgets\LinkSorter::className(),
                'attributes'=>['price','sku']
            ],
            'pager' => [
                'class' => \kop\y2sp\ScrollPager::className(),
                'triggerTemplate' => '<div class="ias-trigger" style="text-align: center; cursor: pointer;"><a href="javascript:void(0)">{text}</a></div>'
            ],
            'emptyTextOptions' => ['class' => 'alert alert-info']
        ]);
        ?>
    </div>
</div>