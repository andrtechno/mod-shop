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
                'icon' => 'add',
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

        $model = Supplier::findModel($id);

        $this->pageName = Yii::t('shop/admin', 'SUPPLIER');
        $this->buttons = [
            [
                'icon' => 'add',
                'label' => Yii::t('shop/admin', 'CREATE_SUPPLIER'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/admin/shop']
        ];
        $this->breadcrumbs[] = [
            'label' => $this->pageName,
            'url' => ['index']
        ];

        $this->breadcrumbs[] = Yii::t('app', 'UPDATE');


        $isNew = $model->isNewRecord;
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $model->save();
            Yii::$app->session->setFlash('success', Yii::t('app', ($isNew) ? 'SUCCESS_CREATE' : 'SUCCESS_UPDATE'));
            $redirect = (isset($post['redirect'])) ? $post['redirect'] : Yii::$app->request->url;
            if (!Yii::$app->request->isAjax)
                return $this->redirect($redirect);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

}
