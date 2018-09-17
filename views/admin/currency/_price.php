
<?= $form->field($model, 'penny')->dropDownList(array(0 => Yii::t('app', 'NO'), 2 => Yii::t('app', 'YES'))) ?>
<?= $form->field($model, 'separator_thousandth')->dropDownList($model::fpSeparator()) ?>
<?= $form->field($model, 'separator_hundredth')->dropDownList($model::fpSeparator()) ?>

