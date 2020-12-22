<?php

/**
 * @var $form \panix\engine\bootstrap\ActiveForm
 * @var $model \panix\mod\shop\models\Category
 */
?>

<?= $form->field($model, 'use_seo_parents')->checkbox() ?>

<?= $form->field($model, 'meta_title')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'meta_description')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'h1')->textInput(['maxlength' => 255]) ?>