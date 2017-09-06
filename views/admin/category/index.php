<?php

use yii\helpers\Html;
use panix\mod\shop\models\ShopCategory;
use panix\mod\shop\models\ShopCategoryNode;

\panix\mod\shop\assets\admin\CategoryAsset::register($this);



echo \panix\ext\jstree\JsTree::widget([
    'id' => 'ShopCategoryTree',
    'name' => 'jstree',
    'data' => ShopCategoryNode::fromArray(ShopCategory::findOne(1)->children()->all(), ['switch' => true]),
    'core' => [
        'force_text' => true,
        'animation' => 0,
        'strings' => [
            'Loading ...' => Yii::t('app', 'LOADING')
        ],
        "themes" => ["stripes" => true, 'responsive' => true,"variant" => "large"],
        'check_callback' => true
    ],
    'plugins' => ['dnd', 'contextmenu', 'search', 'wholerow', 'state'],
    'contextmenu' => [
        'items' => new yii\web\JsExpression('function($node) {
                var tree = $("#jsTree_ShopCategoryTree").jstree(true);
                return {
                    "Switch": {
                        "icon":"icon-eye",
                        "label": "' . Yii::t('app', 'Скрыть показать') . '",
                        "action": function (obj) {
                            $node = tree.get_node($node);
                            categorySwitch($node);
                        }
                    }, 
                    "Add": {
                        "icon":"icon-add",
                        "label": "' . Yii::t('app', 'CREATE') . '",
                        "action": function (obj) {
                            $node = tree.get_node($node);
                            console.log($node);
                            window.location = "/admin/shop/category/create/parent_id/"+$node.id.replace("node_", "");
                        }
                    }, 
                    "Edit": {
                        "icon":"icon-edit",
                        "label": "' . Yii::t('app', 'UPDATE') . '",
                        "action": function (obj) {
                            $node = tree.get_node($node);
                           window.location = "/admin/shop/category/update/id/"+$node.id.replace("node_", "");
                        }
                    },  
                    "Rename": {
                        "icon":"icon-rename",
                        "label": "' . Yii::t('app', 'RENAME') . '",
                        "action": function (obj) {
                            console.log($node);
                            tree.edit($node);
                        }
                    },                         
                    "Remove": {
                        "icon":"icon-trashcan",
                        "label": "' . Yii::t('app', 'DELETE') . '",
                        "action": function (obj) { 
                            tree.delete_node($node);
                        }
                    }
                };
      }')
    ]
]);
?>
