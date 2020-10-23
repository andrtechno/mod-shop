<?php

namespace panix\mod\shop\controllers;

use panix\engine\CMS;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\ProductReviews;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\View;
use panix\engine\Html;
use panix\engine\controllers\WebController;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Category;
use panix\mod\shop\bundles\ProductConfigureAsset;
use yii\widgets\ActiveForm;

class ProductController extends WebController
{

    public function actionView($slug)
    {

        $this->dataModel = $this->findModel($slug);
        $this->dataModel->updateCounters(['views' => 1]);
        $this->view->setModel($this->dataModel);
        $category = $this->dataModel->mainCategory;
        if ($category) {

            $ancestors = Category::getDb()->cache(function () use ($category) {
                return $category->ancestors()->excludeRoot()->addOrderBy('depth')->all();
            }, 3600);

            //$ancestors = $category->ancestors()->excludeRoot()->addOrderBy('depth')->all();
            $this->view->params['breadcrumbs'][] = [
                'label' => Yii::t('shop/default', 'CATALOG'),
                'url' => ['/catalog']
            ];
            foreach ($ancestors as $c) {
                /** @var $c Category */
                $this->view->params['breadcrumbs'][] = [
                    'label' => $c->name,
                    'url' => $c->getUrl()
                ];
            }

            if ($category->id != 1) {
                $this->view->params['breadcrumbs'][] = [
                    'label' => $category->name,
                    'url' => $category->getUrl()
                ];
            }

            if ($this->dataModel->manufacturer) {
                $this->view->params['breadcrumbs'][] = [
                    'label' => $category->name . ' ' . $this->dataModel->manufacturer->name,
                    'url' => Url::to([
                        '/shop/category/view',
                        'slug' => $category->full_path,
                        'manufacturer' => $this->dataModel->manufacturer->id
                    ])
                ];
            } else {
                $this->view->params['breadcrumbs'][] = $this->dataModel->name;
            }
        }


        if ($this->dataModel->type_id) {
            $codes = [];
            if (!empty($this->dataModel->type->product_description)) {

                if (preg_match_all('/{eav.([0-9a-zA-Z_\-]+)\.(name|value)}/', $this->dataModel->type->product_description, $matchDesc)) {
                    foreach (array_unique($matchDesc[1]) as $m) {
                        $name = "eav_{$m}";
                        $codes["{eav.{$m}.value}"] = $this->dataModel->{$name}['value'];
                        $codes["{eav.{$m}.name}"] = $this->dataModel->{$name}['name'];
                    }
                }
                $this->view->description = $this->dataModel->replaceMeta($this->dataModel->type->product_description, $codes);
            }

            if (!empty($this->dataModel->type->product_title)) {
                if (preg_match_all('/{eav.([0-9a-zA-Z_\-]+)\.(name|value)}/', $this->dataModel->type->product_title, $matchTitle)) {
                    foreach (array_unique($matchTitle[1]) as $m) {
                        $name = "eav_{$m}";
                        if (!isset($codes["{eav.{$m}.value}"]))
                            $codes["{eav.{$m}.value}"] = $this->dataModel->{$name}['value'];
                        if (!isset($codes["{eav.{$m}.name}"]))
                            $codes["{eav.{$m}.name}"] = $this->dataModel->{$name}['name'];
                    }
                }
                $this->view->title = $this->dataModel->replaceMeta($this->dataModel->type->product_title, $codes);
            }

        }


        //$this->view->description = $this->dataModel->description($codes);

        // $this->view->title = $this->dataModel->title($codes);


        $this->sessionViews($this->dataModel->id);
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
        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $model = $productModel::find()
            ->where(['slug' => $slug])
            ->published()
            ->one();

        return $this->renderAjax('tabs/_comments', ['model' => $model]);
    }

    /**
     * @param string $slug
     * @return array|null|Product
     * @throws NotFoundHttpException
     */
    protected function findModel($slug)
    {
        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $model = $productModel::find()
            ->where(['slug' => $slug])
            ->published()
            //->cache()
            ->one();

        if ($model !== null) {
            return $model;
        } else {
            $this->error404(Yii::t('shop/default', 'NOT_FOUND_PRODUCT'));
        }
    }

    public function ___actionReviewValidate($id)
    {
        $post = Yii::$app->request->post();
        $model = new ProductReviews;
        $model->product_id = $id;
        if ($model->load($post)) {
            if (Yii::$app->request->isAjax) {
                return $this->asJson(ActiveForm::validate($model));
            }
        }
    }

    public function actionReviewAdd($id)
    {

        $product = Product::findOne($id);

        $model = new ProductReviews;
        $post = Yii::$app->request->post();
        $response = [];
        $response['success'] = false;
        $response['published'] = false;
        $model->product_id = $product->id;
        $model->status = 0;
        if (Yii::$app->user->can('admin')) {
            $model->status = 1;
        }


        if ($model->load($post)) {
            if (Yii::$app->request->isAjax) {
                $errors = ActiveForm::validate($model);
                if(Yii::$app->request->get('validate') == 1){
                    return $this->asJson($errors);
                }

                if ($errors) {
                    $response['errors'] = $errors;
                }
            }
            if (!$errors) {
                $response['success'] = true;
                if ($model->status) {
                    $response['published'] = true;
                    $response['message'] = 'Отзыв успешно добавлен';
                } else {
                    $response['message'] = 'Отзыв будет опубликован после модерации';
                }
                try {
                    $model->saveNode();
                    $ss = ProductReviews::find()->where(['product_id' => $model->product_id])->status(1)->count();
                    $response['total'] = $ss;
                } catch (\yii\db\Exception $exception) {
                    $response = $exception;
                }
            }
        }

        return $this->asJson($response);

    }

    public function actionCalculatePrice($id)
    {
        $result = [];
        $result_options = [];
        $eav = Yii::$app->request->post('eav');
        if ($id && Yii::$app->request->isAjax) {

            $model = Product::findOne($id);
            if ($model) {
                foreach ($model->processVariants() as $variant) {
                    foreach ($variant['options'] as $v) {
                        $result_options[$v->id] = [
                            'price_type' => (int)$v->price_type,
                            'price' => $v->price
                        ];


                    }
                }
            }

            $price = $model->getFrontPrice();
            if ($eav) {
                foreach ($eav as $k => $e) {
                    if (isset($result_options[$e])) {
                        $result['price_type'] = $result_options[$e]['price_type'];
                        if ($result_options[$e]['price_type']) {
                            // Price type is percent
                            $price += $price / 100 * $result_options[$e]['price'];
                        } else {
                            $price += $result_options[$e]['price'];
                        }
                    }
                }
            }

            $result['price'] = Yii::$app->currency->number_format($price);
            $result['price_formatted'] = Yii::$app->currency->number_format($price);
        } else {
            throw new HttpException(403);
        }
        return $this->asJson($result);
    }

    /**
     * @param null $id
     */
    protected function sessionViews($id = null)
    {
        $session = Yii::$app->session;
        //$session->get('views');
        //$session->setTimeout(86400 * 7);
        $session->cookieParams = ['lifetime' => 60];
        if (!isset($session['views'])) {
            $session['views'] = [];
        }

        if (isset($session['views'])) {
            if (!in_array($id, $session['views'])) {
                array_push($_SESSION['views'], $id);
            }
        }
    }

    public function getConfigurableData()
    {
        $attributeModels = Attribute::findAll(['id' => $this->dataModel->configurable_attributes]);
        $models = Product::findAll(['id' => $this->dataModel->configurations]);

        $data = array();
        $prices = array();
        foreach ($attributeModels as $attr) {
            foreach ($models as $m) {
                $prices[$m->id] = $m->price;
                if (!isset($data[$attr->name]))
                    $data[$attr->name] = ['---' => '0'];

                $method = 'eav_' . $attr->name;
                $value = $m->$method;

                if (!isset($data[$attr->name][$value]))
                    $data[$attr->name][$value] = '';

                $data[$attr->name][$value] .= $m->id . '/';
            }
        }

        return [
            'attributes' => $attributeModels,
            'prices' => $prices,
            'data' => $data,
        ];
    }

}