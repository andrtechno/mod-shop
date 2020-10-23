<?php
use panix\engine\bootstrap\ActiveForm;
use yii\helpers\Html;

$reviewModel = new \panix\mod\shop\models\ProductReviews();
?>
<div>
    <?php $form = ActiveForm::begin([
        'id' => 'review-product-form2',
        'enableAjaxValidation' => true,
        'action' => ['/admin/shop/reviews/reply-add', 'id' => Yii::$app->request->get('id')],
        'validationUrl' => ['/admin/shop/reviews/reply-add', 'id' => Yii::$app->request->get('id'),'validate'=>true]

    ]); ?>

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
        <?php echo $form->field($reviewModel, 'text',['template' => '{label}{input}{hint}{error}'])->textarea(['rows' => 5]); ?>
    </div>
    <div class="text-center">
        <?php echo Html::submitButton($reviewModel::t('BTN_SUBMIT'), ['class' => 'btn btn-outline-danger','name'=>'submit','value'=>1]); ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
