<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\Category;
use yii\filters\VerbFilter;

/**
 * AdminController implements the CRUD actions for User model.
 */
class CategoryController extends AdminController {
    /*
      public function actions() {
      return [
      'moveNode' => [
      'class' => 'panix\engine\behaviors\nestedsets\actions\MoveNodeAction',
      'modelClass' => 'panix\mod\shop\models\Category',
      ],
      'deleteNode' => [
      'class' => 'panix\engine\behaviors\nestedsets\actions\DeleteNodeAction',
      'modelClass' => 'panix\mod\shop\models\Category',
      ],
      'updateNode' => [
      'class' => 'panix\engine\behaviors\nestedsets\actions\UpdateNodeAction',
      'modelClass' => 'panix\mod\shop\models\Category',
      ],
      'createNode' => [
      'class' => 'panix\engine\behaviors\nestedsets\actions\CreateNodeAction',
      'modelClass' => Category::className(),
      ],
      ];
      }
     */

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete2' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex() {
        $this->pageName = Yii::t('shop/admin', 'CATEGORIES');
        $this->buttons = [
            [
                'label' => Yii::t('shop/admin', 'CREATE_CATEGORY'),
                'url' => ['/admin/shop/category/create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->breadcrumbs[] = $this->pageName;
        return $this->render('index', [
        ]);
    }

    public function actionUpdate($id) {
        if ($id === true) {
            $model = new Category;
        } else {
            $model = Category::findOne($id);
        }
        if (!$model) {
            $this->error404();
        }
        $this->pageName = Yii::t('shop/admin', 'CATEGORIES');

        $this->breadcrumbs = [
            [
                'label' => Yii::t('shop/default', 'MODULE_NAME'),
                'url' => ['/admin/shop']
            ],
            $this->pageName
        ];
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {

            if ($model->getIsNewRecord()) {
                if (Yii::$app->request->get('parent_id')) {
                    $parent_id = Category::findOne(Yii::$app->request->get('parent_id'));
                } else {
                    $parent_id = Category::findOne(1);
                }

                $model->appendTo($parent_id);
                return $this->redirect(['index']);
            } else {
                $model->saveNode();
                return $this->redirect(['update', 'id' => $model->id]);
            }

            Yii::$app->session->addFlash('success', \Yii::t('app', 'SUCCESS_UPDATE'));
            //return Yii::$app->getResponse()->redirect(['/admin/shop/category']);
        }
        echo $this->render('update', [
            'model' => $model,
        ]);
    }

    protected function findModel($id) {
        $model = new Category;
        if (($model = $model::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionRenameNode() {


        if (strpos($_GET['id'], 'j1_') === false) {
            $id = $_GET['id'];
        } else {
            $id = str_replace('j1_', '', $_GET['id']);
        }

        $model = Category::findOne((int) $id);
        if ($model) {
            $model->name = $_GET['text'];
            $model->seo_alias = \panix\engine\CMS::translit($model->name);
            if ($model->validate()) {
                $model->saveNode(false, false);
                $message = Yii::t('shop/admin', 'CATEGORY_TREE_RENAME');
            } else {
                $message = $model->getError('seo_alias');
            }
            echo CJSON::encode(array(
                'message' => $message
            ));
            Yii::app()->end();
        }
    }

    public function actionCreateNode() {
        $model = new Category;
        $parent = Category::findOne($_GET['parent_id']);

        $model->name = $_GET['text'];
        $model->seo_alias = \panix\engine\CMS::translit($model->name);
        if ($model->validate()) {
            $model->appendTo($parent);
            $message = Yii::t('shop/admin', 'CATEGORY_TREE_CREATE');
        } else {
            $message = $model->getError('seo_alias');
        }
        echo CJSON::encode(array(
            'message' => $message
        ));
        Yii::app()->end();
    }

    /**
     * Drag-n-drop nodes
     */
    public function actionMoveNode() {
        $node = Category::findOne($_GET['id']);
        $target = Category::findOne($_GET['ref']);

        if ((int) $_GET['position'] > 0) {
            $pos = (int) $_GET['position'];
            $childs = $target->children()->all();
            if (isset($childs[$pos - 1]) && $childs[$pos - 1] instanceof Category && $childs[$pos - 1]['id'] != $node->id)
                $node->moveAfter($childs[$pos - 1]);
        } else
            $node->moveAsFirst($target);

        $node->rebuildFullPath()->saveNode(false);
    }

    /**
     * Redirect to category front.
     */
    public function actionRedirect() {
        $node = Category::model()->findByPk($_GET['id']);
        $this->redirect($node->getViewUrl());
    }

    public function actionSwitchNode() {
        //$switch = $_GET['switch'];
        $node = Category::findOne($_GET['id']);
        $node->switch = ($node->switch == 1) ? 0 : 1;
        $node->saveNode();
        echo \yii\helpers\Json::encode(array(
            'switch' => $node->switch,
            'message' => Yii::t('shop/admin', ($node->switch) ? 'CATEGORY_TREE_SWITCH_OFF' : 'CATEGORY_TREE_SWITCH_NO')
        ));
        die;
    }

    /**
     * @param $id
     * @throws CHttpException
     */
    public function actionDelete($id) {
        $model = Category::findOne($id);

        //Delete if not root node
        if ($model && $model->id != 1) {
            foreach (array_reverse($model->descendants()->all()) as $subCategory) {
                $subCategory->deleteNode();
            }
            $model->deleteNode();
        }
    }

}
