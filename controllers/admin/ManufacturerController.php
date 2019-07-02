<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\search\ManufacturerSearch;
use panix\engine\controllers\AdminController;

class ManufacturerController extends AdminController
{

    public $icon = 'apple';

    public function actions()
    {
        return [
            'sortable' => [
                'class' => \panix\engine\grid\sortable\Action::class,
                'modelClass' => Manufacturer::class,
            ],
            'switch' => [
                'class' => \panix\engine\actions\SwitchAction::class,
                'modelClass' => Manufacturer::class,
            ],
            'delete' => [
                'class' => \panix\engine\actions\DeleteAction::class,
                'modelClass' => Manufacturer::class,
            ],
            'deleteFile' => [
                'class' => \panix\engine\actions\DeleteFileAction::class,
                'modelClass' => Manufacturer::class,
            ],
        ];
    }

    public function actionIndex()
    {
        $this->pageName = Yii::t('shop/admin', 'MANUFACTURER');
        $this->buttons = [
            [
                'icon' => 'add',
                'label' => Yii::t('shop/admin', 'CREATE_MANUFACTURER'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->breadcrumbs[] = $this->pageName;

        $searchModel = new ManufacturerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false)
    {

        $model = Manufacturer::findModel($id);


        $this->pageName = Yii::t('shop/admin', 'MANUFACTURER');
        $this->buttons = [
            [
                'icon' => 'add',
                'label' => Yii::t('shop/admin', 'CREATE_MANUFACTURER'),
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


        $isNew = $model->isNewRecord;
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            if ($model->validate()) {

                $model->save();
                Yii::$app->session->setFlash('success', Yii::t('app', ($isNew) ? 'SUCCESS_CREATE' : 'SUCCESS_UPDATE'));
                $redirect = (isset($post['redirect'])) ? $post['redirect'] : Yii::$app->request->url;
                if (!Yii::$app->request->isAjax)
                    return $this->redirect($redirect);
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


}
