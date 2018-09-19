<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;
?>
<?php
$form = ActiveForm::begin();
?>
<div class="card bg-light">
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
                    'options' => ['id' => 'global'],
                ],
                [
                    'label' => 'SEO',
                    'items' => [
                        [
                            'label' => 'Товаров',
                            'contentOptions' => ['id' => 'seo_products'],
                            'content' => $this->render('_seo_products', ['form' => $form, 'model' => $model]),
                        ],
                        [
                            'label' => 'Категорий',
                            'contentOptions' => ['id' => 'seo_categories'],
                            'content' => $this->render('_seo_categories', ['form' => $form, 'model' => $model]),
                        ],
                    ],
                ],
            ],
        ]);
        ?>
    </div>
    <div class="card-footer text-center">
        <?= Html::submitButton(Yii::t('app', 'SAVE'), ['class' => 'btn btn-success']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>