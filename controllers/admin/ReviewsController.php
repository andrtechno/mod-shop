<?php

namespace panix\mod\shop\controllers\admin;

use panix\engine\behaviors\nestedsets\actions\DeleteNodeAction;
use panix\engine\behaviors\nestedsets\NestedSetsBehavior;
use Yii;
use panix\mod\shop\models\ProductReviews;
use panix\mod\shop\models\search\ProductReviewsSearch;
use panix\engine\controllers\AdminController;
use yii\base\InlineAction;
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

    public function actionDelete2()
    {


        $act = new DeleteNodeAction;
        $act->modelClass = ProductReviews::class;
        $act->run(123);
        return $this->render('update', ['model' => true]);
        // $test = parent::actions()['delete'];
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
                        if ($model->appendTo($reply)) {
                            $result['status'] = true;
                            $result['published'] = true;
                            $result['message'] = 'Ответ успешно добавлен';
                        }
                    } else {
                        $result['errors'] = $model->getErrors();
                    }

                }
                //$parent = ProductReviews::findOne(['id' => Yii::$app->request->get('id')]);
                //$items = $parent->children()->all();
                //return $this->render('_items', ['items' => $items]);
                return $this->asJson($result);
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

            $parent = ProductReviews::findOne(['id' => $model->tree]);
            $items = $parent->children()->all();

            return $this->render('_items', ['items' => $items,'root_id'=>$model->tree]);
        }
    }


}
