<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use yii\helpers\ArrayHelper;
use panix\engine\controllers\AdminController;

class DefaultController extends AdminController
{

    public function actionIndex()
    {

        $this->pageName = Yii::t('shop/admin', 'TYPE_PRODUCTS');
        $this->view->params['breadcrumbs'][] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;
        // $this->topButtons = array(array('label' => Yii::t('shop/admin', 'Создать тип'),
        //         'url' => $this->createUrl('create'), 'htmlOptions' => array('class' => 'btn btn-success')));
        return $this->render('index', []);
    }

}
