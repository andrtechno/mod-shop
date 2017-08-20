<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\ShopProduct;
use yii\web\NotFoundHttpException;

class CategoryController extends WebController {

    public $model;

    public function actionIndex() {
        $all = ShopCategory::find()->all();
        $one = ShopCategory::findOne(3);
        echo Yii::$app->request->getQueryParam('language');
        return $this->render('index', [
                    'model' => $all,
                    'one' => $one
                ]);
    }

    public function actionView($url) {
        $this->findModel($url);
        return $this->render('view', ['model' => $this->model]);
    }

    protected function findModel($url) {
        $model = Yii::$app->getModule("shop")->model("ShopCategory");
        if (($this->model = $model::find()
               // ->getCategory()
                ->where(['seo_alias' => $url])
                ->one()) !== null) {
            return $this->model;
        } else {
            throw new NotFoundHttpException('product not found');
        }
    }

}
