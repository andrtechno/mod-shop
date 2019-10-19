<?php

use panix\mod\shop\models\Category;

?>
<div class="form-group">
    <div class="alert alert-info">
        <?= $model::t('ALERT_INFO'); ?>
    </div>
</div>
<div class="form-group row">
    <div class="col-sm-4">
        <label class="col-form-label" for="search-type-category"><?php echo Yii::t('app', 'Поиск:') ?></label></div>
    <div class="col-sm-8">
        <input class="form-control" id="search-type-category" type="text"
               onkeyup='$("#jsTree_TypeCategoryTree").jstree("search", $(this).val());'/>
    </div>
</div>


<?php
// Create jstree
echo \panix\ext\jstree\JsTree::widget([
    'id' => 'TypeCategoryTree',
    'name' => 'jstree',
    'allOpen' => true,
    'data' => Category::find()->dataTree(1),
    'core' => [
        'animation' => 0,
        'strings' => ['Loading ...' => Yii::t('app', 'LOADING')],
        'check_callback' => true,
        "themes" => ["variant" => "large", "stripes" => true, 'responsive' => true],
    ],
    'plugins' => [
        'search',
        'checkbox',
      //  'state',
      // 'wholerow',
       // 'types'
    ],
    'checkbox' => [
        'three_state' => false,
        'tie_selection' => false,
        'whole_node' => false,
        "keep_selected_style" => true
    ],
]);

// Check tree nodes
$categories = unserialize($model->categories_preset);
if (!is_array($categories))
    $categories = [];

foreach ($categories as $id) {
    $this->registerJs("$('#jsTree_TypeCategoryTree').checkNode({$id});");
    //$this->registerJs("$('#jsTree_TypeCategoryTree').jstree('check_node','node_{$id}');");
}



?>
