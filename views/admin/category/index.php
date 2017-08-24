<?php

use yii\helpers\Html;
use panix\mod\shop\models\ShopCategory;
use panix\mod\shop\models\ShopCategoryNode;

$user = Yii::$app->getModule("shop")->model("ShopProduct");
$manufacturer = Yii::$app->getModule("shop")->model("ShopManufacturer");

/*
  $countries = ShopCategory::findOne(1);
  $parent = $countries->children()->all();

  foreach(ShopCategoryNode::fromArray(ShopCategory::findOne(1)->children()->all(),['switch' => true]) as $r){
  print_r($r) ;
  echo '<br><br>';
  foreach($r['children'] as $d){
  print_r($r) ;
  echo '<br><br>';
  }
  }
 */
//print_r(ShopCategory::findOne(1)->children()->asArray()->all());
//print_r(ShopCategoryNode::fromArray(ShopCategory::findOne(1)->children()->all(),['switch' => true]));

echo \panix\ext\jstree\JsTree::widget([
    'id' => 'ShopCategoryTree',
    // 'attribute' => 'attribute_name',
    // 'model' => $model,
    'name' => 'jstree',
    'data' => ShopCategoryNode::fromArray(ShopCategory::findOne(1)->children()->all(), ['switch' => true]),
    'core' => [

        /* 'data'=>[
          'Simple root node',
          [
          'id' => 'node_2',
          'text' => 'Root node with options',
          'state' => [ 'opened' => true, 'selected' => true ],
          'children' => [ [ 'text' => 'Child 1' ], 'Child 2']
          ],
          'Simple root node',
          ] */
        'force_text' => true,
        'animation' => 0,
        'strings' => array('Loading ...' => 'Please wait ...'),
        'check_callback' => true,
        "themes" => array("stripes" => true, 'responsive' => true),
        "check_callback" => 'js:function (operation, node, parent, position, more) {
                    console.log(operation);
                    if(operation === "copy_node" || operation === "move_node") {

                    } else if (operation === "delete_node"){
                    
                    } else if (operation === "rename_node") {

                    }
                      return true; // allow everything else
                    }
    
    '],
    'plugins' => ['dnd', 'contextmenu', 'search', 'wholerow', 'state'],
    'contextmenu' => [
        'items' => 'js:function($node) {
                var tree = $("#jsTree_ShopCategoryTree").jstree(true);
                return {
                    "Add": {
                        "icon":"icon-add",
                        "label": "' . Yii::t('app', 'CREATE', 0) . '",
                        "title":"' . Yii::t('app', 'Скрыть показать') . '",
                        "action": function (obj) {
                            $node = tree.get_node($node);
                            window.location = "/admin/shop/category/create/parent_id/"+$node.id.replace("node_", "");
                        }
                    }, 
                };
            }'
    ]
]);

//$this->title = Yii::t('user/default', 'Users');
//$this->params['breadcrumbs'][] = $this->title;
?>
