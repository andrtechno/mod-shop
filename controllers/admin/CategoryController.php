<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\Category;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;
use yii\web\Response;

/**
 * AdminController implements the CRUD actions for User model.
 */
class CategoryController extends AdminController
{
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
    public $icon = 'folder-open';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete2' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {


        if (!Yii::$app->request->get('id')) {
            $model = new Category;
        } else {
            $model = $this->findModel(Yii::$app->request->get('id'));
        }

        if ($model->getIsNewRecord()) {
            $this->icon = 'add';
            $this->pageName = Yii::t('shop/Category', 'CREATE_TITLE');
        } else {
            $this->pageName = Yii::t('shop/Category', 'UPDATE_TITLE', ['name' => $model->name]);
        }

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


        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {

            if ($model->getIsNewRecord()) {
                if (Yii::$app->request->get('parent_id')) {
                    $parent_id = Category::findOne(Yii::$app->request->get('parent_id'));
                } else {
                    $parent_id = Category::findOne(1);
                }

                $model->appendTo($parent_id);
                Yii::$app->session->setFlash('success', Yii::t('app', 'SUCCESS_UPDATE'));
                return $this->redirect(['/admin/shop/category/index']);
            } else {
                $model->saveNode();
                Yii::$app->session->setFlash('success', Yii::t('app', 'SUCCESS_UPDATE'));
                return $this->redirect(['/admin/shop/category/index', 'id' => $model->id]);
            }
        }


        return $this->render('index', [
            'model' => $model,
        ]);
    }


    protected function findModel($id)
    {
        $model = new Category;
        if (($model = $model::findOne($id)) !== null) {
            return $model;
        } else {
            $this->error404();
        }
    }

    public function actionRenameNode()
    {


        if (strpos($_GET['id'], 'j1_') === false) {
            $id = $_GET['id'];
        } else {
            $id = str_replace('j1_', '', $_GET['id']);
        }

        $model = Category::findOne((int)$id);
        if ($model) {
            $model->name = $_GET['text'];
            $model->seo_alias = Inflector::slug($model->name);
            if ($model->validate()) {
                $model->saveNode(false);
                $success = true;
                $message = Yii::t('shop/Category', 'CATEGORY_TREE_RENAME');
            } else {
                $success = false;
                $message = Yii::t('shop/Category', 'ERROR_CATEGORY_TREE_RENAME');
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'message' => $message,
                'success' => $success
            ];

        }
    }

    public function actionCreateNode()
    {
        $model = new Category;
        $parent = Category::findOne($_GET['parent_id']);

        $model->name = $_GET['text'];
        $model->seo_alias = Inflector::slug($model->name);
        if ($model->validate()) {
            $model->appendTo($parent);
            $message = Yii::t('shop/Category', 'CATEGORY_TREE_CREATE');
        } else {
            $message = $model->getError('seo_alias');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'message' => $message,
        ];
    }

    /**
     * Drag-n-drop nodes
     */
    public function actionMoveNode()
    {
        $node = Category::findOne($_GET['id']);
        $target = Category::findOne($_GET['ref']);

        if ((int)$_GET['position'] > 0) {
            $pos = (int)$_GET['position'];
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
    public function actionRedirect()
    {
        $node = Category::model()->findByPk($_GET['id']);
        $this->redirect($node->getViewUrl());
    }

    public function actionSwitchNode()
    {
        //$switch = $_GET['switch'];
        $node = Category::findOne($_GET['id']);
        $node->switch = ($node->switch == 1) ? 0 : 1;
        $node->saveNode();

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'switch' => $node->switch,
            'message' => Yii::t('shop/Category', ($node->switch) ? 'CATEGORY_TREE_SWITCH_OFF' : 'CATEGORY_TREE_SWITCH_ON')
        ];
    }

    /**
     * @param $id
     * @throws CHttpException
     */
    public function actionDelete($id)
    {
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
