<?php
use panix\engine\Html;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\Currency;

/**
 * @var $form \panix\engine\bootstrap\ActiveForm
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
        'template' => '{label}
<div class="input-group col-sm-8 col-lg-10">{input}


  <span class="input-group-text">{label_unit}</span>
  {unit}
  
<span class="input-group-text">{label_currency}</span>
{currency}{hint}{error}
<div class="input-group-append">
    <a id="add-price" class="text-success btn btn-outline-success" href="#"><i class="icon-add"></i></a>
  </div>
  

</div>',
    ])->textInput([

        'maxlength' => 10
    ]);
}
?>


<div class="form-group row field_price">


    <div class="col" id="extra-prices">
        <?php foreach ($model->prices as $price) { ?>

            <div id="price-row-<?= $price->id ?>">
                <hr/>
                <div class="row">
                    <?php echo Html::label('Цена', 'productprices-' . $price->id . '-value', array('class' => 'col-sm-3 col-md-3 col-lg-2 col-form-label', 'required' => true)); ?>
                    <div class="col-sm-9 col-md-6 col-lg-5 col-xl-3">
                        <div class="input-group mb-2">
                            <?php echo Html::textInput('ProductPrices[' . $price->id . '][value]', $price->value, array('class' => 'float-left form-control')); ?>
                            <div class="input-group-append">
                                    <span class="col-form-label ml-3">
                                        <span class="currency-name">грн.</span> за
                                        <span class="unit-name">шт.</span>
                                        <a href="#" data-price-id="<?= $price->id ?>"
                                           class="remove-price btn btn-sm btn-danger">
                                            <i class="icon-delete"></i>
                                        </a>
                                    </span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <?php echo Html::label($model::t('FROM'), 'productprices-' . $price->id . '-from', array('class' => 'col-sm-3 col-md-3 col-lg-2 col-form-label', 'required' => true)); ?>
                    <div class="col-sm-9 col-md-6 col-lg-5 col-xl-3">
                        <div class="input-group mb-3 mb-sm-0">
                            <?php echo Html::textInput('ProductPrices[' . $price->id . '][from]', $price->from, array('class' => 'float-left form-control')); ?>
                            <div class="input-group-append">
                                <span class="col-form-label ml-3 unit-name">шт.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

</div>