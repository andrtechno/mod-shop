<?php

namespace panix\mod\shop\controllers;

use panix\engine\CMS;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\ProductImage;
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
    public function actionGetFile($dirtyAlias)
    {


        $dotParts = explode('.', $dirtyAlias);
        if (!isset($dotParts[1])) {
            throw new HttpException(404, 'Image must have extension');
        }
        $dirtyAlias = $dotParts[0];

        $size = isset(explode('_', $dirtyAlias)[1]) ? explode('_', $dirtyAlias)[1] : false;
        $alias = isset(explode('_', $dirtyAlias)[0]) ? explode('_', $dirtyAlias)[0] : false;


        /** @var $image ProductImage */
        $image = \Yii::$app->getModule('shop')->getImage($alias);

        if ($image) {
            $response = Yii::$app->getResponse();
            $response->format = \yii\web\Response::FORMAT_RAW;
            // $image->getContent($size)->show();

            $i = $image->getContent($size);


            if ($i instanceof \panix\engine\components\ImageHandler) {
                $response->format = \yii\web\Response::FORMAT_RAW;
                $i->show();
                die;
            } else {

                if ($i) {
                    $imginfo = getimagesize(Yii::getAlias('@webroot') . $i);
                    header("Content-type: {$imginfo['mime']}");
                    return readfile(Yii::getAlias('@webroot') . $i);
                } else {

                    throw new HttpException(404, 'There is no images [1]');
                }

                // die;
            }
        } else {
            throw new HttpException(404, 'There is no images');
        }
    }

    public function actionView($slug, $id)
    {

        $this->dataModel = $this->findModel($slug, $id);
        $this->dataModel->preload = true;
        if (Yii::$app->settings->get('seo', 'google_tag_manager')) {
            $dataLayer['ecomm_pagetype'] = 'offerdetail';
            $dataLayer['ecomm_totalvalue'] = (string)$this->dataModel->getFrontPrice();
            $dataLayer['ecomm_prodid'] = $id;
            $this->view->params['gtm_ecomm'] = $dataLayer;
        }

        $this->dataModel->updateCounters(['views' => 1]);
        $this->view->setModel($this->dataModel);
        $category = $this->dataModel->mainCategory;
        if ($category) {

            $ancestors = Category::getDb()->cache(function () use ($category) {
                return $category->ancestors()->excludeRoot()->addOrderBy('depth')->all();
            }, 3600);

            //$ancestors = $category->ancestors()->excludeRoot()->addOrderBy('depth')->all();
            /* $this->view->params['breadcrumbs'][] = [
                 'label' => Yii::t('shop/default', 'CATALOG'),
                 'url' => ['/catalog']
             ];*/
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

            if ($this->dataModel->brand) {
                $this->view->params['breadcrumbs'][] = [
                    'label' => $category->name . ' ' . $this->dataModel->brand->name,
                    /*'url' => Url::to([
                        '/shop/category/view',
                        'slug' => $category->full_path,
                        'brand' => $this->dataModel->brand->id
                    ])*/
                    'class' => 'active',
                    'url' => Url::to([
                        '/catalog/' . $category->full_path . '/brand/' . $this->dataModel->brand->id,
                        // 'slug' => $category->full_path,
                        // 'brand' => $this->dataModel->brand->id
                    ])
                ];
            } else {
                $this->view->params['breadcrumbs'][] = $this->dataModel->name;
            }
        }


        if ($this->dataModel->type_id) {
            $codes = [];
            if (!empty($this->dataModel->type->product_description)) {

                if (preg_match_all('/{([0-9a-zA-Z_\-]+)\.(name|value)}/', $this->dataModel->type->product_description, $matchDesc)) {
                    foreach (array_unique($matchDesc[1]) as $name) {

                        if (!isset($codes["{{$name}.value}"])) {
                            if (isset($this->dataModel->{$name})) {
                                $codes["{{$name}.value}"] = $this->dataModel->{$name}->value;
                            } else {
                                $codes["{{$name}.value}"] = '';
                            }
                        }

                        if (!isset($codes["{{$name}.name}"])) {
                            if (isset($this->dataModel->{$name})) {
                                $codes["{{$name}.name}"] = $this->dataModel->{$name}->name;
                            } else {
                                $codes["{{$name}.name}"] = '';
                            }
                        }


                    }
                }
                $this->view->description = $this->dataModel->replaceMeta($this->dataModel->type->product_description, $codes);
            }

            if (!empty($this->dataModel->type->product_title)) {
                if (preg_match_all('/{([0-9a-zA-Z_\-]+)\.(name|value)}/', $this->dataModel->type->product_title, $matchTitle)) {

                    foreach (array_unique($matchTitle[1]) as $name) {
                        if (!isset($codes["{{$name}.value}"])) {
                            if ($this->dataModel->{$name}) {
                                $codes["{{$name}.value}"] = $this->dataModel->{$name}->value;
                            }
                        }

                        if (!isset($codes["{{$name}.name}"])) {
                            if ($this->dataModel->{$name}) {
                                $codes["{{$name}.name}"] = $this->dataModel->{$name}->name;
                            }
                        }

                    }
                }
                $this->view->title = $this->dataModel->replaceMeta($this->dataModel->type->product_title, $codes);
            }

        }


        $this->view->description = $this->dataModel->description($codes);
        $this->view->title = $this->dataModel->title($codes);

        $mainImage = $this->dataModel->getMainImageObject();


        $this->sessionViews($this->dataModel->id);
        $this->view->registerMetaTag(['property' => 'og:image', 'content' => Url::toRoute($mainImage->get(), true)]);
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

        //$this->dataModel->use_configurations ||
        // if ($this->dataModel->use_configurations || $this->dataModel->processVariants())
        ProductConfigureAsset::register($this->view);
        //$this->view->registerJsFile($this->module->assetsUrl . '/js/product.view.configurations.js', ['position'=>View::POS_END]);

        if (Yii::$app->settings->get('shop', 'enable_reviews')) {
            $reviewsQuery = $this->dataModel->getReviews()->status(1);
            $reviewsCount = $reviewsQuery->roots()->count();
        } else {
            $reviewsCount = 0;
        }
       // var_dump($reviewsCount);die;
        return $this->render('view', ['model' => $this->dataModel, 'mainImage' => $mainImage, 'reviewsCount' => $reviewsCount]);
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
     * @return array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModel_old($slug)
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


    protected function findModel($slug, $id)
    {
        /** @var Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $model = $productModel::find()
            ->where(['id' => $id])
            ->published()
            //->cache()
            ->one();

        if ($model !== null) {
            if ($model->slug == $slug) {
                return $model;
            }
            $this->error404(Yii::t('shop/default', 'NOT_FOUND_PRODUCT'));
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

    private function stopFlood($expire = 5)
    {
        $readCookie = Yii::$app->request->cookies;
        $setCookie = Yii::$app->response->cookies;

        if (isset($readCookie['review_expire'])) {
            return $this->asJson(['stop_food' => $readCookie['review_expire']]);
        }
        if (!isset($readCookie['review_expire'])) {

            $setCookie->add(new \yii\web\Cookie([
                'name' => 'review_expire',
                'httpOnly' => true,
                'value' => time() + (60 * $expire),
                'expire' => 60 * $expire
            ]));
            //  return $this->asJson(['set' => $cookies['review_expire']]);
        }
        return $this->asJson(['set' => $readCookie['review_expire']]);
    }

    public function actionReviewAdd($id)
    {

        $product = Product::findOne($id);

        $alreadyRate = false;
        $readCookie = Yii::$app->request->cookies;
        $setCookie = Yii::$app->response->cookies;


        //$this->stopFlood(50);

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

        /*if (Yii::$app->user->isGuest) {
            if (isset($readCookie['review_expire'])) {
                if ($readCookie->get('review_expire')->value > time()) {
                    $response['message'] = 'STOP flood: ' . Yii::$app->formatter->asTime($readCookie->get('review_expire')->value);
                    return $this->asJson($response);
                } else {
                    $setCookie->remove('review_expire');
                }
            }
        } else {
            $time = ProductReviews::find()
                ->where(['product_id' => $product->id, 'user_id' => Yii::$app->user->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();
            if ($time) {
                $date = new \DateTime(date(\DateTime::ISO8601, $time->created_at), new \DateTimeZone(CMS::timezone()));
                if (($date->format('U') + 60 * 1) > time()) {
                    $response['message'] = 'STOP FLOOD';
                    return $this->asJson($response);
                }
            }
        }*/
        $response['message'] = 'Ошибка';
        if ($model->load($post)) {

            if (Yii::$app->request->isAjax) {
                $response['errors'] = ActiveForm::validate($model);
                if (Yii::$app->request->get('validate') == 1) {

                    return $this->asJson($response);
                }
            }
            if (!$response['errors']) {
                $response['success'] = true;

                if ($model->status) {
                    $response['published'] = true;
                    $response['message'] = $model::t('PUBLIC');
                } else {
                    $response['message'] = $model::t('PUBLIC_AFTER');
                }
                try {


                    $model->saveNode();

                    if ($model->user_id && $model->status == ProductReviews::STATUS_PUBLISHED && !$model->apply_points) {
                        $has = ProductReviews::find()->where(['apply_points' => 0, 'product_id' => $model->product_id])->count();

                        if ($has) {
                            $model->apply_points = true;
                            $model->user->setPoints(Yii::$app->settings->get('user', 'bonus_comment_value'));
                        }
                    }

                    $response['rated'] = $model->checkUserRate();

                    if (!isset($readCookie['review_expire'])) {
                        $setCookie->add(new \yii\web\Cookie([
                            'name' => 'review_expire',
                            'httpOnly' => true,
                            'value' => time() + 60 * 1,
                            'expire' => time() + 60 * 1
                        ]));
                    }


                    $ss = ProductReviews::find()->where(['product_id' => $model->product_id])->status(1)->count();
                    $score = Product::findOne($model->product_id);
                    $response['total'] = $ss;
                    $response['score'] = $score->ratingScore;
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
                            'price' => $v->price,
                            'test' => $v
                        ];


                    }
                }
            }

            $price = $model->getFrontPrice();
            if ($eav) {
                foreach ($eav as $k => $e) {
                    if (isset($result_options[$e])) {
                        $result['test'] = $result_options[$e]['test'];
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
            $result['test'] = Yii::$app->currency->number_format($price);
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
        //$session->cookieParams = ['lifetime' => 60];
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

        $data = [];
        $data2 = [];
        $prices = [];
        foreach ($attributeModels as $attr) {
            foreach ($models as $m) {
                $prices[$m->id] = $m->price;
                if (!isset($data[$attr->name])) {
                    $data[$attr->name] = ['' => '---'];
                    //  $data2[$attr->name] = ['' => '---'];
                }

                $method = 'eav_' . $attr->name;
                if (isset($m->$method->value)) {
                    $value = $m->$method->value;

                    if (!isset($data[$attr->name][$value])) {
                        $data[$attr->name][$value] = '';
                    }

                    $data[$attr->name][$value] .= $m->id;
                    $data2[$attr->name][$m->id] = $value;
                }
            }
        }

        return [
            'attributes' => $attributeModels,
            'prices' => $prices,
            'data' => $data,
            'data2' => $data2,
        ];
    }

    public function actionTags($tag)
    {
        if ($tag) {
            /** @var Product $productModel */
            $productModel = Yii::$app->getModule('shop')->model('Product');

            $query = $productModel::find();
            $query->andWhere(['!=', "{$productModel::tableName()}.availability", $productModel::STATUS_ARCHIVE]);
            $query->published();


            $query->anyTagValues($tag);
        }

        $provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $query,
            'sort' => Product::getSort(),
            'pagination' => [
                'pageSize' => 12,
                // 'defaultPageSize' =>(int)  $this->allowedPageLimit[0],
                // 'pageSizeLimit' => $this->allowedPageLimit,
            ]
        ]);
        return $this->render('@theme/modules/shop/views/catalog/view2', [
            'provider' => $provider,
            'itemView' => '_view_grid',
        ]);

    }
}
