<?php

use yii\widgets\Pjax;
use yii\bootstrap4\ActiveForm;
use panix\engine\Html;

?>

    <div class="container">
        <div class="text-center mt-5">
            <button type="button" id="review-button" class="btn btn-primary" data-fancybox
                    data-src="#rev-modal">
                <?= $reviewModel::t('BTN_SUBMIT'); ?>
            </button>
        </div>
        <div class="row">


            <div class="col-sm-8 offset-2">
                <?php
                Pjax::begin([
                    'id' => 'pjax-productreview',
                    'timeout' => false,
                ]);
                echo \panix\engine\widgets\ListView::widget([
                    'id' => 'reviews-product-list',
                    'dataProvider' => $provider,
                    'itemView' => $this->context->itemView,
                    'layout' => '{items}{pager}',
                    //'layout' => '{items}{pager}',
                    'emptyText' => 'Отзывов нет.',
                    'options' => ['class' => 'list-view list-comment'],
                    'itemOptions' => ['class' => 'item'],
                    'emptyTextOptions' => ['class' => 'alert alert-info'],
                    //'sorter' => [
                    //'class' => \yii\widgets\LinkSorter::class,
                    //'attributes' => ['price', 'sku']
                    //],
                    'pager' => [
                        'options' => ['class' => 'pagination justify-content-center mt-5']
                    ],


                ]);
                Pjax::end();
                ?>
            </div>
        </div>
    </div>

<?php


$js = <<<JS
var comment_xhr;
$('#review-product-form').on('beforeSubmit', function(){
    var that = $(this);
    
    if (typeof comment_xhr !== 'undefined'){
        console.warn('abort cancel comment xhr');
        comment_xhr.abort();
    }
    var progressBar = $('#progressbar');
    //$('#submit-review').attr('disabled','disabled');  
    comment_xhr = $.ajax({
        url:$(this).attr('action'),
        type:'POST',
        data:$(this).serialize(),
        dataType:'json',
        befoseSend:function(){

        },
        success:function(response) {
            if(response.success){
                
               // $('#review-modal').modal('hide');
                common.notify(response.message,'success');
                if(response.published){
                    $.pjax.reload('#pjax-productreview',{
                        timeout:false,
                    });
                }

                //$('input',that).val('');
                $('textarea',that).val('');
                if(response.score){
                    $('#product-rating').raty('set', {score: response.score});
                    $('#product-rating').raty('reload');
                }
                
              //  var instance = $.fancybox.getInstance();
                $.fancybox.getInstance().close();
                if(response.rated){
                    $('#review-rate').remove();
                }
            }else{
                common.notify(response.message,'error');
                if(response.errors){
                    $.each(response.errors,function(key,error){
                        common.notify(error,'error');
                    });
                }
            }
        }
    });
    return false;
});


$(document).on('pjax:beforeSend', function(xhr, options) {
  $(xhr.target).addClass('pjax-loading');
});
$(document).on('pjax:complete', function(xhr, options) {
    $(xhr.target).removeClass('pjax-loading');
});
JS;

$this->registerJs($js);
?>

    <div style="display: none" id="rev-modal">
        <?php $form = ActiveForm::begin([
            'id' => 'review-product-form',
            //  'options' => ['class' => 'form-auto'],
            // 'enableAjaxValidation' => true,
            'enableClientValidation' => true,
            'action' => ['/shop/product/review-add', 'id' => $model->id],
            'validationUrl' => ['/shop/product/review-add', 'id' => $model->id, 'validate' => true]

        ]);
        // var_dump($reviewModel->checkUserRate());
        ?>


        <?php //if(!$reviewModel->checkUserRate()){ ?>
        <div class="d-flex align-items-center mb-4" id="review-rate">
            <?php if (Yii::$app->user->isGuest) { ?>
                <span><?= Yii::t('default', 'RATING_GUEST_PRODUCT'); ?> <?= Html::a(Yii::t('user/default', 'REGISTRATION'), ['/user/default/reguster']); ?>
                    <?= Yii::t('default', 'OR'); ?> <?= Html::a(Yii::t('user/default', 'LOGIN'), ['/user/default/login']); ?></span>
            <?php } else { ?>

                <span class="mr-2">Ваша оценка</span>
                <?php

                echo \panix\ext\rating\RatingInput::widget([
                    'model' => $reviewModel,
                    'attribute' => 'rate',
                    'options' => [
                        'path' => $this->theme->asset[1] . '/images/',
                        'starOff' => 'star-off.svg',
                        'starOn' => 'star-on.svg',
                        'hints' => [
                            Yii::t('default', 'RATING_1'),
                            Yii::t('default', 'RATING_2'),
                            Yii::t('default', 'RATING_3'),
                            Yii::t('default', 'RATING_4'),
                            Yii::t('default', 'RATING_5'),
                        ],
                    ],
                ]);
                ?>
            <?php } ?>

        </div>
        <?php //} ?>

        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($reviewModel, 'user_name', [
                    'template' => '{label}{input}{hint}{error}'
                ])->textInput(['maxlength' => 50]); ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($reviewModel, 'user_email', [
                    'template' => '{label}{input}{hint}{error}'
                ])->textInput(['maxlength' => 50]); ?>
            </div>
        </div>

        <div>
            <?= $form->field($reviewModel, 'text')->textarea(['rows' => 5]); ?>
        </div>
        <div class="text-center">
            <?= Html::submitButton($reviewModel::t('BTN_SUBMIT'), ['id' => 'submit-review', 'class' => 'btn btn-primary', 'name' => 'submit', 'value' => 1]); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php
