<?php
use yii\widgets\ActiveForm;


$form = ActiveForm::begin([
    'options' => [
        'id' => 'notify-form',
        'class' => 'form-horizontal'
    ]
]);
?>


<?= $form->field($model, 'email') ?>
<?= $form->field($model, 'product_id')->hiddenInput(['value' => $product->id])->label(false) ?>

<?php ActiveForm::end(); ?>

