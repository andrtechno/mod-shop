<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;
//use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\ProductType;
?>

<div id="test"></div>


<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::encode($this->context->pageName) ?></h3>
    </div>
    <div class="panel-body">



        <?php
        if (!$model->isNewRecord && Yii::$app->settings->get('shop', 'auto_gen_url')) {
            echo Yii::t('shop/admin', 'ENABLE_AUTOURL_MODE');
        }


        $typesList = ProductType::find()->all();
        if (count($typesList) > 0) {
// If selected `configurable` product without attributes display error
            if ($model->isNewRecord && $model->use_configurations == true && empty($model->configurable_attributes))
                $attributeError = true;
            else
                $attributeError = false;

            if ($model->isNewRecord && !$model->type_id || $attributeError === true) {
                // Display "choose type" form
                echo Html::beginForm('', 'get', array('class' => 'form-horizontal'));
                panix\mod\shop\assets\admin\ProductAsset::register($this);

                if ($attributeError) {
                    echo Yii::t('shop/admin', 'Выберите атрибуты для конфигурации продуктов.');
                }
                ?>
                <div class="form-group">
                    <div class="col-sm-4"><?= Html::activeLabel($model, 'type_id', array('class' => 'control-label')); ?></div>
                    <div class="col-sm-8">
                        <?php echo Html::activeDropDownList($model, 'type_id', ArrayHelper::map($typesList, 'id', 'name')); ?>


                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-4"><?= Html::activeLabel($model, 'use_configurations', array('class' => 'control-label')); ?></div>
                    <div class="col-sm-8">
                        <?php echo Html::activeDropDownList($model, 'use_configurations', array(0 => Yii::t('app', 'NO'), 1 => Yii::t('app', 'YES'))); ?>


                    </div>
                </div>

                <div id="availableAttributes" class="form-group hidden"></div>

                <div class="form-group text-center">
                    <?= Html::submitButton(Yii::t('app', 'CREATE', 0), array('name' => false, 'class' => 'btn btn-success')); ?>
                </div>
                <?php
                echo Html::endForm();
            } else {


                $form = ActiveForm::begin([
                            'id' => basename(get_class($model)),
                            'options' => [
                                'class' => 'form-horizontal',
                                'enctype' => 'multipart/form-data'
                            ]
                ]);

                echo yii\bootstrap\Tabs::widget([
                    //'encodeLabels'=>true,
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
                            'label' => 'Связи товаров',
                            'content' => $this->render('tabs/_related', ['exclude' => $model->id, 'form' => $form, 'model' => $model]),
                            'headerOptions' => [],
                            'options' => ['id' => 'related'],
                        ],
                        [
                            'label' => 'Категории',
                            'content' => $this->render('tabs/_tree', ['exclude' => $model->id, 'form' => $form, 'model' => $model]),
                            'headerOptions' => [],
                            'options' => ['id' => 'tree'],
                        ],
                        [
                            'label' => (isset($this->context->tab_errors['attributes'])) ? Html::icon('warning', ['class' => 'text-danger']) . ' Характеристики' : 'Характеристики',
                            'encode' => false,
                            'content' => $this->render('tabs/_attributes_old', ['form' => $form, 'model' => $model]),
                            //'linkOptions' => ['class'=>'text-danger'],
                            'options' => ['id' => 'attributes'],
                        ],
                    ],
                ]);
                ?>
                <div class="form-group text-center">
                    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                </div>



                <?php
                ActiveForm::end();
            }
        } else {
            echo 'Для начало необходимо создать Тип товара';
        }
        ?>
    </div>
</div>

