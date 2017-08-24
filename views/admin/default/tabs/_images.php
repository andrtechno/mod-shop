<?php

use yii\helpers\Html;
?>


<?= $form->field($model, 'image')->fileInput() ?>


<?= Html::img($model->getBehavior('image')->getUrl('thumb')); ?>
<?= Html::img($model->getBehavior('image')->getUrl('background')); ?>
<?= Html::img($model->getBehavior('image')->getUrl('main')); ?>

