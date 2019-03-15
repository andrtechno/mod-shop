<?php

namespace panix\mod\shop\controllers;

use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Category;

class ProductController extends WebController
{

    public function actionView($seo_alias)
    {

        $this->dataModel = $this->findModel($seo_alias);
        $this->dataModel->updateCounters(['views' => 1]);
        $category = $this->dataModel->mainCategory;
        if ($category) {

            $ancestors = Category::getDb()->cache(function () use ($category) {
                return $category->ancestors()->excludeRoot()->addOrderBy('depth')->all();
            }, 3600);

            //$ancestors = $category->ancestors()->excludeRoot()->addOrderBy('depth')->all();
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

    /**
     * @param $url
     * @return array|null|\yii\db\ActiveRecord
     */
    protected function findModel($url)
    {
        $model = Product::find()
            ->where(['seo_alias' => $url])
            ->published()
            ->one();

        if ($model !== null) {
            return $model;
        } else {
            $this->error404('product not found');
        }
    }

}
