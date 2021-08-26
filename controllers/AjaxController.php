<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;

class AjaxController extends WebController
{
    /**
     * Set store currency
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionCurrency($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->currency->setActive($id);
        } else {
            return $this->goHome();
        }
    }
}
