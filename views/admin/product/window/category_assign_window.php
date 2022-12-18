<?php

use panix\mod\shop\models\Category;

/**
 * @var \yii\web\View $this
 */

//\yii\widgets\PjaxAsset::register($this);
\panix\engine\assets\BootstrapNotifyAsset::register($this);

//for bootstrap modal window
//\yii\bootstrap4\BootstrapPluginAsset::register($this);
?>
<div class="p-3">
    <div class="alert alert-info">Для выбора основной категории необходимо выделить ее. Кликнув на категорию она должна подсветится синим
        цветом.</div>
    <div id="alert-s"></div>
    <div class="form-group mt-3">
        <input class="form-control" placeholder="Поиск..." type="text"
               onkeyup='$("#CategoryAssignTreeDialog").jstree(true).search($(this).val())'/>
    </div>
    <?php


    echo \panix\ext\jstree\JsTree::widget([
        'id' => 'CategoryAssignTreeDialog',
        'allOpen' => false,
        'data' => Category::find()->dataTree(1, null, ['switch' => 1]),
        'core' => [
            'strings' => [
                'Loading ...' => Yii::t('app/default', 'LOADING')
            ],
            'check_callback' => true,
            "themes" => [
                "stripes" => true,
                'responsive' => true,
                "variant" => "large",
                // 'name' => 'default-dark',
                // "dots" => true,
                // "icons" => true
            ],
        ],
        'plugins' => ['checkbox', 'search'],//"wholerow",
        'checkbox' => [
            'three_state' => false, // need set true
            'tie_selection' => false,
            'whole_node' => false,
            "keep_selected_style" => true,
        ],
    ]);
    ?>
</div>