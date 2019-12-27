<?php
use panix\mod\shop\models\Category;

?>
<div class="p-3">
    <div id="alert-s"></div>
    <?php


    echo \panix\ext\jstree\JsTree::widget([
        'id' => 'CategoryAssignTreeDialog',
        'allOpen' => true,
        'data' => Category::find()->dataTree(1, null, ['switch' => 1]),
        'core' => [
            'strings' => [
                'Loading ...' => Yii::t('app', 'LOADING')
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
        'plugins' => ['search', 'checkbox'],
        'checkbox' => [
            'three_state' => false,
            'tie_selection' => false,
            'whole_node' => false,
            "keep_selected_style" => true
        ],
    ]);
    ?>
</div>