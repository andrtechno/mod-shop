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
        'onInit' => new \yii\web\JsExpression('function(){
            console.log("fancybox.init");

        }'),
        /*'touch' => [
            'vertical' => false,
            'momentum' => false
        ],*/
        'touch' => false,
        'beforeShow' => new \yii\web\JsExpression('function(instance, current ) {
            $(".fancybox-bg").css({"background":"transparent"});
            //common.notify("test!",\'success\');
        }')
    ]
]);

//$test2 = $q->aggregateReviews()->one();


?>
<div class="container">
    <div class="text-center mt-5">
        <button type="button" id="review-button" class="btn btn-lg btn-outline-danger" data-fancybox data-src="#rev-modal">
            <?= $reviewModel::t('BTN_SUBMIT'); ?>
        </button>
    </div>
    <div class="row">


        <div class="col-sm-8 offset-2">
            <?php
            Pjax::begin([
                // 'dataProvider' => $provider,
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
$('#review-product-form').on('submit', function(){
    var that = $(this);
    $.ajax({
        url:$(this).attr('action'),
        type:'POST',
        data:$(this).serialize(),
        dataType:'json',
        success:function(response) {
            console.log(response);
            if(response.success){
               // $('#review-modal').modal('hide');
                common.notify(response.message,'success');
                if(response.published){
                    $.pjax.reload('#pjax-productreview',{timeout:false});
                }

                $('input',that).val('');
                $('textarea',that).val('');

                var instance = $.fancybox.getInstance();
                console.log(instance);
                instance.close();
            }
        }
    });
    return false;
});
JS;

$this->registerJs($js);
?>


<div style="display: none" id="rev-modal">
    <?php $form = ActiveForm::begin([
        'id' => 'review-product-form',
        'options' => ['class' => 'form-auto'],
        'enableAjaxValidation' => true,
        'action' => ['/shop/product/review-add', 'id' => $model->id],
        'validationUrl' => ['/shop/product/review-add', 'id' => $model->id,'validate'=>true]

    ]); ?>


    <div class="d-flex align-items-center mb-4">
        <?php if (Yii::$app->user->isGuest) { ?>
            <p>Чтобы оценить товар необходимо <?= Html::a('зарегистрироваться',['/user/default/reguster']); ?> или <?= Html::a('войти',['/user/default/login']); ?></p>
        <?php } else { ?>

            <p>Ваша оценка</p>
            <?php

            echo \panix\ext\rating\RatingInput::widget([
                'model' => $reviewModel,
                'attribute' => 'rate',
                'options' => [
                    //'cancelOff' => $this->theme->asset[1] . 'cancel-off.png',
                    //'cancelOn' => $this->theme->asset[1] . 'cancel-on.png',
                    //'starHalf' => $this->theme->asset[1] . 'star-half.png',
                    'starOff' => $this->theme->asset[1] . '/img/star-off.svg',
                    'starOn' => $this->theme->asset[1] . '/img/star-on.svg',
                ],
            ]);
            ?>
        <?php } ?>

    </div>
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
        <?php echo Html::submitButton($reviewModel::t('BTN_SUBMIT'), ['class' => 'btn btn-outline-danger','name'=>'submit','value'=>1]); ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
