<?php

/**
 * Bug https://github.com/yiisoft/yii2-jui/issues/62
 *
 * if $.fn.button.noConflict is not a function
 *
 * Solution is add .noConflict in \vendor\yiisoft\yii2-jui\src\Dialog.php at line 45
 *
 * change to ($.fn.button && $.fn.button.noConflict !== undefined)
 */

use panix\mod\shop\models\Category;
use yii\web\View;

echo \panix\ext\jstree\JsTree::widget([
    'id' => 'CategoryTree',
    'name' => 'jstree',
    'allOpen'=>true,
    'data' => Category::find()->dataTree(1),
    'core' => [
        'force_text' => true,
        'animation' => 0,
        'strings' => [
            'Loading ...' => Yii::t('app', 'LOADING')
        ],
        "themes" => [
            "stripes" => true,
            'responsive' => true,
            "variant" => "large"
        ],
        'check_callback' => true
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
    foreach (Yii::$app->request->post('categories') as $id) {

       $this->registerJs("$('#jsTree_CategoryTree').checkNode({$id});", View::POS_END, 'check-'.$id);
    }
} elseif ($model->isNewRecord && empty($_POST['categories']) && isset($presetCategories)) {
    foreach ($presetCategories as $id) {
        if ($model->type && $id === $model->type->main_category)
            continue;
        $this->registerJs("$('#jsTree_CategoryTree').checkNode({$id});", View::POS_END, 'check-'.$id);
    }
} else {
    // Check tree nodes
    echo count($model->categories);
    foreach ($model->categories as $c) {
        if ($c->id === $model->main_category_id)
            continue;
        $this->registerJs("$('#jsTree_CategoryTree').checkNode({$c->id});", View::POS_END, 'check-'.$c->id);
    }
}
