
<?= $form->field($model, 'quantity')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'discount')->textInput(['maxlength' => 255])->hint($model::t('HINT_DISCOUNT')) ?>
<?php //echo $form->field($model, 'archive')->checkbox() ?>

<?=

$form->field($model, 'auto_decrease_quantity')->dropDownList([
                                0 => Yii::t('app', 'NO'),
                                1 => Yii::t('app', 'YES')
                            ], [
    //'prompt' => 'Укажите производителя'
])->hint($model::t('HINT_AUTO_DECREASE_QUANTITY'));
?>

<?=

$form->field($model, 'availability')->dropDownList($model::getAvailabilityItems(), [
    //'prompt' => 'Укажите производителя'
]);
?>
 