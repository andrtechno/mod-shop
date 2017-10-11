<div id="alert-s"></div>
<?php
$this->widget('ext.jstree.JsTree', array(
    'id' => 'CategoryAssignTreeDialog',
    'data' => ShopCategoryNode::fromArray(ShopCategory::model()->published()->findAllByPk(1)),
    'options' => array(
        'core' => array(
            //  'force_text' => true,
            'animation' => 0,
            'strings' => array('Loading ...' => 'Please wait ...'),
            // 'check_callback' => true,
            "themes" => array("stripes" => true, 'responsive' => true),
        ),
        'plugins' => array('search', 'checkbox'),
        'checkbox' => array(
            'three_state' => false,
            'tie_selection' => false,
            'whole_node' => false,
            "keep_selected_style" => true
        ),
    ),
));

?>

