<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;

class DefaultController extends AdminController {

    public function actionIndex()
    {
    $this->icon ='shopcart';
        $this->pageName = Yii::t('shop/default', 'MODULE_NAME');
        $this->breadcrumbs[] = $this->pageName;
        return $this->render('index');
    }

}