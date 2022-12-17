<?php

use panix\engine\bootstrap\ActiveForm;
use panix\engine\bootstrap\Alert;

/**
 * @var \yii\web\View $this
 */

//\app\web\themes\dashboard\AdminAsset::register($this);
//\panix\engine\widgets\PjaxAsset::register($this);
\panix\engine\assets\BootstrapNotifyAsset::register($this);
$form = ActiveForm::begin(['options'=>['csrf'=>false]]);
?>
    <div class="p-3">
        <?php
        echo Alert::widget([
            'options' => [
                'class' => 'alert-info',
            ],
            'body' => 'Внимание товары которые привязаны к валюте и/или используют конфигурации изменены не будут',
        ]);
        echo $form->field($model, 'price');

        ?>
    </div>
<?php ActiveForm::end(); ?>