<?php

use yii\helpers\Html;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\CategoryNode;

\panix\mod\shop\assets\admin\CategoryAsset::register($this);
?>

<div class="card bg-light">
    <div class="card-header">
        <h5><?= Html::encode($this->context->pageName) ?></h5>
    </div>
    <div class="card-body">
        <?php




        echo \panix\ext\jstree\JsTree::widget([
            'id' => 'CategoryTree',
            'name' => 'jstree',
            'allOpen'=>true,
            'data' => CategoryNode::fromArray(Category::findOne(1)->children()->all(), ['switch' => true]),
            'core' => [
                'force_text' => true,
                'animation' => 0,
                'strings' => [
                    'Loading ...' => Yii::t('app', 'LOADING')
                ],
                "themes" => ["stripes" => true, 'responsive' => true, "variant" => "large"],
                'check_callback' => true
            ],
            'plugins' => ['dnd', 'contextmenu', 'search', 'wholerow', 'state'],
            'contextmenu' => [
                'items' => new yii\web\JsExpression('function($node) {
                var tree = $("#jsTree_CategoryTree").jstree(true);
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
                            window.location = "/admin/shop/category/index?parent_id="+$node.id.replace("node_", "");
                        }
                    }, 
                    "Edit": {
                        "icon":"icon-edit",
                        "label": "' . Yii::t('app', 'UPDATE') . '",
                        "action": function (obj) {
                            $node = tree.get_node($node);
                           window.location = "/admin/shop/category/index?id="+$node.id.replace("node_", "");
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
                            if (confirm("' . Yii::t('app', 'DELETE_CONFIRM') . '\nТак же будут удалены все товары.")) {
                                tree.delete_node($node);
                            }
                        }
                    }
                };
      }')
            ]
        ]);
        ?>
    </div>
</div>
