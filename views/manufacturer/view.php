<?php

use yii\helpers\Html;

/**
 * @var $provider \panix\engine\data\ActiveDataProvider
 */
?>


<div class="row">
    <div class="col-sm-3">
        <?php
        echo \panix\mod\shop\widgets\filtersnew\FiltersWidget::widget([
            'model' => $this->context->dataModel,
            'attributes' => $this->context->eavAttributes,
        ]);

        ?>
    </div>

    <div class="col-sm-9">
        <div class="heading-gradient">
            <h1><?= Html::encode(($this->h1) ? $this->h1 : $model->name); ?></h1>
        </div>
        <?php if (!empty($model->description)) { ?>
            <div>
                <?php echo $model->description ?>
            </div>
        <?php } ?>


        <?php
        echo \panix\engine\widgets\ListView::widget([
            'dataProvider' => $provider,
            'itemView' => '@shop/views/category/_view_grid',
            'layout' => '{summary}{items}{pager}',
            'itemOptions' => ['class' => 'item product col-sm-422'],
            'options' => ['class' => 'list-view _view_grid'],
            //'summaryOptions' => ['class' => 'summary'],
            'emptyTextOptions' => ['class' => 'col-sm-12 alert alert-info'],
            //'beforeItem'=>function ($model, $key, $index, $widget){
            //return 'ss';
            //}
            'pager' => [
                'options' => ['class' => 'col-sm-12 pagination']
            ]
        ]);
        ?>
    </div>
</div>


