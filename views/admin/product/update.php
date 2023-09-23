<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;

//use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\ProductType;

/**
 * @var \yii\web\View $this
 * @var \panix\mod\shop\models\Product $model
 */


if (!$model->isNewRecord && Yii::$app->settings->get('shop', 'auto_gen_url')) {
    echo Yii::t('shop/admin', 'ENABLE_AUTOURL_MODE');
}
if(Yii::$app->db->driverName == 'pgsql' && !$model->isNewRecord){
    print_r($model->options);
}
?>

<?php
$typesList = ProductType::find()->all();
if (count($typesList) > 0) {
    // If selected `configurable` product without attributes display error
    if ($model->isNewRecord && $model->use_configurations == true && empty($model->configurable_attributes))
        $attributeError = true;
    else
        $attributeError = false;

    if ($model->isNewRecord && !$model->type_id || $attributeError === true) {
        ?>


        <?php
        echo Html::beginForm('', 'GET');
        ?>
        <div class="card">
            <div class="card-header">
                <h5><?= Html::encode($this->context->pageName) ?></h5>
            </div>
            <div class="card-body">
                <?php


                panix\mod\shop\bundles\admin\ProductAsset::register($this);

                if ($attributeError) {
                    echo '<div class="alert alert-danger">' . Yii::t('shop/admin', 'SELECT_ATTRIBUTE_PRODUCT') . '</div>';
                }
                ?>

                <div class="form-group row">
                    <div class="col-sm-4"><?= Html::activeLabel($model, 'type_id', ['class' => 'control-label']); ?></div>
                    <div class="col-sm-8">
                        <?php echo Html::activeDropDownList($model, 'type_id', ArrayHelper::map($typesList, 'id', 'name'), ['class' => 'form-control']); ?>
                    </div>
                </div>
                <?php if (true) { ?>
                    <div class="form-group row">
                        <div class="col-sm-4"><?= Html::activeLabel($model, 'use_configurations', ['class' => 'control-label']); ?></div>
                        <div class="col-sm-8">
                            <?php echo Html::activeDropDownList($model, 'use_configurations', [0 => Yii::t('app/default', 'NO'), 1 => Yii::t('app/default', 'YES')], ['class' => 'form-control']); ?>
                        </div>
                    </div>

                    <div id="availableAttributes" class="form-group d-none"></div>
                <?php } ?>


            </div>
            <div class="card-footer text-center">
                <?= Html::submitButton(Yii::t('app/default', 'CREATE', 0), ['name' => false, 'class' => 'btn btn-success']); ?>
            </div>
        </div>
        <?= Html::endForm(); ?>

    <?php } else { ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5><?= Html::encode($this->context->pageName) ?></h5>
                    </div>
                    <?php


                    $form = ActiveForm::begin([
                        'id' => 'product-form',
                        'options' => [
                            'enctype' => 'multipart/form-data',
                            'data-pjax' => 0
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
                            'label' => $model::t('TAB_KIT'),
                            'content' => $this->render('tabs/_kit', ['exclude' => $model->id, 'form' => $form, 'model' => $model]),
                            'headerOptions' => [],
                            'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                            'visible' => false,
                        ];
                        $tabs[] = [
                            'label' => $model::t('TAB_VARIANTS'),
                            'content' => $this->render('tabs/_variations', ['model' => $model]),
                            'headerOptions' => [],
                            'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                        ];

                        $tabs[] = [
                            'label' => Yii::t('seo/default', 'TAB_SEO'),
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
                            'content' => $this->render('tabs/_attributes', ['form' => $form, 'model' => $model, 'eavList' => $eavList]),
                            'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                        ];


                        if ($model->use_configurations) {
                            $tabs[] = [
                                'label' => $model::t('USE_CONFIGURATIONS'),
                                'content' => $this->render('tabs/_configurations', ['product' => $model]),
                                'headerOptions' => [],
                                'options' => ['class' => 'flex-sm-fill text-center nav-item'],
                                'visible' => true,
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
                        <?= $model->submitButton(); ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>


            </div>
            <div class="col-lg-4">

                <div class="card">
                    <div class="card-header">
                        <h5>Информация о товаре</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="form-group row">
                            <div class="col-sm-8 col-md-6">ID</div>
                            <div class="col-sm-4 col-md-6 font-weight-bold"><?= $model->id; ?></div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-8 col-md-6"><?= Yii::t('shop/admin','DATE_CREATE'); ?></div>
                            <div class="col-sm-4 col-md-6 font-weight-bold"><?= \panix\engine\CMS::date($model->created_at); ?></div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-8 col-md-6"><?= Yii::t('shop/admin','DATE_UPDATE'); ?></div>
                            <div class="col-sm-4 col-md-6 font-weight-bold"><?= \panix\engine\CMS::date($model->updated_at); ?></div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-8 col-md-6"><?= Yii::t('shop/admin','DATE_LAST_BUY'); ?></div>
                            <div class="col-sm-4 col-md-6 font-weight-bold"><?= \panix\engine\CMS::date($model->added_to_cart_date); ?></div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-8 col-md-6"><?= Yii::t('shop/admin','BUYING'); ?></div>
                            <div class="col-sm-4 col-md-6 font-weight-bold"><?= $model->added_to_cart_count; ?></div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12"><?= Yii::t('app/default', 'VIEWS', ['n' => $model->views]); ?><?php // Html::a('Очистить просмотры', ['/shop/admin/product/update-views','id'=>$model->id],['class'=>'btn btn-sm','onclick'=>"updateProductsViews(this); return false;",'data-confirm'=>'Вы уверены, что хотите обновить просмотры?']); ?></div>
                        </div>
                        <?php

                        $this->registerJs("
                        
                        function updateProductsViews(that){
                            $.ajax({
                                url:$(that).attr('href'),
                                type:'POST',
                                success:function(response){
                                
                                }
                            });
                        return false;
                        }
                        
                        ", \yii\web\View::POS_END);

                        $revCount = $model->getReviews()->count();
                        if ($revCount) { ?>
                            <div class="form-group row">
                                <div class="col-sm-12"><?= Html::a(Yii::t('app/default', 'REVIEWS', ['n' => $revCount]), ['/shop/admin/reviews/index', Html::getInputName(new \panix\mod\shop\models\search\ProductReviewsSearch(), 'product_id') => $model->id]); ?></div>
                            </div>
                        <?php } ?>
                        <?php if ($model->user_id) { ?>
                            <div class="form-group row">
                                <div class="col-sm-8 col-md-6"><?= Yii::t('shop/admin','ADD_BY'); ?></div>
                                <div class="col-sm-4 col-md-6 font-weight-bold"><?= Html::a($model->user->username, $model->user->getUpdateUrl()); ?></div>
                            </div>
                        <?php } ?>
                        <?php if (is_object($model->hasMarkup)) { ?>
                            <div class="form-group row">
                                <div class="col-sm-8 col-md-6"><?= Yii::t('shop/admin','MARKUP_APPLIED'); ?></div>
                                <div class="col-sm-4 col-md-6">
                                    <?= $model->hasMarkup->name; ?>: <span
                                            class="font-weight-bold"><?= $model->hasMarkup->sum; ?></span>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (Yii::$app->settings->get('shop', 'enable_reviews')) { ?>
                            <div class="form-group row">
                                <div class="col-sm-4 col-md-6"><?= Yii::t('shop/default', 'RATING_SCORE', $model->ratingScore); ?></div>
                                <div class="col-sm-8 col-md-6">
                                    <?php
                                    echo \panix\ext\rating\RatingInput::widget([
                                        'name' => 'product-rating',
                                        //'id' => 'product-rating',
                                        'value' => $model->ratingScore,
                                        'options' => [
                                            'hints' => [
                                                Yii::t('shop/default', 'RATING_SCORE', $model->ratingScore),
                                                Yii::t('shop/default', 'RATING_SCORE', $model->ratingScore),
                                                Yii::t('shop/default', 'RATING_SCORE', $model->ratingScore),
                                                Yii::t('shop/default', 'RATING_SCORE', $model->ratingScore),
                                                Yii::t('shop/default', 'RATING_SCORE', $model->ratingScore),
                                            ],
                                            'readOnly' => true,
                                        ]
                                    ]);
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><?= Yii::t('shop/admin','GRAPH_PRICE_CHANGE'); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php
                        echo $this->render('_prices_history', ['model' => $model]);
                        ?>
                    </div>
                </div>

            </div>
        </div>

    <?php } ?>
<?php }else{ ?>
    <div class="alert alert-warning"><?= Yii::t('shop/admin','WARN_NO_PRODUCT_TYPE'); ?></div>
<?php } ?>
