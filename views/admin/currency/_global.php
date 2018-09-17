<?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'iso')->textInput(['maxlength' => 10]) ?>
<?= $form->field($model, 'symbol')->textInput(['maxlength' => 10]) ?>
<?= $form->field($model, 'rate')->textInput() ?>
<?= $form->field($model, 'is_main')->checkbox() ?>
<?= $form->field($model, 'is_default')->checkbox() ?>
