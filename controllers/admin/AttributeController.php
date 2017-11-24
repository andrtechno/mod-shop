<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\search\AttributeSearch;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\Product;

class AttributeController extends AdminController {

    public $icon = 'icon-filter';

    public function actions() {
        return [
            'sortableOptions' => [
                'class' => \panix\engine\grid\sortable\Action::className(),
                'modelClass' => AttributeOption::className(),
            ],
            'sortable' => [
                'class' => \panix\engine\grid\sortable\Action::className(),
                'modelClass' => Attribute::className(),
            ],
            'switch' => [
                'class' => \panix\engine\actions\SwitchAction::className(),
                'modelClass' => Attribute::className(),
            ],
            'delete' => [
                'class' => \panix\engine\actions\DeleteAction::className(),
                'modelClass' => Attribute::className(),
            ],
        ];
    }

    public function actionIndex() {
        $searchModel = new AttributeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        $this->buttons = [
            [
                'icon' => 'icon-add',
                'label' => Yii::t('shop/admin', 'CREATE_ATTRIBUTE'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];

        $this->pageName = Yii::t('shop/admin', 'ATTRIBUTES');
        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/admin/shop']
        ];
        $this->breadcrumbs[] = $this->pageName;

        return $this->render('index', array(
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Update attribute
     * @param bool $new
     * @throws CHttpException
     */
    public function actionUpdate($id = false) {

        if ($id === true)
            $model = new Attribute;
        else {
            $model = Attribute::findOne($id);
        }

        if (!$model)
            $this->error404(Yii::t('shop/admin', 'NO_FOUND_ATTR'));


        
       $this->pageName = ($model->isNewRecord) ? $model::t('CREATE_ATTRIBUTES') : $model::t('UPDATE_ATTRIBUTES');

        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/admin/shop']
        ];
        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/admin', 'ATTRIBUTES'),
            'url' => ['index']
        ];
        $this->breadcrumbs[] = $this->pageName;



        $post = Yii::$app->request->post();


        if ($model->load($post) && $model->validate()) {
            $model->save();
            $this->saveOptions($model);
            if ($id) {
                return $this->redirect(['index']);
            } else {
                return $this->redirect(['update', 'id' => $id]);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Save attribute options
     * @param ShopAttribute $model
     */
    protected function saveOptions($model) {
        //  print_r(Yii::app()->languageManager->languages);
        //    die;
        $dontDelete = array();
        if (!empty($_POST['options'])) {
            foreach ($_POST['options'] as $key => $val) {
                if (isset($val[0]) && $val[0] != '') {
                    $index = 0;

                    $attributeOption = AttributeOption::find()
                            ->where(['id' => $key,
                                'attribute_id' => $model->id])
                            ->one();

                    if (!$attributeOption) {
                        $attributeOption = new AttributeOption;
                        $attributeOption->attribute_id = $model->id;
                    }
                    $attributeOption->save(false);

                    foreach (Yii::$app->languageManager->languages as $lang) {

                        $attributeLangOption = AttributeOption::find()
                                //->translate($lang->code)
                                ->where(['id' => $attributeOption->id])
                                ->one();
                        $attributeLangOption->value = $val[$index];
                        $attributeLangOption->save(false);
                        ++$index;
                    }
                    array_push($dontDelete, $attributeOption->id);
                }
            }
        }

        if (sizeof($dontDelete)) {
            // $cr = new CDbCriteria;
            //$cr->addNotInCondition('t.id', $dontDelete);

            $optionsToDelete = AttributeOption::findAll(
                            ['AND',
                        'attribute_id=:id',
                        ['NOT IN', 'id', $dontDelete]
                            ], [':id' => $model->id]);
        } else {
            // Clear all attribute options
            $optionsToDelete = AttributeOption::find()->where(['attribute_id' => $model->id])->all();
        }

        if (!empty($optionsToDelete)) {
            foreach ($optionsToDelete as $o)
                $o->delete();
        }
    }

    /**
     * Delete attribute
     * @param array $id
     */
    public function actionDelete($id = array()) {
        if (Yii::$app->request->isPost) {
            $model = Attribute::find(['id' => $id])->all();

            if (!empty($model)) {
                foreach ($model as $m) {
                    // $count = Product::find()->withEavAttributes(array($m->name))->count();
                    //if ($count)
                    //    throw new \yii\web\HttpException(503, Yii::t('shop/admin', 'ERR_DEL_ATTR'));
                    $m->delete();
                }
            }

            if (!Yii::$app->request->isAjax)
                return $this->redirect('index');
        }
    }

    public function getAddonsMenu() {
        return array(
            array(
                'label' => Yii::t('shop/admin', 'ATTRIBUTES_GROUP'),
                'url' => array('/admin/shop/attributeGroups'),
                'visible' => false
            ),
        );
    }

}
