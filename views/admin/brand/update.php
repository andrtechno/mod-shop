<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;


?>


<?php
$form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data']
]);
?>
    <div class="card">
        <div class="card-header">
            <h5><?= Html::encode($this->context->pageName) ?></h5>
        </div>
        <div class="card-body">
            <?php
            $tabs = [];


            $tabs[] = [
                'label' => $model::t('TAB_MAIN'),
                'content' => $this->render('_main', ['form' => $form, 'model' => $model]),
                'active' => true,
                'options' => ['class' => 'flex-sm-fill text-center nav-item'],
            ];

            $tabs[] = [
                'label' => Yii::t('seo/default','TAB_SEO'),
                'content' => $this->render('@seo/views/admin/default/_module_seo', ['model' => $model]),
                'options' => ['class' => 'flex-sm-fill text-center nav-item'],
            ];


            echo \panix\engine\bootstrap\Tabs::widget([
                //'encodeLabels'=>true,
                'options' => [
                    'class' => 'nav-pills flex-column flex-sm-row nav-tabs-static'
                ],
                'items' => $tabs,
            ]);
            ?>

        </div>
        <div class="card-footer text-center">
            <?= $model->submitButton(); ?>
        </div>
    </div>
<?php ActiveForm::end(); ?>