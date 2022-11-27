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
        <h5 class="card-title"><?= $this->context->pageName ?></h5>
    </div>
    <div class="card-body">
        <?php
        $tabs[]=[
            'label' => $model::t('TAB_GLOBAL'),
            'content' => $this->render('_global', ['form' => $form, 'model' => $model]),
            'active' => true,
            'options' => ['id' => 'global'],
        ];
        $tabs[]=[
            'label' => $model::t('TAB_FORMAT_PRICE'),
            'content' => $this->render('_price', ['form' => $form, 'model' => $model]),
            'options' => ['id' => 'price'],
        ];
        if(!$model->isNewRecord){
            $tabs[]=[
                'label' => $model::t('TAB_GRAPHIC'),
                'content' => $this->render('_history_rate', ['form' => $form, 'model' => $model]),
                'options' => ['id' => 'history_rate'],
            ];
        }
        echo yii\bootstrap4\Tabs::widget([
            'items' => $tabs,
        ]);
        ?>
    </div>
    <div class="card-footer text-center">
        <?= $model->submitButton(); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>



