<?php
/**
 * @var \panix\engine\bootstrap\ActiveForm $form
 * @var \panix\mod\shop\models\forms\SettingsForm $model
 */
?>

<?= $form->field($model, 'per_page') ?>
<?= $form->field($model, 'product_related_bilateral')->checkbox() ?>
<?= $form->field($model, 'group_attribute')->checkbox() ?>
<?= $form->field($model, 'label_expire_new')->dropDownList($model::labelExpireNew(),['prompt'=>Yii::t('app/default','OFF')]) ?>
