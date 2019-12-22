<?php
use panix\ext\multipleinput\MultipleInput;
use panix\ext\multipleinput\TabularInput;

$test = 'mi_' . \panix\engine\CMS::gen(10);

echo MultipleInput::widget([
    'id' => $test,
    'value' => '',
    'name' => 'options[' . rand(9999, 999999) . '][data]',
    'min' => 1,
    'allowEmptyList' => false,

    'enableGuessTitle' => true,
    'sortable' => true,
    'addButtonPosition' => MultipleInput::POS_ROW, // show add button in the header
    'columns' => [
        [
            'name' => 'color',
            'type' => \panix\ext\colorpicker\ColorPicker::class,
            'enableError' => false,
            'nameSuffix' => $test,
            //'options' => [
            // 'id' => $test,
            //'mode' => 'flat',
            // ],
            // 'title' => $model::t('COLOR'),
            //'headerOptions' => [
            //'style' => 'width: 250px;',
            //],
        ],

    ]
]);