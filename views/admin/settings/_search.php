<?php

use panix\mod\shop\models\Product;


/**
 * @var $form \panix\engine\bootstrap\ActiveForm
 * @var $model \panix\mod\shop\models\forms\SettingsForm
 * @var $this \yii\web\View
 */
?>


<?= $form->field($model, 'search_availability')->checkboxList(Product::getAvailabilityItems()) ?>
<?= $form->field($model, 'search_limit')->textInput() ?>


