<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;
//use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\ProductType;

?>


    <div class="card">
        <div class="card-header">
            <h5><?= Html::encode($this->context->pageName) ?></h5>
        </div>


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
                // Display "choose type" form?>


                <?php
                echo Html::beginForm('', 'get');
                panix\mod\shop\assets\admin\ProductAsset::register($this);

                if ($attributeError) {
                    $this->theme->alert('danger', Yii::t('shop/admin', 'Выберите атрибуты для конфигурации продуктов.'), false);
                }
                ?>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-sm-4"><?= Html::activeLabel($model, 'type_id', ['class' => 'control-label']); ?></div>
                        <div class="col-sm-8">
                            <?php echo Html::activeDropDownList($model, 'type_id', ArrayHelper::map($typesList, 'id', 'name'), ['class' => 'form-control']); ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-4"><?= Html::activeLabel($model, 'use_configurations', ['class' => 'control-label']); ?></div>
                        <div class="col-sm-8">
                            <?php echo Html::activeDropDownList($model, 'use_configurations', [0 => Yii::t('app', 'NO'), 1 => Yii::t('app', 'YES')], ['class' => 'form-control']); ?>
                        </div>
                    </div>

                    <div id="availableAttributes" class="form-group d-none"></div>


                </div>
                <div class="card-footer text-center">

                    <?= Html::submitButton(Yii::t('app', 'CREATE', 0), ['name' => false, 'class' => 'btn btn-success']); ?>

                </div>
                <?php
                echo Html::endForm(); ?>

                <?php
            } else {
                ?>

                <?php

                $form = ActiveForm::begin([
                    'id' => strtolower(basename(get_class($model))) . '-form',
                    'options' => [
                        'enctype' => 'multipart/form-data'
                    ]
                ]);
                ?>
                <div class="card-body">
                    <?php

                    $tabs = [];


                    $tabs[] = [
                        'label' => $model::t('TAB_MAIN'),
                        'content' => $this->render('tabs/_main', ['form' => $form, 'model' => $model]),
                        'active' => true,
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];
                    $tabs[] = [
                        'label' => $model::t('TAB_WAREHOUSE'),
                        'content' => $this->render('tabs/_warehouse', ['form' => $form, 'model' => $model]),
                        'headerOptions' => [],
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];
                    $tabs[] = [
                        'label' => $model::t('TAB_IMG'),
                        'content' => $this->render('tabs/_images', ['form' => $form, 'model' => $model]),
                        'headerOptions' => [],
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];
                    $tabs[] = [
                        'label' => $model::t('TAB_REL'),
                        'content' => $this->render('tabs/_related', ['exclude' => $model->id, 'form' => $form, 'model' => $model]),
                        'headerOptions' => [],
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];
                    $tabs[] = [
                        'label' => $model::t('TAB_VARIANTS'),
                        'content' => $this->render('tabs/_variations', ['model' => $model]),
                        'headerOptions' => [],
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];

                    $tabs[] = [
                        'label' => $model::t('TAB_SEO'),
                        'content' => $this->render('@seo/views/admin/default/_module_seo', ['model' => $model]),
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];


                    $tabs[] = [
                        'label' => $model::t('TAB_CATEGORIES'),
                        'content' => $this->render('tabs/_tree', ['exclude' => $model->id, 'form' => $form, 'model' => $model]),
                        'headerOptions' => [],
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];
                    $tabs[] = [
                        'label' => (isset($this->context->tab_errors['attributes'])) ? Html::icon('warning', ['class' => 'text-danger']) . ' Характеристики' : 'Характеристики',
                        'encode' => false,
                        'content' => $this->render('tabs/_attributes', ['form' => $form, 'model' => $model]),
                        'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                    ];


                    if ($model->use_configurations) {
                        $tabs[] = [
                            'label' => 'UPDATE_PRODUCT_TAB_CONF',
                            'content' => $this->render('tabs/_configurations', ['product' => $model]),
                            'headerOptions' => [],
                            'itemOptions' => ['class' => 'flex-sm-fill text-center nav-item'],
                        ];
                    }

                    echo \panix\engine\bootstrap\Tabs::widget([
                        //'encodeLabels'=>true,
                        'options' => [
                            'class' => 'nav-pills flex-column flex-sm-row nav-tabs-static'
                        ],
                        'items' => $tabs,
                    ]);

                    ?>


                </div>
                <div class="card-footer text-center">
                    <?= Html::submitButton(Yii::t('app', 'SAVE'), ['class' => 'btn btn-success']) ?>
                    <?= Html::submitButton(Yii::t('app', 'SAVE_RETURN'), ['class' => 'btn btn-link', 'value' => \yii\helpers\Url::to(['index']), 'name' => 'redirect']) ?>
                </div>
                <?php
                ActiveForm::end();
            }
        } else {
            $this->theme->alert('warning', Yii::t('shop/admin', 'Для начало необходимо создать Тип товара'), false);
        }
        ?>


    </div>
<?php

$this->registerJs('
$(document).ready(function () {
        $("body").on("beforeSubmit111", "form#product-form", function () {
            var form = $(this);
            // return false if form still have some validation errors
            if (form.find(".has-error").length) 
            {
                return false;
            }
            // submit form
            
               var $input = $("#product-file");
    var fd = new FormData;
    
      fd.append(\'img\', $input.prop(\'files\')[0]);
            $.ajax({
            url    : form.attr("action"),
            type   : "post",
             processData: false,
        contentType: false,
            data   : fd, //form.serialize(),
            success: function (response) 
            {
                var getupdatedata = $(response).find("#filter_id_test");
                // $.pjax.reload("#note_update_id"); for pjax update
                $("#yiiikap").html(getupdatedata);
                //console.log(getupdatedata);
            },
            error  : function () 
            {
                console.log(\'internal server error\');
            }
            });
            return false;
         });
    });
');