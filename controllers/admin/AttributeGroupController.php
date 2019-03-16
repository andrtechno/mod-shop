<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\mod\shop\models\AttributeGroup;
use panix\mod\shop\models\search\AttributeGroupSearch;
use panix\engine\controllers\AdminController;

class AttributeGroupController extends AdminController {

    public function actions() {
        return [
            'sortable' => [
                'class' => \panix\engine\grid\sortable\Action::class,
                'modelClass' => AttributeGroup::class,
            ],
        ];
    }



    public function actionIndex() {
        $this->pageName = Yii::t('shop/admin', 'ATTRIBUTE_GROUP');
        $this->buttons = [
            [
                'icon' => 'icon-add',
                'label' => Yii::t('shop/AttributeGroup', 'CREATE_GROUP'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];

        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/shop']
        ];
        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/admin', 'ATTRIBUTES'),
            'url' => ['/shop/attribute']
        ];
        $this->breadcrumbs[] = $this->pageName;

        $searchModel = new AttributeGroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false) {


        if ($id === true) {
            $model = new AttributeGroup;
        } else {
            $model = $this->findModel($id);
        }


        $this->pageName = Yii::t('shop/admin', 'ATTRIBUTE_GROUP');
        $this->buttons = [
            [
                'icon' => 'icon-add',
                'label' => Yii::t('shop/AttributeGroup', 'CREATE_GROUP'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/admin', 'ATTRIBUTES'),
            'url' => ['/shop/attribute']
        ];
        $this->breadcrumbs[] = [
            'label' => $this->pageName,
            'url' => ['index']
        ];

        $this->breadcrumbs[] = Yii::t('app', 'UPDATE');


        //$model->setScenario("admin");
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $model->save();
            Yii::$app->session->setFlash('success', \Yii::t('app', 'SUCCESS_CREATE'));
            return Yii::$app->getResponse()->redirect(['/shop/attribute-group']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    protected function findModel($id) {
        $model = new AttributeGroup;
        if (($model = $model::findOne($id)) !== null) {
            return $model;
        } else {
            $this->error404();
        }
    }

}
