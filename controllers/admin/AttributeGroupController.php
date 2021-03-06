<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\mod\shop\models\AttributeGroup;
use panix\mod\shop\models\search\AttributeGroupSearch;
use panix\engine\controllers\AdminController;

class AttributeGroupController extends AdminController
{

    public function actions()
    {
        return [
            'sortable' => [
                'class' => \panix\engine\grid\sortable\Action::class,
                'modelClass' => AttributeGroup::class,
            ],

        ];
    }


    public function actionIndex()
    {
        $this->pageName = Yii::t('shop/admin', 'ATTRIBUTE_GROUP');
        if (Yii::$app->user->can("/{$this->module->id}/{$this->id}/*") || Yii::$app->user->can("/{$this->module->id}/{$this->id}/create")) {
            $this->buttons = [
                [
                    'icon' => 'add',
                    'label' => Yii::t('shop/AttributeGroup', 'CREATE_GROUP'),
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
            'label' => Yii::t('shop/admin', 'ATTRIBUTES'),
            'url' => ['/admin/shop/attribute']
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;

        $searchModel = new AttributeGroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false)
    {

        $model = AttributeGroup::findModel($id);

        $this->pageName = Yii::t('shop/admin', 'ATTRIBUTE_GROUP');
        $this->buttons = [
            [
                'icon' => 'add',
                'label' => Yii::t('shop/AttributeGroup', 'CREATE_GROUP'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->view->params['breadcrumbs'][] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('shop/admin', 'ATTRIBUTES'),
            'url' => ['/admin/shop/attribute']
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

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        $model = new AttributeGroup;
        if (($model = $model::findOne($id)) !== null) {
            return $model;
        } else {
            $this->error404();
        }
    }

    public function actionCreate()
    {
        return $this->actionUpdate(false);
    }
}
