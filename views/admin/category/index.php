<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;

use panix\ext\taginput\TagInput;


?>

<div class="row">
    <div class="col-sm-12 col-md-7 col-lg-8">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
        <div class="card">
            <div class="card-header">
                <h5><?= Html::encode($this->context->pageName) ?></h5>
            </div>

            <div class="card-body">


                <?php


                $tabs = [];


                $tabs[] = [
                    'label' => $model::t('TAB_MAIN'),
                    'content' => $this->render('_main', ['form' => $form, 'model' => $model]),
                    'active' => true,
                    'options' => ['class' => 'text-center nav-item'],
                ];
                $tabs[] = [
                    'label' => Yii::t('seo/default','TAB_SEO'),
                    'content' => $this->render('@seo/views/admin/default/_module_seo', ['model' => $model]),
                    'options' => ['class' => 'text-center nav-item'],
                ];

                echo \panix\engine\bootstrap\Tabs::widget([
                    //'encodeLabels'=>true,
                    'options' => [
                        'class' => 'nav-pills'
                    ],
                    'items' => $tabs,
                ]);

                ?>


            </div>
            <div class="card-footer text-center">

                <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

            </div>

        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="col-sm-12 col-md-5 col-lg-4">
        <?= $this->render('_category', ['model' => $model]); ?>
    </div>
</div>
