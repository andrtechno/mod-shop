<?php

use panix\engine\bootstrap\ActiveForm;

/**
 * @var \yii\web\View $this
 */

$form = ActiveForm::begin(['options' => ['csrf' => false]]);
?>
    <div class="p-3">
        <div class="alert alert-info">Внимание товары которые привязаны к валюте и/или используют конфигурации изменены
            не будут
        </div>
        <?= $form->field($model, 'price'); ?>
    </div>
<?php ActiveForm::end(); ?>