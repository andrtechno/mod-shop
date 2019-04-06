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
        ];
    }

    public function actionIndex()
    {
        $this->pageName = Yii::t('shop/admin', 'MANUFACTURER');
        $this->buttons = [
            [
                'icon' => 'icon-add',
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


        if ($id === true) {
            $model = Yii::$app->getModule("shop")->model("Manufacturer");
        } else {
            $model = $this->findModel($id);
        }


        $this->pageName = Yii::t('shop/admin', 'MANUFACTURER');
        $this->buttons = [
            [
                'icon' => 'icon-add',
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


        //$model->setScenario("admin");
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            if ($model->validate()) {

                $model->save();

                Yii::$app->session->setFlash('success', \Yii::t('app', 'SUCCESS_CREATE'));
                // return Yii::$app->getResponse()->redirect(['/admin/shop/manufacturer']);
            } else {
                print_r($model->getErrors());
                die;
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        $model = new Manufacturer;
        if (($model = $model::findOne($id)) !== null) {
            return $model;
        } else {
            $this->error404();
        }
    }

}
