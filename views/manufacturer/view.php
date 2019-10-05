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

        <div id="listview-ajax">
            <?php
            echo $this->render('listview',[
                'provider' => $provider,
                'itemView'=>$this->context->itemView
            ]);
            ?>

        </div>
    </div>
</div>


