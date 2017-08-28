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

    public function actionView($seo_alias) {

        $this->findModel($seo_alias);
        return $this->render('view', ['model' => $this->model]);
    }

    protected function findModel($seo_alias) {
        $model = Yii::$app->getModule("shop")->model("ShopCategory");
        if (($this->model = $model::find()
                ->where(['full_path' => $seo_alias])
                ->one()) !== null) {
            return $this->model;
        } else {
            throw new NotFoundHttpException('product not found');
        }
    }

}
