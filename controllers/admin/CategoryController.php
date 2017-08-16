<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\ShopCategory;
use yii\filters\VerbFilter;


/**
 * AdminController implements the CRUD actions for User model.
 */
class CategoryController extends AdminController {

    public function actions() {
        return [
            'moveNode' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\MoveNodeAction',
                'modelClass' => 'panix\mod\shop\models\ShopCategory',
            ],
            'deleteNode' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\DeleteNodeAction',
                'modelClass' => 'panix\mod\shop\models\ShopCategory',
            ],
            'updateNode' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\UpdateNodeAction',
                'modelClass' => 'panix\mod\shop\models\ShopCategory',
            ],
            'createNode' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\CreateNodeAction',
                'modelClass' => 'panix\mod\shop\models\ShopCategory',
            ],
        ];
    }

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex() {
        $this->pageName = Yii::t('shop/default', 'MODULE_NAME');
        $this->buttons = [
            [
                'label' => Yii::t('shop/default', 'CREATE_CATEGORY'),
                'url' => ['/admin/shop/category/create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];

        echo $this->render('index', [
 
        ]);
    }

    public function actionUpdate($id) {
        if ($id === true) {
            $model = Yii::$app->getModule("shop")->model("ShopCategory");
        } else {
            $model = $this->findModel($id);
        }

        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {






            if (Yii::$app->request->get('parent_id')) {
                $parent = $this->findModel(Yii::$app->request->get('parent_id'));
            } else {
                $parent = ShopCategory::findOne(1);
            }
            if ($model->getIsNewRecord()) {
                $model->appendTo($parent);
                $this->redirect(array('create'));
            } else {
                $model->makeRoot();
            }

            Yii::$app->session->addFlash('success', \Yii::t('app', 'SUCCESS_UPDATE'));
            // return $this->redirect(['index']);
            return Yii::$app->getResponse()->redirect(['/admin/shop/category']);
        }
        echo $this->render('update', [
            'model' => $model,
        ]);
    }

    protected function findModel($id) {
        $model = Yii::$app->getModule("shop")->model("ShopCategory");
        if (($model = $model::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
