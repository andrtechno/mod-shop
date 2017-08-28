<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;
use yii\bootstrap\Dropdown;
?>


<?php
$formatter = Yii::$app->formatter;

echo $formatter->asCurrency($model->price, 'UAH', [
    NumberFormatter::MIN_FRACTION_DIGITS => 0,
    NumberFormatter::MAX_FRACTION_DIGITS => 0,
        ], [NumberFormatter::NEGATIVE_PREFIX => 1]);

/* ().
  $formatter->decimalSeparator = '';
  $formatter->thousandSeparator = '';

  echo $formatter->asPercent(100, 0,[
  NumberFormatter::MIN_FRACTION_DIGITS => 0,
  NumberFormatter::MAX_FRACTION_DIGITS => 1
  ]);
 */
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::encode($this->context->pageName) ?></h3>
    </div>
    <div class="panel-body">


        <?php
        $form = ActiveForm::begin([
                    'options' => ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data']
        ]);
        ?>
        <?php
        echo yii\bootstrap\Tabs::widget([
            'items' => [
                [
                    'label' => $model::t('TAB_MAIN'),
                    'content' => $this->render('tabs/_main', ['form' => $form, 'model' => $model]),
                    'active' => true,
                    'options' => ['id' => 'main'],
                ],
                [
                    'label' => 'Изображение',
                    'content' => $this->render('tabs/_images', ['form' => $form, 'model' => $model]),
                    'headerOptions' => [],
                    'options' => ['id' => 'images'],
                ],
                [
                    'label' => 'Связи товаров',
                    'content' => $this->render('tabs/_related', ['exclude' => $model->id, 'form' => $form, 'model' => $model]),
                    'headerOptions' => [],
                    'options' => ['id' => 'related'],
                ],
                [
                    'label' => 'Example',
                    'url' => 'http://www.corner-cms.com',
                ],
                [
                    'label' => 'Dropdown',
                    'items' => [
                        [
                            'label' => 'DropdownA',
                            'content' => 'DropdownA, Anim pariatur cliche...',
                        ],
                        [
                            'label' => 'DropdownB',
                            'content' => 'DropdownB, Anim pariatur cliche...',
                        ],
                    ],
                ],
            ],
        ]);
        ?>









        <div class="form-group text-center">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'CREATE') : Yii::t('app', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>




        <?php
        foreach ($model->getEavAttributes()->all() as $attr) {
            //print_r($attr);
            echo $attr->name;
            echo $form->field($model, $attr->name, ['class' => '\mirocow\eav\widgets\ActiveField'])->eavInput();
        }
        ?>

        <?php ActiveForm::end(); ?>



    </div>
</div>


<?=
\mirocow\eav\admin\widgets\Fields::widget([
    'model' => $model,
    'categoryId' => $model->id,
    'entityName' => 'product',
    'entityModel' => 'app\system\modules\shop\models\ShopProduct',
])?>