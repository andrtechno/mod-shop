<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\forms\SettingsForm;

class SettingsController extends AdminController
{

    public $icon = 'settings';

    public function actionIndex()
    {
        $this->pageName = Yii::t('app', 'SETTINGS');
        $this->breadcrumbs = [
            [
                'label' => $this->module->info['label'],
                'url' => $this->module->info['url'],
            ],
            $this->pageName
        ];
        $model = new SettingsForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $model->save();
            }
            $this->refresh();
        }
        return $this->render('index', [
            'model' => $model
        ]);
    }

}
