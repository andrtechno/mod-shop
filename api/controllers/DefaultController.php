<?php

namespace panix\mod\shop\api\controllers;

use Codeception\Template\Api;
use panix\engine\api\ApiHelpers;
use panix\engine\CMS;
use panix\engine\api\Serializer;
use yii\filters\AccessControl;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AjaxFilter;
use yii\helpers\ArrayHelper;
use panix\engine\api\ApiController;
use yii\web\Response;
use panix\mod\shop\models\Product;

/**
 * Class DefaultController
 * @package panix\mod\shop\api\controllers
 */
class DefaultController extends ApiController
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['hostControl']['only'] = ['search'];
        return $b;
    }

    public function actionSearch()
    {
        $lang = Yii::$app->language;
        $q = Yii::$app->request->get('q');
        if (empty($q)) {
            $q = '+';
        }
        $json = [];
        if ($q) {
            $model = Product::find()->published()->limit(16);
            $model->applySearch($q);
            $model->andWhere(["availability" => [Product::STATUS_IN_STOCK, Product::STATUS_PREORDER]]);

            $result = $model->all();
            $json['data']['products'] = [];
            foreach ($result as $m) {
                /** @var Product $m */
                $name = "name_{$lang}";
                $json['data']['products'][] = [
                    'id' => $m->id,
                    'name' => $m->{$name},
                    'price' => $m->getFrontPrice(),
                    'currency' => Yii::$app->currency->active['symbol'],
                    //'url' => ApiHelpers::url($m->getUrl()),
                    'url' => ApiHelpers::url("/product/" . $m->slug . '-' . $m->id, true),
                    'image' => $m->getMainImage('80x80')->url,
                    'image_original' => $m->getMainImage()->url,
                ];
            }

        }
        $json['success'] = true;
        return $this->asJson($json);
    }


}


