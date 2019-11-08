<?php

namespace panix\mod\shop\controllers\admin;

use panix\engine\CMS;
use panix\mod\shop\models\translate\CategoryTranslate;
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

    public $icon = 'folder-open';

    public function actions()
    {
        return [
            'rename-node' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\RenameNodeAction',
                'modelClass' => Category::class,
                'successMessage' => Category::t('CATEGORY_TREE_RENAME'),
                'errorMessage' => Category::t('ERROR_CATEGORY_TREE_RENAME')
            ],
            'move-node' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\MoveNodeAction',
                'modelClass' => Category::class,
            ],
            'switch-node' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\SwitchNodeAction',
                'modelClass' => Category::class,
                'onMessage' => Category::t('CATEGORY_TREE_SWITCH_ON'),
                'offMessage' => Category::t('CATEGORY_TREE_SWITCH_OFF')
            ],
            'delete-node' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\DeleteNodeAction',
                'modelClass' => Category::class,
            ],
        ];
    }

    public function actionIndex()
    {
        /**
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $model
         */
        $model = Category::findModel(Yii::$app->request->get('id'));

        if ($model->getIsNewRecord()) {
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
        if (Yii::$app->request->get('parent_id')) {
            $model->parent_id = Category::findModel(Yii::$app->request->get('parent_id'));
        } else {
            $model->parent_id = Category::findModel(1);
        }
        if ($model->load($post) && $model->validate()) {

            if ($model->getIsNewRecord()) {


                $model->appendTo($model->parent_id);
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


    public function actionRenameNode2()
    {
        /**
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $model
         */
        if (strpos(Yii::$app->request->get('id'), 'j1_') === false) {
            $id = Yii::$app->request->get('id');
        } else {
            $id = str_replace('j1_', '', Yii::$app->request->get('id'));
        }

        $model = Category::findOne((int)$id);
        if ($model) {
            $model->name = Yii::$app->request->get('text');
            $model->slug = CMS::slug($model->name);
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

    public function actionCreateNode2()
    {
        /**
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $model
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $parent
         */
        $model = new Category;
        $parent = Category::findModel(Yii::$app->request->get('parent_id'));

        $model->name = $_GET['text'];
        $model->slug = CMS::slug($model->name);
        if ($model->validate()) {
            $model->appendTo($parent);
            $message = Yii::t('shop/Category', 'CATEGORY_TREE_CREATE');
        } else {
            $message = $model->getError('slug');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'message' => $message,
        ];
    }

    /**
     * Drag-n-drop nodes
     */
    public function actionMoveNode2()
    {
        /**
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $node
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $target
         */
        $node = Category::findModel(Yii::$app->request->get('id'));
        $target = Category::findOne(Yii::$app->request->get('ref'));


        $pos = (int)Yii::$app->request->get('position');

        if ($pos == 1) {

            $childs = $target->children()->all();
            if (isset($childs[$pos - 1]) && $childs[$pos - 1]['id'] != $node->id) {
                // die('moveAfter');
                $node->moveAfter($childs[$pos - 1]);
            }
        } elseif ($pos == 2) {
            $childs = $target->children()
                //->orderBy(['lft'=>SORT_DESC])
                ->all();
            // echo count($childs);die;
            // if (isset($childs[$pos - 1]) && $childs[$pos - 1]['id'] != $node->id) {
            // die('moveAfter');


            if (isset($childs[$pos - 1]) && $childs[$pos - 1]['id'] != $node->id) {
                $node->moveAfter($childs[$pos - 1]);
            }

        } else {
            $node->moveAsFirst($target);
        }

        $node->rebuildFullPath();
        $node->saveNode(false);
    }

    /**
     * Redirect to category front.
     */
    public function actionRedirect()
    {
        $node = Category::findModel(Yii::$app->request->get('id'));
        return $this->redirect($node->getViewUrl());
    }

    public function actionSwitchNode2()
    {
        /**
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $node
         */
        $node = Category::findOne(Yii::$app->request->get('id'));
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
     */
    public function actionDelete2($id)
    {
        /**
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $model
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $subCategory
         */
        $model = Category::findModel($id);

        //Delete if not root node
        if ($model && $model->id != 1) {
            foreach (array_reverse($model->descendants()->all()) as $subCategory) {
                $subCategory->deleteNode();
            }
            $model->deleteNode();
        }
    }

    public function actionCreateRoot()
    {

        Yii::$app->db->createCommand()->truncateTable(Category::tableName())->execute();
        Yii::$app->db->createCommand()->truncateTable(CategoryTranslate::tableName())->execute();


        $model = new Category;
        $model->name = 'Каталог продукции';
        $model->lft = 1;
        $model->rgt = 2;
        $model->depth = 1;
        $model->slug = 'root';
        $model->full_path = '';
        if ($model->validate()) {
            $model->saveNode();

            $model2 = new Category;
            $model2->name = 'Category 1';
            $model2->slug = CMS::slug($model2->name);
            $model2->appendTo($model);


            $model2 = new Category;
            $model2->name = 'Category 2';
            $model2->slug = CMS::slug($model2->name);
            $model2->appendTo($model);


            $model3 = new Category;
            $model3->name = 'Category 2-1';
            $model3->slug = CMS::slug($model3->name);
            $model3->appendTo($model2);


            $model2 = new Category;
            $model2->name = 'Category 3';
            $model2->slug = CMS::slug($model2->name);
            $model2->appendTo($model);

        } else {
            print_r($model->getErrors());
            die;
        }

        ///return $this->redirect('index');
    }
}
