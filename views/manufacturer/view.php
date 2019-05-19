<?php

use yii\helpers\Html;

?>

<div class="container">
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
            <h1><?= Html::encode(($this->h1) ? $this->h1 : $model->name); ?></h1>
            <?php if (!empty($model->description)) { ?>
                <div>
                    <?php echo $model->description ?>
                </div>
            <?php } ?>


            <?php
            echo \yii\widgets\ListView::widget([
                'dataProvider' => $provider,
                'itemView' => '@shop/views/category/_view_grid',
                'layout' => '{summary}{items}{pager}',
                'options' => ['class' => 'row'],
                'itemOptions' => ['class' => 'col-sm-4'],
                'emptyTextOptions' => ['class' => 'col alert alert-info']
            ]);
            ?>
        </div>
    </div>
</div>

