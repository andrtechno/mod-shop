<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\ShopProduct;
use yii\web\NotFoundHttpException;

class DefaultController extends WebController {

    public function actionIndex(){
        return $this->render('index');
    }

    public function actionView($url) {
        $this->findModel($url);

        if ($this->dataModel->mainCategory) {
            $ancestors = $this->dataModel->mainCategory->leaves()->all();
            $this->breadcrumbs[] = [
                'label'=>Yii::t('cart/default', 'BC_SHOP'),
                    'url'=>['/shop']
            ];
            foreach ($ancestors as $c) {
                $this->breadcrumbs[] = [
                    'label'=>$c->name,
                    'url'=>$c->getUrl()
                ];
            }
            // 
            // Do not add root category to breadcrumbs
            if ($this->dataModel->mainCategory->id != 1) {
                //$bc[$this->model->mainCategory->name]=$this->model->mainCategory->getViewUrl();

                $this->breadcrumbs[] = [
                    'label'=>$this->dataModel->mainCategory->name,
                    'url'=>$this->dataModel->mainCategory->getUrl()
                ];
            }
            $this->breadcrumbs[] = $this->dataModel->name;
        }
        
        return $this->render('view', ['model' => $this->dataModel]);
    }

    protected function findModel($url) {
        $model = new ShopProduct;
        if (($this->dataModel = $model::find()
                // ->getCategory()
                ->where(['seo_alias' => $url])
                ->one()) !== null) {
            return $this->dataModel;
        } else {
            throw new NotFoundHttpException('product not found');
        }
    }

}
