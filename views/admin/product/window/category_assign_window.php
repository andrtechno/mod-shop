
<div style="margin: 15px;">
    <div id="alert-s"></div>
<?php

use panix\mod\shop\models\Category;

echo \panix\ext\jstree\JsTree::widget([
    'id' => 'CategoryAssignTreeDialog',
    'name' => 'jstree',
    'allOpen'=>true,
    'data' => Category::find()->dataFancytree(2),
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
]);?>
</div>