<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\mod\shop\models\Currency;
use panix\mod\shop\models\search\CurrencySearch;
use panix\engine\controllers\AdminController;

class CurrencyController extends AdminController
{


    public $icon = 'currencies';

    public function actions()
    {
        return [
            'sortable' => [
                'class' => \panix\engine\grid\sortable\Action::class,
                'modelClass' => Currency::class,
            ],
        ];
    }

    public function actionActive($id)
    {
        $model = $this->findModel($id);
        if ($model->switch == 0)
            $model->switch = 1;
        else
            $model->switch = 0;

        if (!$model->save()) {
            Yii::$app->session->setFlash("error", "Error saving");
        }
        $model->refresh();

        if (Yii::$app->request->isAjax) { // Render the index view
            return $this->actionIndex();
        } else
            return $this->redirect(['manufacturer/index']);
    }

    public function actionIndex()
    {
        $this->pageName = Yii::t('shop/admin', 'CURRENCY');
        $this->buttons = [
            [
                'icon' => 'add',
                'label' => Yii::t('shop/admin', 'CREATE_CURRENCY'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/admin/shop']
        ];
        $this->breadcrumbs[] = $this->pageName;

        $searchModel = new CurrencySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false)
    {

        $model = Currency::findModel($id);
        $this->pageName = Yii::t('shop/admin', 'CURRENCY');
        $this->buttons = [
            [
                'icon' => 'add',
                'label' => Yii::t('shop/admin', 'CREATE_CURRENCY'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
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
