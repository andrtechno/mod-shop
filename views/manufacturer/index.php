<?php

use yii\helpers\Html;
?>

    <div class="products_list">


        <h1><?php echo Html::encode($this->context->dataModel->name); ?></h1>

        <?php if (!empty($this->context->dataModel->description)) { ?>
            <div>
                <?php echo $this->context->dataModel->description ?>
            </div>
        <?php } ?>
          <?php echo $this->context->dataModel->productsCount ?>

        <?php
        echo \yii\widgets\ListView::widget([
            'dataProvider' => $provider,
            'itemView' => '@shop/views/category/_view_grid',
            'layout' => '{summary}{items}{pager}',
            'emptyText' => 'Empty',
            'options' => ['class' => 'row'],
            'itemOptions' => ['class' => 'col-sm-4'],
            'emptyTextOptions' => ['class' => 'alert alert-info']
        ]);
        ?>
    </div>

