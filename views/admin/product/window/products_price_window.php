<?php

use panix\engine\bootstrap\ActiveForm;
use panix\engine\bootstrap\Alert;
\app\web\themes\dashboard\AdminAsset::register($this);
//\panix\engine\widgets\PjaxAsset::register($this);
$form = ActiveForm::begin();
?>
    <div class="p-3">
        <?php
        echo Alert::widget([
            'options' => [
                'class' => 'alert-info',
            ],
            'body' => 'Внимание товары которые привязаны к валюте и/или используют конфигурации изменены не будут',
        ]);
        echo $form->field($model, 'price')->textInput();

        ?>
    </div>
<?php ActiveForm::end(); ?>