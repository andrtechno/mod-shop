

<?php

use panix\mod\shop\models\ShopCategoryNode;
use panix\mod\shop\models\ShopCategory;

echo \panix\ext\jstree\JsTree::widget([
    'id' => 'ShopCategoryTree',
    'name' => 'jstree',
    'allOpen'=>true,
    'data' => ShopCategoryNode::fromArray(ShopCategory::findOne(1)->children()->all()),
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
        "keep_selected_style" => false,
        'tie_selection' => false,
    ],
]);

// Get categories preset
/* if ($model->type) {
  $presetCategories = unserialize($model->type->categories_preset);
  if (!is_array($presetCategories))
  $presetCategories = array();
  } */

if (isset($_POST['categories']) && !empty($_POST['categories'])) {
    foreach ($_POST['categories'] as $id) {
        Yii::app()->getClientScript()->registerScript("checkNode{$id}", "
			$('#ShopCategoryTree').checkNode({$id});
		");
    }
} elseif ($model->isNewRecord && empty($_POST['categories']) && isset($presetCategories)) {
    foreach ($presetCategories as $id) {
        if ($model->type && $id === $model->type->main_category)
            continue;
        $this->registerJs("$('#jsTree_ShopCategoryTree').checkNode({$id});");
    }
} else {
    // Check tree nodes
    foreach ($model->categories as $c) {
        if ($c->id === $model->main_category_id)
            continue;

        $this->registerJs("$('#jsTree_ShopCategoryTree').checkNode({$c->id});");
    }
}

