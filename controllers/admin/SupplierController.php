<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\search\SupplierSearch;
use panix\mod\shop\models\Supplier;

class SupplierController extends AdminController
{

    public $icon = 'supplier';
    public function actions()
    {
        return [
            'delete' => [
                'class' => 'panix\engine\actions\DeleteAction',
                'modelClass' => Supplier::class,
            ],

        ];
    }
    /**
     * Display types list
     */
    public function actionIndex()
    {


        $this->pageName = Yii::t('shop/admin', 'SUPPLIER');
        $this->view->params['breadcrumbs'][] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;
        if (Yii::$app->user->can("/{$this->module->id}/{$this->id}/*") ||  Yii::$app->user->can("/{$this->module->id}/{$this->id}/create")) {
            $this->buttons = [
                [
                    'icon' => 'add',
                    'label' => Yii::t('shop/admin', 'CREATE_SUPPLIER'),
                    'url' => ['create'],
                    'options' => ['class' => 'btn btn-success']
                ]
            ];
        }
        $searchModel = new SupplierSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id = false)
    {

        $model = Supplier::findModel($id);

        $this->pageName = Yii::t('shop/admin', 'SUPPLIER');
        if (Yii::$app->user->can("/{$this->module->id}/{$this->id}/*") ||  Yii::$app->user->can("/{$this->module->id}/{$this->id}/create")) {
            $this->buttons = [
                [
                    'icon' => 'add',
                    'label' => Yii::t('shop/admin', 'CREATE_SUPPLIER'),
                    'url' => ['create'],
                    'options' => ['class' => 'btn btn-success']
                ]
            ];
        }
        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/admin/shop']
        ];
        $this->view->params['breadcrumbs'][] = [
            'label' => $this->pageName,
            'url' => ['index']
        ];

        $this->view->params['breadcrumbs'][] = Yii::t('app/default', 'UPDATE');


        $isNew = $model->isNewRecord;
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $model->save();
            return $this->redirectPage($isNew, $post);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        return $this->actionUpdate(false);
    }
}
