<?php

namespace panix\mod\shop\controllers;

use panix\mod\shop\components\FilterController;
use Yii;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\Product;

class ManufacturerController extends FilterController
{


    /**
     * Sets page limits
     * @var array
     */
    public $allowedPageLimit;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $this->allowedPageLimit = explode(',', Yii::$app->settings->get('shop', 'per_page'));
        return true;
    }


    /**
     * Display products by manufacturer
     * @param $slug
     * @return string
     */
    public function actionView($slug)
    {
        $this->findModel($slug);

        $query = Product::find();
        $query->attachBehaviors($query->behaviors());
        $query->published();
        $query->applyManufacturers($this->dataModel->id);

        $this->view->title = $this->dataModel->name;

        $provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $query,
            'id' => false,
            'pagination' => array(
                'pageSize' => $this->allowedPageLimit[0],
            )
        ]);
        return $this->render('view', array(
            'provider' => $provider,
            'model' => $this->dataModel
        ));
    }

    /**
     * @param $slug
     * @return mixed
     */
    protected function findModel($slug)
    {
        $this->dataModel = Manufacturer::find()
            ->where(['slug' => $slug])
            ->published()
            ->one();

        if ($this->dataModel !== null) {
            return $this->dataModel;
        } else {
            $this->error404('manufacturer not found');
        }
    }

}
