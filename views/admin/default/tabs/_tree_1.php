<div class="form-group">
    <div class="col-xs-4"><label class="control-label" for="search-coupon-category"><?php echo Yii::t('app', 'Поиск:') ?></label></div>
    <div class="col-xs-8"> <input class="form-control" id="search-coupon-category" type="text" onkeyup='$("#ShopCategoryTree").jstree("search", $(this).val());' />
    </div>
</div>

<?php
// Register scripts
//Yii::app()->clientScript->registerScriptFile(
//        $this->module->assetsUrl . '/admin/products.js', CClientScript::POS_END
//);

// Create jstree
$this->widget('ext.jstree.JsTree', array(
    'id' => 'ShopCategoryTree',
    'data' => ShopCategoryNode::fromArray(ShopCategory::model()->published()->findAllByPk(1)),
    'options' => array(
        'core' => array(
            'strings' => array('Loading ...' => 'Please wait ...'),
            'check_callback' => true,
            "themes" => array("stripes" => true, 'responsive' => true),
        ),
        'plugins' => array('search', 'checkbox'),
        'checkbox' => array(
            'three_state' => false,
            "keep_selected_style" => false,
            'tie_selection' => false,
        ),
    ),
));

// Get categories preset
if ($model->type) {
    $presetCategories = unserialize($model->type->categories_preset);
    if (!is_array($presetCategories))
        $presetCategories = array();
}

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

        Yii::app()->getClientScript()->registerScript("checkNode{$id}", "
			$('#ShopCategoryTree').checkNode({$id});
		");
    }
} else {
    // Check tree nodes
    foreach ($model->categories as $c) {
        if ($c->id === $model->main_category_id)
            continue;
        echo $c->id;
        Yii::app()->getClientScript()->registerScript("checkNode{$c->id}", "
			$('#ShopCategoryTree').checkNode({$c->id});
		");
    }
}


