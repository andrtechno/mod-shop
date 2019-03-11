<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;

class ProductController extends WebController
{

    public function actionView($seo_alias)
    {
        $this->findModel($seo_alias);

        $category = $this->dataModel->mainCategory;
        if ($category) {
            $ancestors = $category->ancestors()->excludeRoot()->addOrderBy('depth')->all();
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
            if ($category->id != 1) {
                $this->breadcrumbs[] = [
                    'label' => $category->name,
                    'url' => $category->getUrl()
                ];
            }

            if ($this->dataModel->manufacturer) {
                $this->breadcrumbs[] = [
                    'label' => $category->name . ' ' . $this->dataModel->manufacturer->name,
                    'url' => [
                        '/shop/category/view',
                        'seo_alias' => $category->full_path,
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


    protected function findModel($url)
    {
        if (($this->dataModel = Product::findOne(['seo_alias' => $url])) !== null) {
            return $this->dataModel;
        } else {
            $this->error404('product not found');
        }
    }

}
