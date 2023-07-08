<?php

namespace panix\mod\shop\controllers\admin;

use panix\engine\behaviors\nestedsets\NestedSetsBehavior;
use panix\engine\CMS;
use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\Category;
use yii\web\Response;

/**
 * AdminController implements the CRUD actions for User model.
 */
class CategoryController extends AdminController
{

    public $icon = 'folder-open';

    public function actions()
    {
        return [
            'rename-node' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\RenameNodeAction',
                'modelClass' => Category::class,
                'successMessage' => Category::t('NODE_RENAME_SUCCESS'),
                'errorMessage' => Category::t('NODE_RENAME_ERROR')
            ],
            'move-node' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\MoveNodeAction',
                'modelClass' => Category::class,
            ],
            'switch-node' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\SwitchNodeAction',
                'modelClass' => Category::class,
                'onMessage' => Category::t('NODE_SWITCH_ON'),
                'offMessage' => Category::t('NODE_SWITCH_OFF')
            ],
            'delete-node' => [
                'class' => 'panix\engine\behaviors\nestedsets\actions\DeleteNodeAction',
                'modelClass' => Category::class,
                'disallowIds' => [1]
            ],
            'delete-file' => [
                'class' => \panix\engine\actions\DeleteFileAction::class,
                'modelClass' => Category::class,
                'saveMethod' => 'saveNode'
            ],
        ];
    }

    public function actionIndex()
    {
        /**
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $model
         */

        $model = Category::findModel(Yii::$app->request->get('id'));
        $isNew = $model->isNewRecord;
        if ($model->getIsNewRecord()) {
            $this->pageName = Yii::t('shop/Category', 'CREATE_TITLE');
        } else {
            $this->pageName = Yii::t('shop/Category', 'UPDATE_TITLE', ['name' => $model->name]);
        }

        $this->pageName = Yii::t('shop/admin', 'CATEGORIES');
        if (Yii::$app->user->can("/{$this->module->id}/{$this->id}/*") || Yii::$app->user->can("/{$this->module->id}/{$this->id}/create")) {
            $this->buttons = [
                [
                    'label' => Yii::t('shop/admin', 'CREATE_CATEGORY'),
                    'url' => ['/admin/shop/category'],
                    'options' => ['class' => 'btn btn-success']
                ]
            ];
        }
        $this->view->params['breadcrumbs'][] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;


        $post = Yii::$app->request->post();
        if (Yii::$app->request->get('parent_id')) {
            $model->parent_id = Category::findModel(Yii::$app->request->get('parent_id'));
        } else {
            $model->parent_id = Category::findModel(1);
        }
        if ($model->load($post) && $model->validate()) {

            if ($model->getIsNewRecord()) {
                $model->appendTo($model->parent_id);
                Yii::$app->session->setFlash('success', Yii::t('app/default', 'SUCCESS_UPDATE'));
                return $this->redirect(['/admin/shop/category/index']);
            } else {
                $model->saveNode();
                Yii::$app->session->setFlash('success', Yii::t('app/default', 'SUCCESS_UPDATE'));
                return $this->redirect(['/admin/shop/category/index', 'id' => $model->id]);
            }


            // return $this->redirectPage($isNew, $post);
        }


        return $this->render('index', [
            'model' => $model,
            //  'redirect'=>['/admin/shop/category/index']
        ]);
    }


    public function actionCreateNode2()
    {
        /**
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $model
         * @var \panix\engine\behaviors\nestedsets\NestedSetsBehavior|Category $parent
         */
        $model = new Category;
        $parent = Category::findModel(Yii::$app->request->get('parent_id'));

        $model->name = $_GET['text'];
        $model->slug = CMS::slug($model->name);
        if ($model->validate()) {
            $model->appendTo($parent);
            $message = Yii::t('shop/Category', 'CATEGORY_TREE_CREATE');
        } else {
            $message = $model->getError('slug');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'message' => $message,
        ];
    }


    /**
     * Redirect to category front.
     */
    public function actionRedirect()
    {
        $node = Category::findModel(Yii::$app->request->get('id'));
        return $this->redirect($node->getViewUrl());
    }

    public function actionCreateRoot()
    {
        /**
         * @var Category|NestedSetsBehavior $model
         **/
        Category::getDb()->createCommand()->truncateTable(Category::tableName())->execute();

        $model = new Category;
        $model->name_ru = 'Каталог продукции';
        $model->name_uk = 'Каталог продукції';
        $model->lft = 1;
        $model->rgt = 2;
        $model->depth = 1;
        $model->slug = 'root';
        $model->full_path = '';
        if ($model->validate()) {
            $model->saveNode();
            return $this->redirect(['index']);
        } else {
            print_r($model->getErrors());
            die;
        }

        ///return $this->redirect('index');
    }

    public function actionCreate()
    {
        return $this->actionUpdate(false);
    }

    public function actionGpt($id)
    {
        $modelGRP = new \yii\base\DynamicModel(['prompt', 'temperature','max_tokens']);
        $modelGRP->addRule(['prompt', 'max_tokens', 'frequency_penalty', 'presence_penalty', 'temperature'], 'required'); //, 'n'
        $modelGRP->addRule(['result'], 'string');
        $modelGRP->setAttributeLabels(
            [
                'prompt' => 'Запрос',
                'n' => 'Количество вариантов ответа',
                'max_tokens' => 'Макс. количество токенов',
            ]
        );
        $result=[];
        if ($modelGRP->load(Yii::$app->request->post())) {
            if ($modelGRP->validate()) {
                if (isset($modelGRP->result) && !empty($modelGRP->result)) {
                    $result['success'] = true;
                    $result['action'] = 'apply';
                    $result['result'] = $modelGRP->result;
                    return $this->asJson($result);
                }

                $category = Category::findOne($id);
                $parent = $category->parent()->one();
                $modelGRP->prompt = str_replace(['{current_category}', '{parent_category}'], [$category->name, $parent->name], $modelGRP->prompt);


                $ai = Yii::$app->chatgpt;
                $ai->setORG('org-JwpyHD3oO2fI7JMo49MbxUxM');
                $gpt = $ai->completion([
                    'model' => 'text-davinci-003',
                    'prompt' => $modelGRP->prompt,
                    'temperature' => (float)$modelGRP->temperature,
                    'max_tokens' => (int)$modelGRP->max_tokens,
                    'n' => 1,//(int)$modelGRP->n,
                    'frequency_penalty' => (float)$modelGRP->frequency_penalty,
                    'presence_penalty' => (float)$modelGRP->presence_penalty,
                ]);
                //\panix\engine\CMS::dump($gpt);die;

                $result['success'] = false;
                if (isset($gpt->error)) {
                    $result['message'] = $gpt->error->message;
                }
                if (isset($gpt->choices[0]->text)) {
                    $result['success'] = true;
                    $result['result'] = trim($gpt->choices[0]->text);
                }

                /*echo '<br><br><br>';
                if (isset($gpt->usage)) {
                    echo '<div>запрос tokens: ' . $gpt->usage->prompt_tokens . '</div>';
                    echo '<div>ответ tokens: ' . $gpt->usage->completion_tokens . '</div>';
                    echo '<div>всего tokens: ' . $gpt->usage->total_tokens . '</div>';
                }*/
            } else {
                $result['errors'] = $modelGRP->getErrors();
            }

        }


        return $this->asJson($result);

    }
}
