<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use yii\helpers\ArrayHelper;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\search\ProductTypeSearch;
use panix\mod\shop\models\Attribute;

class DefaultController extends AdminController {

    public $icon = 'icon-t';

    /**
     * Display types list
     */
    public function actionIndex() {


        die('index');
        $this->pageName = Yii::t('shop/admin', 'TYPE_PRODUCTS');
        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->breadcrumbs[] = $this->pageName;
        // $this->topButtons = array(array('label' => Yii::t('shop/admin', 'Создать тип'),
        //         'url' => $this->createUrl('create'), 'htmlOptions' => array('class' => 'btn btn-success')));

        $searchModel = new ProductTypeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());



        return $this->render('index', array(
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ));
    }

}
