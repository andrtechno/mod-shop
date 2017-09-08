<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\ShopManufacturer;
use panix\mod\shop\models\ShopProduct;

class ManufacturerController extends WebController {


    /**
     * @var array
     */
    public $allowedPageLimit;

    /**
     * Sets page limits
     *
     * @return bool
     */
    public function beforeAction($action) {
        $this->allowedPageLimit = explode(',', Yii::$app->settings->get('shop', 'per_page'));
        return true;
    }

    /**
     * Display products by manufacturer
     *
     * @param $seo_alias
     * @throws CHttpException
     */
    public function actionIndex($seo_alias) {
        $this->findModel($seo_alias);

        $query = ShopProduct::find();
        $query->attachBehaviors($query->behaviors());
        $query->published();
        $query->applyManufacturers($this->dataModel->id);



        $provider = new \panix\engine\data\ActiveDataProvider([
            'query'=>$query, 
            'id' => false,
            'pagination' => array(
                'pageSize' => $this->allowedPageLimit[0],
            )
        ]);
        return $this->render('index', array(
                    'provider' => $provider,
        ));
    }

    protected function findModel($seo_alias) {
        $model = new ShopManufacturer;
        if (($this->dataModel = $model::find()
                ->published()
                ->where(['seo_alias' => $seo_alias])
                ->one()) !== null) {
            return $this->dataModel;
        } else {
            throw new NotFoundHttpException('product not found');
        }
    }

}
