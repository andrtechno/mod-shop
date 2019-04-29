<?php

namespace panix\mod\shop\controllers;

use panix\engine\Html;
use panix\mod\shop\ProductConfigureAsset;
use Yii;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Category;
use yii\helpers\Url;
use yii\web\View;

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


        $this->description = $this->dataModel->description();

        $this->view->title = $this->dataModel->title();



        $this->registerSessionViews($this->dataModel->id);
        $this->view->registerMetaTag(['property' => 'og:image', 'content' => Url::toRoute($this->dataModel->getMainImage()->url, true)]);
        $this->view->registerMetaTag(['property' => 'og:description', 'content' => (!empty($this->dataModel->short_description)) ? $this->dataModel->short_description : $this->dataModel->name]);
        $this->view->registerMetaTag(['property' => 'og:title', 'content' => Html::encode($this->dataModel->name)]);
        $this->view->registerMetaTag(['property' => 'og:image:alt', 'content' => Html::encode($this->dataModel->name)]);
        $this->view->registerMetaTag(['property' => 'og:type', 'content' => 'product']);
        $this->view->registerMetaTag(['property' => 'og:url', 'content' => Url::toRoute($this->dataModel->getUrl(), true)]);

        //Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl . '/product.view.js', CClientScript::POS_END);
        $this->view->registerJs("
        var penny = " . Yii::$app->currency->active->penny . ";
        var separator_thousandth = '" . Yii::$app->currency->active->separator_thousandth . "';
        var separator_hundredth = '" . Yii::$app->currency->active->separator_hundredth . "';
        ", View::POS_END);

        if ($this->dataModel->use_configurations || $this->dataModel->processVariants())
            ProductConfigureAsset::register($this->view);
            //$this->view->registerJsFile($this->module->assetsUrl . '/js/product.view.configurations.js', ['position'=>View::POS_END]);



        return $this->render('view', ['model' => $this->dataModel]);
    }

    protected function registerSessionViews($id = null)
    {
        //unset($_SESSION['views']);
        $session = Yii::$app->session->get('views');
        Yii::$app->session->setTimeout(86400 * 7);

        if (empty($session)) {
            Yii::$app->session['views'] = [];
        }

        if (isset($session)) {
            if (!in_array($id, $_SESSION['views'])) {
                array_push($_SESSION['views'], $id);
            }
        }
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
