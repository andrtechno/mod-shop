<?php

namespace panix\mod\shop\api\controllers;

use panix\engine\CMS;
use panix\engine\api\Serializer;
use yii\filters\AccessControl;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use yii\filters\HostControl;
use yii\helpers\ArrayHelper;
use panix\engine\api\ApiActiveController;
use yii\web\Response;

/**
 * Class ProductController
 * @package panix\mod\shop\api\controllers
 */
class ProductController extends ApiActiveController
{
    public $modelClass = 'panix\mod\shop\api\models\Product';
    public $serializer = [
        'class' => Serializer::class,
    ];

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['hostControl']['except'] = array_keys(parent::actions()); //All actions
        return $b;
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->login()) {
            return [
                'access_token' => $model->login(),
            ];
        } else {
            return $model->getFirstErrors();
        }
    }


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    public function actionIndex()
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;

        $query = $modelClass::find();
        $query->sort();
        //$query->publushed();

        return Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);
    }


}


