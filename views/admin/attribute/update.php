<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;

echo \panix\engine\bootstrap\Alert::widget([
    'options' => [
        'class' => 'alert-info',
    ],
    'closeButton' => false,
    'body' => Yii::t('shop/Attribute', 'INFO', ['productType' => Html::a('типу товара', '/admin/shop/productType')]),
]);

?>

<?php
$form = ActiveForm::begin([
    'id' => basename(get_class($model)),
    'options' => [
        'class' => 'form-horizontal',
    ]
]);
?>
    <div class="card">
        <div class="card-header">
            <h5><?= Html::encode($this->context->pageName) ?></h5>
        </div>
        <div class="card-body">

            <?php
            echo panix\engine\bootstrap\Tabs::widget([
                'options' => ['id' => 'attributes-tabs'],
                'items' => [
                    [
                        'label' => 'Основные',
                        'encode' => false,
                        'content' => $this->render('tabs/_main', ['form' => $form, 'model' => $model]),
                        'active' => true,

                    ],
                    [
                        'label' => (isset($model->tab_errors['options'])) ? Html::icon('warning', ['class' => 'text-danger','title'=>$model->tab_errors['options']]) . ' Опции' : 'Опции',
                        'encode' => false,
                        'content' => $this->render('tabs/_options', ['form' => $form, 'model' => $model]),
                        'headerOptions' => [],

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
