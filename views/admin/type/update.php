<?php

use yii\helpers\Html;

?>
<?php
echo Html::beginForm('', 'post', [
    'id' => 'ProductTypeForm',
]);
?>
    <div class="card">
        <div class="card-header">
            <h5><?= Html::encode($this->context->pageName) ?></h5>
        </div>
        <div class="card-body">
            <?php

            echo Html::hiddenInput('main_category', $model->main_category, ['id' => 'main_category']);
            echo \panix\engine\bootstrap\Tabs::widget([
                'items' => [
                    [
                        'label' => $model::t('TAB_OPTIONS'),
                        'content' => $this->render('tabs/_options', ['attributes' => $attributes, 'model' => $model]),
                        'headerOptions' => [],
                        'active' => true,
                        'options' => ['id' => 'options'],
                    ],
                    [
                        'label' => $model::t('TAB_PRODUCT_SEO'),
                        'content' => $this->render('tabs/_product_seo', ['model' => $model]),
                        'options' => ['id' => 'product_seo'],
                    ],
                    [
                        'label' => $model::t('TAB_CATEGORIES'),
                        'content' => $this->render('tabs/_tree', ['model' => $model]),
                        'options' => ['id' => 'tree'],
                    ],
                ],
            ]);
            ?>
        </div>
        <div class="card-footer text-center">
            <?= $model->submitButton(); ?>
        </div>
    </div>
<?= Html::endForm(); ?>