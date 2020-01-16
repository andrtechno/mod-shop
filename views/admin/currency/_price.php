
<?= $form->field($model, 'penny')->dropDownList([0 => Yii::t('app', 'NO'), 2 => Yii::t('app', 'YES')]) ?>
<?= $form->field($model, 'separator_thousandth')->dropDownList($model::fpSeparator(),['prompt'=>Yii::t('app','NO')]) ?>
<?= $form->field($model, 'separator_hundredth')->dropDownList($model::fpSeparator(),['prompt'=>Yii::t('app','NO')]) ?>

