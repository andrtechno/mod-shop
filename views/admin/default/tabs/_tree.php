

<?php

use panix\mod\shop\models\ShopCategoryNode;
use panix\mod\shop\models\ShopCategory;





echo \panix\ext\jstree\JsTree::widget([
    'id' => 'ShopCategoryTree',
    'name' => 'jstree',
     'data' => ShopCategoryNode::fromArray(ShopCategory::findOne(1)->children()->all()),

    'core' => [
        'strings' => array('Loading ...' => 'Please wait ...'),
        'check_callback' => true,
        "themes" => array("stripes" => true, 'responsive' => true),
    ],
    'plugins' => ['search', 'checkbox'],
    'checkbox' => array(
        'three_state' => false,
        "keep_selected_style" => false,
        'tie_selection' => false,
    ),
]);

