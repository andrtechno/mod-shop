<?php

namespace panix\mod\shop\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\View;
use panix\engine\Html;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Category;
use panix\mod\shop\bundles\ProductConfigureAsset;

class ProductController extends WebController
{

    public function actionView($slug)
    {

        $this->dataModel = $this->findModel($slug);
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
                /** @var $c Category */
                $this->breadcrumbs[] = [
                    'label' => $c->name,
                    'url' => $c->getUrl()
                ];
            }

            if ($category->id != 1) {
                $this->breadcrumbs[] = [
                    'label' => $category->name,
                    'url' => $category->getUrl()
                ];
            }

            if ($this->dataModel->manufacturer) {
                $this->breadcrumbs[] = [
                    'label' => $category->name . ' ' . $this->dataModel->manufacturer->name,
                    'url' => Url::to([
                        '/shop/category/view',
                        'slug' => $category->full_path,
                        'manufacturer' => $this->dataModel->manufacturer->id
                    ])
                ];
            } else {
                $this->breadcrumbs[] = $this->dataModel->name;
            }
        }


        $this->view->description = $this->dataModel->description();

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
        var penny = " . Yii::$app->currency->active['penny'] . ";
        var separator_thousandth = '" . Yii::$app->currency->active['separator_thousandth'] . "';
        var separator_hundredth = '" . Yii::$app->currency->active['separator_hundredth'] . "';
        ", View::POS_END);

        if ($this->dataModel->use_configurations || $this->dataModel->processVariants())
            ProductConfigureAsset::register($this->view);
        //$this->view->registerJsFile($this->module->assetsUrl . '/js/product.view.configurations.js', ['position'=>View::POS_END]);


        return $this->render('view', ['model' => $this->dataModel]);
    }

    /**
     * @param string $slug
     * @return string
     */
    public function actionComments($slug)
    {
        if (Yii::$app->request->isAjax) {

        }
        $model = Product::find()
            ->where(['slug' => $slug])
            ->published()
            ->one();

        return $this->renderAjax('tabs/_comments', ['model' => $model]);
    }

    /**
     * @param $slug
     * @return array|null|Product
     */
    protected function findModel($slug)
    {
        $model = Product::find()
            ->where(['slug' => $slug])
            ->published()
            ->one();

        if ($model !== null) {
            return $model;
        } else {
            $this->error404(Yii::t('shop/default', 'NOT_FOUND_PRODUCT'));
        }
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
}
