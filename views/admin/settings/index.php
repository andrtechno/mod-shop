<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;
?>
<?php
$form = ActiveForm::begin();
?>
<div class="card">
    <div class="card-header">
        <h5><?= $this->context->pageName ?></h5>
    </div>
    <div class="card-body">
        <?php
        echo yii\bootstrap4\Tabs::widget([
            'items' => [
                [
                    'label' => 'Общие',
                    'content' => $this->render('_global', ['form' => $form, 'model' => $model]),
                    'active' => true,
                ],
               /* [
                    'label' => 'SEO',
                    'items' => [
                        [
                            'label' => 'Категорий',
                            'contentOptions' => ['id' => 'seo_categories'],
                            'content' => $this->render('_seo_categories', ['form' => $form, 'model' => $model]),
                        ],
                    ],
                ],*/
            ],
        ]);
        ?>
    </div>
    <div class="card-footer text-center">
        <?= $model->submitButton(); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>