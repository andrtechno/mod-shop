<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;

class DefaultController extends AdminController {

    /**
     * Display types list
     */
    public function actionIndex() {
        $this->pageName = Yii::t('shop/default', 'MODULE_NAME');
        $this->view->params['breadcrumbs'][] = $this->pageName;
        $items = Yii::$app->getModule($this->module->id)->getAdminMenu()['shop']['items'];
        return $this->render('index', ['items' => $items]);
    }

}
