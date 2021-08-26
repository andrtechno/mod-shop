<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\forms\SettingsForm;
use yii\web\UploadedFile;

class SettingsController extends AdminController
{

    public $icon = 'settings';

    public function actionIndex()
    {
        $this->pageName = Yii::t('app/default', 'SETTINGS');
        $this->view->params['breadcrumbs'] = [
            [
                'label' => $this->module->info['label'],
                'url' => $this->module->info['url'],
            ],
            $this->pageName
        ];
        $model = new SettingsForm();
        $oldWatermark = $model->attachment_wm_path;
        if ($model->load(Yii::$app->request->post())) {
            $model->attachment_wm_path = UploadedFile::getInstance($model, 'attachment_wm_path');
            if ($model->validate()) {
                if ($model->attachment_wm_path) {
                    $model->attachment_wm_path->saveAs(Yii::getAlias('@uploads') . DIRECTORY_SEPARATOR . 'watermark.' . $model->attachment_wm_path->extension);
                    $model->attachment_wm_path = 'watermark.' . $model->attachment_wm_path->extension;
                } else {
                    $model->attachment_wm_path = $oldWatermark;
                }
                $model->save();
                Yii::$app->session->setFlash("success", Yii::t('app/default', 'SUCCESS_UPDATE'));
            }else{

                foreach ($model->getErrors() as $error){
                    Yii::$app->session->setFlash("error", $error);
                }

            }
            return $this->refresh();
        }
        return $this->render('index', [
            'model' => $model
        ]);
    }

}
