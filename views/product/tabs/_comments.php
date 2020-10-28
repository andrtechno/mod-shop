<?php
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use panix\mod\shop\models\ProductReviews;
use yii\helpers\Html;

$reviewModel = new ProductReviews;

//echo $query->createCommand()->rawSql;die;

$provider = new \panix\engine\data\ActiveDataProvider([
    'query' => $model->getReviews()->status(1),
    'pagination' => [
        'pageSize' => 50,
    ]
]);


echo \panix\ext\fancybox\Fancybox::widget([
    'target' => '#review-button',
    'options' => [
        'touch' => false,
        'beforeShow' => new \yii\web\JsExpression('function(instance, current ) {
            $(".fancybox-bg").css({"background":"transparent"});
        }')
    ]
]);


?>

    <div class="container">
        <div class="text-center mt-5">
            <button type="button" id="review-button" class="btn btn-lg btn-outline-danger" data-fancybox
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
                    'itemView' => '_comment_item',
                    'layout' => '{items}{pager}',
                    //'layout' => '{items}{pager}',
                    'emptyText' => 'Отзывов нет.',
                    'options' => ['class' => 'list-view rev-box'],
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
            //console.log(response);
          // $('#submit-review').removeAttr('disabled');
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
  //console.log(xhr, options);
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
                <p>Чтобы оценить товар необходимо <?= Html::a('зарегистрироваться', ['/user/default/reguster']); ?>
                    или <?= Html::a('войти', ['/user/default/login']); ?></p>
            <?php } else { ?>

                <p>Ваша оценка</p>
                <?php

                echo \panix\ext\rating\RatingInput::widget([
                    'model' => $reviewModel,
                    'attribute' => 'rate',
                    'options' => [
                        'path' => $this->theme->asset[1] . '/img/',
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
        <div class="input-line">
            <div class="row">
                <div class="col-sm-6">
                    <?php echo $form->field($reviewModel, 'user_name', [
                        'template' => '{label}{input}{hint}{error}'
                    ])->textInput(['maxlength' => 50]); ?>
                </div>
                <div class="col-sm-6">
                    <?php echo $form->field($reviewModel, 'user_email', [
                        'template' => '{label}{input}{hint}{error}'
                    ])->textInput(['maxlength' => 50]); ?>
                </div>
            </div>
        </div>
        <div class="textarea-line">
            <?php echo $form->field($reviewModel, 'text')->textarea(['rows' => 5]); ?>
        </div>
        <div class="text-center">
            <?php echo Html::submitButton($reviewModel::t('BTN_SUBMIT'), ['id' => 'submit-review', 'class' => 'btn btn-outline-danger', 'name' => 'submit', 'value' => 1]); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php
