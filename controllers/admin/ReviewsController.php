<?php

namespace panix\mod\shop\controllers\admin;

use app\modules\reviews\models\Reviews;
use panix\engine\behaviors\nestedsets\actions\DeleteNodeAction;
use panix\engine\behaviors\nestedsets\NestedSetsBehavior;
use Yii;
use panix\mod\shop\models\ProductReviews;
use panix\mod\shop\models\search\ProductReviewsSearch;
use panix\engine\controllers\AdminController;
use yii\base\InlineAction;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

class ReviewsController extends AdminController
{

    public $icon = 'comments';

    public function actions()
    {
        return [
            'delete' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\DeleteNodeAction',
                'modelClass' => ProductReviews::class,
            ],
        ];
    }

    public function actionIndex()
    {
        $this->pageName = Yii::t('shop/admin', 'REVIEWS');

        $this->view->params['breadcrumbs'][] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;

        $searchModel = new ProductReviewsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionItems($id)
    {
        $model = ProductReviews::findOne(['id' => $id]);
        $items = $model->children()->orderBy(['created_at' => SORT_DESC])->all();
        return $this->render('_items', ['items' => $items]);
    }

    public function actionReplyAdd($id)
    {

        if (Yii::$app->request->isAjax) {
            /** @var NestedSetsBehavior $model */
            $model = ProductReviews::findOne($id);
            $result = [];
            $result['status'] = false;
            $result['published'] = false;
            $reply = ProductReviews::findOne($id);
            $model = new ProductReviews();
            $model->product_id = $reply->product_id;

            $post = Yii::$app->request->post();
            if ($reply && $post) {
                if ($model->load($post)) {

                    $errors = ActiveForm::validate($model);
                    if (Yii::$app->request->get('validate') == 1) {
                        return $this->asJson($errors);
                    }

                    if (!$errors) {
                        if (Yii::$app->user->can('admin')) {
                            $model->status = 1;
                        }
                        if ($model->appendTo($reply)) {
                            $result['status'] = true;
                            $result['published'] = true;
                            $result['url'] = Url::to(['items', 'id' => $model->tree]);
                            $result['message'] = 'Ответ успешно добавлен';
                        }
                    } else {
                        $result['errors'] = $model->getErrors();
                    }

                }
                return $this->asJson($result);
                //$parent = ProductReviews::findOne(['id' => $model->tree]);
                //$items = $parent->children()->orderBy(['created_at'=>SORT_DESC])->all();
                //return $this->render('_items', ['items' => $items,'root_id'=>$model->tree]);


            }

            return $this->render('_reply_add');
        }

    }

    public function actionUpdate($id = false)
    {

        $model = ProductReviews::findOne($id);


        $this->pageName = Yii::t('shop/admin', 'REVIEWS');

        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/admin/shop']
        ];
        $this->view->params['breadcrumbs'][] = [
            'label' => $this->pageName,
            'url' => ['index']
        ];


        $isNew = $model->isNewRecord;
        $this->view->params['breadcrumbs'][] = Yii::t('app/default', ($isNew) ? 'CREATE' : 'UPDATE');
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            if ($model->validate()) {


                if ($model->user_id && $model->status == ProductReviews::STATUS_PUBLISHED && !$model->apply_points) {
                    $has = ProductReviews::find()->where(['apply_points' => 0, 'product_id' => $model->product_id])->count();
                    if ($has) {
                        $model->apply_points = true;
                        $model->user->setPoints(Yii::$app->settings->get('user', 'bonus_comment_value'));
                    }
                }


                $model->saveNode();
                return $this->redirectPage($isNew, $post);
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        return $this->actionUpdate(false);
    }

    public function actionStatus($id)
    {
        $response = [];
        $response['success'] = false;
        if (Yii::$app->request->isAjax) {
            /** @var NestedSetsBehavior $model */
            $model = ProductReviews::findOne($id);
            $model->status = Yii::$app->request->get('status');
            $model->saveNode(false);
            $response['success'] = true;
            $response['published'] = true;
            $post = Yii::$app->request->post();
            //  return $this->asJson($response);

            //if($model->user_id && $response['published']){
            //    $model->user->setPoints(Yii::$app->settings->get('user','bonus_comment_value'));
            //}


            $parent = ProductReviews::findOne(['id' => $model->tree]);
            $items = $parent->children()->orderBy(['created_at' => SORT_DESC])->all();

            return $this->render('_items', ['items' => $items, 'root_id' => $model->tree]);
        }
    }


}
