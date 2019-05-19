<?php

use yii\helpers\Html;

?>

<div class="container">

    <div class="row">
        <h1><?= Html::encode(($this->h1) ? $this->h1 : $model->name); ?></h1>
        <?php if (!empty($model->description)) { ?>
            <div>
                <?php echo $model->description ?>
            </div>
        <?php } ?>

    </div>
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

