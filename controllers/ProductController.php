<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;

class ProductController extends WebController {

    public function actionView($seo_alias) {
        $this->findModel($seo_alias);

        if ($this->dataModel->mainCategory) {
            $ancestors = $this->dataModel->mainCategory->ancestors()->excludeRoot()->addOrderBy('depth')->all();
            $this->breadcrumbs[] = [
                'label' => Yii::t('shop/default', 'CATALOG'),
                'url' => ['/shop']
            ];
            foreach ($ancestors as $c) {
                $this->breadcrumbs[] = [
                    'label' => $c->name,
                    'url' => $c->getUrl()
                ];
            }
            // 
            // Do not add root category to breadcrumbs
            if ($this->dataModel->mainCategory->id != 1) {
                $this->breadcrumbs[] = [
                    'label' => $this->dataModel->mainCategory->name,
                    'url' => $this->dataModel->mainCategory->getUrl()
                ];
            }

            if ($this->dataModel->manufacturer) {
                $this->breadcrumbs[] = [
                    'label' => $this->dataModel->mainCategory->name . ' ' . $this->dataModel->manufacturer->name,
                    'url' => [
                        '/shop/category/view',
                        'seo_alias' => $this->dataModel->mainCategory->full_path,
                        'manufacturer' => $this->dataModel->manufacturer->id
                    ]
                ];
            } else {
                $this->breadcrumbs[] = $this->dataModel->name;
            }
        }

        if (Yii::$app->settings->get('shop', 'seo_products')) {
            $this->keywords = $this->dataModel->keywords();
            $this->description = $this->dataModel->description();
            $this->title = $this->dataModel->title();
        }
        return $this->render('view', ['model' => $this->dataModel]);
    }

    protected function findModel($seo_alias) {
        $model = new Product;
        if (($this->dataModel = $model::find()
                // ->getCategory()
                ->where(['seo_alias' => $seo_alias])
                ->one()) !== null) {
            return $this->dataModel;
        } else {
            $this->error404('product not found');
        }
    }

    protected function findModel2($url) {
        $model = new Product;
        if (($this->dataModel = $model::findOne(['seo_alias' => $url])) !== null) {
            return $this->dataModel;
        } else {
            $this->error404('product not found');
        }
    }

}
