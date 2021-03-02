<?php
use yii\helpers\Html;
?>

<div class="modal fade" id="modal-notify" tabindex="-1" role="dialog" aria-labelledby="modal-notify-Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <?php
            $form = \yii\widgets\ActiveForm::begin([
                    'id'=>'notify-form',
                'action' => ['/shop/notify/index','id'=>$product->id],
                'options' => []
            ]);
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="modal-notify-Label"><?= Yii::t('shop/default','NOT_AVAILABLE'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                11<div class="alert alert-info">111Вы уведомим Вас о наличии <strong><?= $product->name; ?></strong></div>


                <?= $form->field($model, 'email')->textInput(['maxlength' => 255]); ?>
            </div>
            <div class="modal-footer">
                <?php
                echo Html::button(Yii::t('app/default', 'CLOSE'), ['class' => 'btn btn-secondary','data-dismiss'=>'modal']);
                echo Html::submitButton(Yii::t('app/default', 'SEND'), ['class' => 'btn btn-success']);
                ?>
            </div>
            <?php \yii\widgets\ActiveForm::end(); ?>
        </div>
    </div>
</div>