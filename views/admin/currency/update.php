<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;


?>

<?php
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal']
]);
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $this->context->pageName ?></h3>
    </div>
    <div class="card-body">
        <?php
        echo yii\bootstrap4\Tabs::widget([
            'items' => [
                [
                    'label' => 'Общие',
                    'content' => $this->render('_global', ['form' => $form, 'model' => $model]),
                    'active' => true,
                    'options' => ['id' => 'global'],
                ],
                [
                    'label' => 'Формат цены',
                    'content' => $this->render('_price', ['form' => $form, 'model' => $model]),
                    'options' => ['id' => 'price'],
                ],
            ],
        ]);
        ?>
    </div>
    <div class="card-footer text-center">
        <?= $model->submitButton(); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>




