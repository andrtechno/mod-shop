<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductNotifications;
use yii\web\Response;

class NotifyController extends WebController
{

    // public function init() {
    /// Yii::app()->request->enableCsrfValidation = false;
    //    parent::init();
    // }

    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $json = [];
        if (Yii::$app->request->isAjax) {

            $product = Product::findOne(Yii::$app->request->post('ProductNotifications')['product_id']);

            if (!$product)
                $this->error404();

            $post = Yii::$app->request->post();

            $record = new ProductNotifications();
            if ($record->load($post)) {
                // $record->attributes = array('email' => $_POST['ProductNotifications']['email']);
                $record->product_id = $product->id;
                if ($record->validate() && $record->hasEmail() === false) {
                    $record->save();
                    $json['message'] = 'Мы сообщим вам когда товар появится в наличии';
                    $json['status'] = 'OK';
                } else {
                    $json['message'] = 'Ошибка';
                    $json['status'] = 'ERROR';
                }
            }
            $json['data'] = $this->renderPartial('_form', ['model' => $record, 'product' => $product]);
        } else {

        }
        return $json;

        // $this->render('_form', array('model' => $record, 'product' => $product));
    }

}