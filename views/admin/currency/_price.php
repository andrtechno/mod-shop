
<?= $form->field($model, 'penny')->dropDownList([0 => Yii::t('app/default', 'NO'), 2 => Yii::t('app/default', 'YES')]) ?>
<?= $form->field($model, 'separator_thousandth')->dropDownList($model::fpSeparator(),['prompt'=>Yii::t('app/default','NO')]) ?>
<?= $form->field($model, 'separator_hundredth')->dropDownList($model::fpSeparator(),['prompt'=>Yii::t('app/default','NO')]) ?>

