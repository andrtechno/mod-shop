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

if ($model->use_configurations) {
    echo $form->field($model, 'price')->hiddenInput()->label(false);
} else {
    echo $form->field($model, 'price', [
        'parts' => [
            '{label_unit}' => Html::activeLabel($model, 'unit'),
            '{unit}' => Html::activeDropDownList($model, 'unit', $model->getUnits(), ['class' => 'custom-select']),
            '{label_currency}' => Html::activeLabel($model, 'currency_id'),
            '{currency}' => Html::activeDropDownList($model, 'currency_id', ArrayHelper::map(Currency::find()->andWhere(['!=', 'id', Yii::$app->currency->main['id']])->all(), 'id', 'name'), [
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
}




$commonAttributeOptions = [
    'enableAjaxValidation'   => true,
    'enableClientValidation' => false,
    'validateOnChange'       => false,
    'validateOnSubmit'       => true,
    'validateOnBlur'         => false,
];
$enableActiveForm = true;
?>

<?php /*echo \panix\ext\multipleinput\MultipleInput::widget([
    'model' => $model,
    'attribute' => 'questions',
    'attributeOptions' => $commonAttributeOptions,
    'columns' => [
        [
            'name' => 'question',
            'title' => 'question',
            'type' => 'textarea',
        ],
        [
            'name' => 'answers',
            'title' => 'answers',
            'type'  => \panix\ext\multipleinput\MultipleInput::class,
            'options' => [
                'attributeOptions' => $commonAttributeOptions,
                'columns' => [
                    [
                        'name' => 'right',
                        'title' => 'asddasdsa',
                        'type' => \panix\ext\multipleinput\MultipleInputColumn::TYPE_CHECKBOX
                    ],
                    [
                        'name' => 'answer'
                    ]
                ]
            ]
        ]
    ],
]);*/ ?>
<?php echo $form->field($model, 'prices1')->widget(\panix\ext\multipleinput\MultipleInput::class, [
    //'model' => $model,
    //'attribute' => 'phone',
    //'max' => 5,
    'min' => 0, // should be at least 2 rows
    'allowEmptyList' => false,
    'enableGuessTitle' => true,
    'sortable' => true,
    'addButtonPosition' => \panix\ext\multipleinput\MultipleInput::POS_HEADER, // show add button in the header
    'columns' => [
        [
            'name' => 'number',
            'title' => 'Цена',
            'type' => \panix\ext\multipleinput\MultipleInputColumn::TYPE_TEXT_INPUT,
            'enableError' => true,
            // 'title' => 'phone',
            'headerOptions' => [
                'style' => 'width: 250px;',
            ],
        ],
        [
            'name' => 'count',
            'enableError' => false,
            'title' => 'Количество',
            'headerOptions' => [
                'style' => 'width: 250px;',
            ],
        ],
    ]
]);