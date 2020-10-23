<?php
use yii\widgets\ActiveForm;
use panix\engine\Html;


?>

<?php $formAnswer = ActiveForm::begin([
    'id' => 'review-product-form',
    'enableAjaxValidation' => true,
    'action' => ['/admin/shop/reviews/reply-add', 'id' => $model->id],
    //'validationUrl' => ['/shop/product/review-validate', 'id' => $model->id]

]); ?>

<div class="input-line">
    <div class="row">
        <div class="col-sm-6">
            <?php echo $formAnswer->field($reviewModel, 'user_name', [
                'template' => '{label}{input}{hint}{error}'
            ])->textInput(['maxlength' => 50]); ?>
        </div>
        <div class="col-sm-6">
            <?php echo $formAnswer->field($reviewModel, 'user_email', [
                'template' => '{label}{input}{hint}{error}'
            ])->textInput(['maxlength' => 50]); ?>
        </div>
    </div>
</div>
<div class="textarea-line">
    <?php echo $formAnswer->field($reviewModel, 'text')->textarea(['rows' => 5]); ?>
</div>
<div class="text-center">
    <?php echo Html::submitButton($reviewModel::t('BTN_SUBMIT'), ['class' => 'btn btn-outline-danger']); ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$('#review-product-form').on('beforeSubmit', function(){
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
