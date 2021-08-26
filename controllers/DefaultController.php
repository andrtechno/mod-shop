<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;


class DefaultController extends WebController {

    public function actionIndex(){
        return $this->render('index');
    }




}
