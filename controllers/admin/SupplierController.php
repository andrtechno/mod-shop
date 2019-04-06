<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\search\SupplierSearch;
use panix\mod\shop\models\Supplier;

class SupplierController extends AdminController
{

    public $icon = 'supplier';

    /**
     * Display types list
     */
    public function actionIndex()
    {


        $this->pageName = Yii::t('shop/admin', 'SUPPLIER');
        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->breadcrumbs[] = $this->pageName;
        $this->buttons = [
            [
                'icon' => 'icon-add',
                'label' => Yii::t('shop/admin', 'CREATE_SUPPLIER'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $searchModel = new SupplierSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', array(
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionUpdate($id = false)
    {


        if ($id === true) {
            $model = new Supplier;
        } else {
            $model = $this->findModel($id);
        }


        $this->pageName = Yii::t('shop/admin', 'SUPPLIER');
        $this->buttons = [
            [
                'icon' => 'icon-add',
                'label' => Yii::t('shop/admin', 'CREATE_SUPPLIER'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/shop']
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
            Yii::$app->session->setFlash('success', Yii::t('app', 'SUCCESS_CREATE'));
            return Yii::$app->getResponse()->redirect(['/shop/supplier']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        $model = new Supplier;
        if (($model = $model::findOne($id)) !== null) {
            return $model;
        } else {
            $this->error404();
        }
    }
}
