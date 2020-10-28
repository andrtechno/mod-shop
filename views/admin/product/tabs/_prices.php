<?php
use panix\engine\Html;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\Currency;

/**
 * @var \panix\engine\bootstrap\ActiveForm $form
 * @var \panix\mod\shop\models\Product $model
 */
?>

<?php

echo $form->field($model, 'price_purchase')->hint('Данная цена не где не отображается, она нужна только для статистики');
//if ($model->use_configurations) {
    //echo $form->field($model, 'price')->hiddenInput()->label(false);
//} else {
    echo $form->field($model, 'price', [
        'parts' => [
            '{label_unit}' => Html::activeLabel($model, 'unit'),
            '{unit}' => Html::activeDropDownList($model, 'unit', $model->getUnits(), ['class' => 'custom-select']),
            '{label_currency}' => Html::activeLabel($model, 'currency_id'),
            '{currency}' => Html::activeDropDownList($model, 'currency_id', ArrayHelper::map(Currency::find()->published()->andWhere(['!=', 'id', Yii::$app->currency->main['id']])->all(), 'id', 'iso'), [
                'class' => 'custom-select',
                'prompt' => $model::t('SELECT_CURRENCY', [
                    'currency' => Yii::$app->currency->main['iso']
                ])
            ])
        ],
        'template' => '<div class="col-sm-4 col-lg-2">{label}</div>
<div class="input-group col-sm-8 col-lg-10">{input}
<span class="input-group-text">{label_unit}</span>
{unit}<span class="input-group-text">{label_currency}</span>{currency}{hint}{error}</div>',
    ])->textInput(['maxlength' => 10]);



echo $form->field($model, 'prices')->label(false)->widget(\panix\ext\multipleinput\MultipleInput::class, [
    //'model' => $model,
    //'attribute' => 'phone',
    //'max' => 5,
    'min' => 0, // should be at least 2 rows
    'allowEmptyList' => false,
    'enableGuessTitle' => true,
    'sortable' => true,
    'addButtonOptions'=>['class'=>'text-right btn btn-sm btn-success'],
    'addButtonPosition' => \panix\ext\multipleinput\MultipleInput::POS_HEADER, // show add button in the header
    'columns' => [
        [
            'name' => 'value',
            'title' => $model::t('PRICE'),
            'type' => \panix\ext\multipleinput\MultipleInputColumn::TYPE_TEXT_INPUT,
            'enableError' => true,
            // 'title' => 'phone',
            'headerOptions' => [
                'style' => 'width: 250px;',
            ],
        ],
        [
            'name' => 'from',
            'enableError' => false,
            'title' => 'Количество',
            'headerOptions' => [
                'style' => 'width: 250px;',
            ],
        ],
    ]
]);
//}