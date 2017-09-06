<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
echo Yii::t('app', 'Чтобы атрибут отображался в товарах его необходимо добавить к необходимому {productType}', array('productType' => Html::a('типу товара', '/admin/shop/productType')));


?>


<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::encode($this->context->pageName) ?></h3>
    </div>
    <div class="panel-body">


        <?php
        $form = ActiveForm::begin([
                    'id' => basename(get_class($model)),
                    'options' => [
                        'class' => 'form-horizontal',
                        'enctype' => 'multipart/form-data'
                    ]
        ]);
        ?>
        <?php
        echo yii\bootstrap\Tabs::widget([

            'items' => [
                [
                    'label' => 'Основные',
                    'content' => $this->render('tabs/_main', ['form' => $form, 'model' => $model]),
                    'active' => true,
                    'options' => ['id' => 'main'],
                ],
                [
                    'label' => 'Опции',
                    'content' => $this->render('tabs/_options', ['form' => $form, 'model' => $model]),
                    'headerOptions' => [],
                    'options' => ['id' => 'options'],
                ],
            ],
        ]);
        ?>









        <div class="form-group text-center">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>





        <?php ActiveForm::end(); ?>



    </div>
</div>