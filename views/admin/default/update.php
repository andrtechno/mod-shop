<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm; //widgets
use yii\bootstrap\Dropdown;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::encode($this->context->pageName) ?></h3>
    </div>
    <div class="panel-body">


        <?php
        $form = ActiveForm::begin([

                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-4',
                            'offset' => 'col-sm-offset-4',
                            'wrapper' => 'col-sm-8',
                            'error' => '',
                            'hint' => '',
                        ],
                    ],
                    'options' => ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data']
        ]);
        ?>
        <?php
        echo yii\bootstrap\Tabs::widget([
            'items' => [
                [
                    'label' => $model::t('TAB_MAIN'),
                    'content' => $this->render('tabs/_main', ['form' => $form, 'model' => $model]),
                    'active' => true,
                    'options' => ['id' => 'main'],
                ],
                [
                    'label' => 'Изображение',
                    'content' => $this->render('tabs/_images', ['form' => $form, 'model' => $model]),
                    'headerOptions' => [],
                    'options' => ['id' => 'images'],
                ],
                [
                    'label' => 'Example',
                    'url' => 'http://www.corner-cms.com',
                ],
                [
                    'label' => 'Dropdown',
                    'items' => [
                        [
                            'label' => 'DropdownA',
                            'content' => 'DropdownA, Anim pariatur cliche...',
                        ],
                        [
                            'label' => 'DropdownB',
                            'content' => 'DropdownB, Anim pariatur cliche...',
                        ],
                    ],
                ],
            ],
        ]);
        ?>









        <div class="form-group text-center">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>




        <?php
        foreach ($model->getEavAttributes()->all() as $attr) {
            //print_r($attr);
            echo $attr->name;
            echo $form->field($model, $attr->name, ['class' => '\mirocow\eav\widgets\ActiveField'])->eavInput();
        }
        ?>

        <?php ActiveForm::end(); ?>



    </div>
</div>


<?=
\mirocow\eav\admin\widgets\Fields::widget([
    'model' => $model,
    'categoryId' => $model->id,
    'entityName' => 'product',
    'entityModel' => 'app\system\modules\shop\models\ShopProduct',
])?>