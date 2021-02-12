<?php

use panix\mod\cart\models\OrderStatus;
use panix\mod\shop\models\TypeAttribute;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Html;


$class = '';//($model->status_id || $model->delivery_city) ? 'show' : '';


?>
<div class="card-body">


    <div class="collapse <?= $class; ?>" id="collapse-grid-filter">
        <div class="p-3">
            <?php

            /*$form = ActiveForm::begin([
                'id' => 'form-grid-filter',
                'action' => ['index'],
                'method' => 'GET',
                'options' => [
                    'data' => ['pjax' => true]
                ],

            ]);*/
            ?>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <?php
                        echo Html::activeLabel($model, 'type_id');
                        echo Html::activeDropDownList($model, 'type_id', ArrayHelper::map(\panix\mod\shop\models\ProductType::find()
                            ->addOrderBy(['name' => SORT_ASC])
                            ->all(), 'id', 'name'), [
                            'class' => 'form-control',
                            'prompt' => html_entity_decode('&mdash;'),
                            'id' => Html::getInputId($model, 'type_id')
                        ])
                        ?>

                    </div>

                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <?php
                        echo Html::activeLabel($model, 'currency_id');
                        echo Html::activeDropDownList($model, 'currency_id', ArrayHelper::map(\panix\mod\shop\models\Currency::find()
                            // ->addOrderBy(['name' => SORT_ASC])
                            ->where(['switch' => 1])
                            ->all(), 'id', function ($data) {

                            return $data->iso . ' - ' . $data->rate;
                        }), [
                            'class' => 'form-control',
                            'prompt' => html_entity_decode('&mdash;'),
                            'id' => Html::getInputId($model, 'currency_id')
                        ])
                        ?>
                    </div>
                    <div class="form-group">
                        <?php
                        echo Html::activeLabel($model, 'availability');
                        echo Html::activeDropDownList($model, 'availability', \panix\mod\shop\models\Product::getAvailabilityItems(), [
                            'class' => 'form-control',
                            'prompt' => html_entity_decode('&mdash;'),
                            'id' => Html::getInputId($model, 'availability')
                        ])
                        ?>
                    </div>
                </div>
                <div class="col-sm-4">

                    <div class="form-check">
                        <?= Html::activeCheckbox($model, 'switch', ['class' => 'form-check-input']); ?>
                    </div>
                    <div class="form-check">
                        <?= Html::activeCheckbox($model, 'use_configurations', ['class' => 'form-check-input']); ?>
                    </div>

                </div>
            </div>
            <div id="filter-grid-attributes" class="row"></div>
        </div>
        <div class="card-footer">
            <?= Html::submitButton('Применить фильтр', ['class' => 'btn btn-sm btn-primary']) ?>
        </div>
        <?php
        // ActiveForm::end();
        ?>
    </div>
</div>