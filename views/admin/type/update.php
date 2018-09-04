<?php

use yii\helpers\Html;
?>

<div class="card card-light">
    <div class="card-header">
        <h5><?= Html::encode($this->context->pageName) ?></h5>
    </div>
    <div class="card-body">
        <?php
        echo Html::beginForm('', 'post', array(
            'id' => 'ProductTypeForm',
            'class' => 'form-horizontal'
        ));

        echo Html::hiddenInput('main_category', $model->main_category, ['id' => 'main_category']);
        echo yii\bootstrap4\Tabs::widget([
            'items' => [
                [
                    'label' => $model::t('TAB_OPTIONS'),
                    'content' => $this->render('tabs/_options', ['attributes' => $attributes, 'model' => $model]),
                    'headerOptions' => [],
                    'active' => true,
                    'options' => ['id' => 'options'],
                ],
                [
                    'label' => $model::t('TAB_CATEGORIES'),
                    'content' => $this->render('tabs/_tree', ['model' => $model]),
                    'options' => ['id' => 'tree'],
                ],
            ],
        ]);
        ?>

        <div class="form-group text-center">
            <?= Html::submitButton(Yii::t('app', 'SAVE'), array('class' => 'btn btn-success')); ?>
        </div>

        <?php echo Html::endForm(); ?>

    </div>
</div>