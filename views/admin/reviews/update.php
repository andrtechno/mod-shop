<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $this \yii\web\View
 */
$reviewModel = new \panix\mod\shop\models\ProductReviews();

echo \panix\ext\fancybox\Fancybox::widget([
    'target' => 'a[data-fancybox]',
    'options' => [
        'onInit' => new \yii\web\JsExpression('function(){
            console.log("fancybox.init");

        }'),
        /*'touch' => [
            'vertical' => false,
            'momentum' => false
        ],*/
        'modal' => false,
        'touch' => false,
        'beforeShow' => new \yii\web\JsExpression('function(instance, current ) {

        }')
    ]
]);

/*
echo \panix\ext\fancybox\Fancybox::widget([
    'target' => '#review-button',
    'options' => [
        'onInit' => new \yii\web\JsExpression('function(){
            console.log("fancybox.init");

        }'),
        'touch' => false,
        'beforeShow' => new \yii\web\JsExpression('function(instance, current ) {
           // $(".fancybox-bg").css({"background":"transparent"});
            //common.notify("test!",\'success\');
        }')
    ]
]);*/

?>


    <div class="row">
        <div class="col-sm-7">
            <?php
            $form = ActiveForm::begin([
                'options' => ['enctype' => 'multipart/form-data']
            ]);
            ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="float-left"><?= Html::encode($this->context->pageName) ?></h5>
                    <div class="float-right mr-3 mt-2">
                        <?php
                        echo \panix\ext\rating\RatingInput::widget([
                            'model' => $model,
                            'attribute' => 'rate',
                            'options' => [
                                'readOnly' => true,
                                //'starOff' => $this->theme->asset[1] . '/img/star-off.svg',
                                //'starOn' => $this->theme->asset[1] . '/img/star-on.svg',

                            ]
                        ]);
                        ?>
                    </div>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'text')->textarea(); ?>
                    <?= $form->field($model, 'status')->dropDownList($model->getStatusList()); ?>
                </div>
                <div class="card-footer text-center">
                    <?= $model->submitButton(); ?>
                    <a class="btn btn-outline-info" data-fancybox data-type="ajax"
                       data-src="<?= \yii\helpers\Url::to(['reply-add', 'id' => $model->id]); ?>" href="javascript:;">
                        <?= $reviewModel::t('BTN_ANSWER'); ?>
                    </a>
                </div>
            </div>
            <?php ActiveForm::end(); ?>


            <div class="card">
                <div class="card-header">
                    <h5>Ответы</h5>
                </div>
                <div class="card-body p-3">
                    <?php
                    Pjax::begin(['timeout' => false, 'id' => 'tester-p', 'enablePushState' => false, 'enableReplaceState' => false]);
                    $descendants = $model->children()->orderBy(['id' => SORT_DESC])->all();
                    echo $this->render('_items', ['items' => $descendants]);
                    Pjax::end();

                    ?>
                </div>
            </div>


            <?php
            $js = <<<JS
$(document).on('submit', '#review-product-form2',function(){
    var that = $(this);
    $.ajax({
        url:$(this).attr('action'),
        type:'POST',
        data:$(this).serialize(),
        dataType:'json',
        success:function(response) {
            console.log(response);
           // if(response.success){
               // $('#review-modal').modal('hide');
                common.notify(response.message,'success');
                if(response.published){
                    $.pjax.reload('#tester-p',{timeout:false});
                }

               // $('input',that).val('');
               // $('textarea',that).val('');

                var instance = $.fancybox.getInstance();
                console.log(instance);
                instance.close();
           // }
        }
    });
    return false;
});
JS;
            $this->registerJs($js);
            ?>
        </div>
        <div class="col-sm-5">
            <?= $this->render('_product', ['model' => $model]); ?>
            <?php
            echo $model->getDisplayName();
            ?>
            <ul class="list-group">
                <?php
                $browser = new \panix\engine\components\Browser($model->user_agent);
                ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="d-flex align-items-center mr-4"><?= $model->getAttributeLabel('ip_create'); ?>:</span>
                    <span class="m-0"><?= \panix\engine\CMS::ip($model->ip_create); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="d-flex align-items-center mr-4"><?= $model->getAttributeLabel('created_at'); ?>:</span>
                    <span class="m-0"><?= \panix\engine\CMS::date($model->created_at); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="d-flex align-items-center mr-4"><?= $model->getAttributeLabel('user_agent'); ?>:</span>
                    <span class="m-0 text-right">
                    <?= $browser->getBrowser(); ?> (v <?= $browser->getVersion(); ?>)
                    <br/>
                        <?= $browser->getPlatformIcon(); ?> <?= $browser->getPlatform(); ?>
                </span>
                </li>
            </ul>

        </div>
    </div>

<?php

$this->registerJs("
$(document).on('pjax:beforeSend', function(xhr, options) {
  $(xhr.target).addClass('pjax-loading');
  console.log(xhr, options);
});
$(document).on('pjax:complete', function(xhr, options) {
    $(xhr.target).removeClass('pjax-loading');
});
$(document).on('click','.change-status',function(){
    /*$.ajax({
        url:$(this).attr('href'),
        type:'POST',
        success:function(data){
          //  $.pjax.reload('#tester-p',{timeout:false});
        }
    });*/
    return false;

});

$(document).on('click','.delete',function(){
    $.ajax({
        url:$(this).attr('href'),
        type:'POST',
        success:function(data){
            if(data.success){
                common.notify(data.message,'success');
                $.each(data.objects,function(k,v){
                    $('#comment-'+v).remove();
                });
            }
        }
    });
    return false;
});
");

