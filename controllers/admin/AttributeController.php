<?php

namespace panix\mod\shop\controllers\admin;

use panix\mod\shop\models\translate\AttributeOptionTranslate;
use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\search\AttributeSearch;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\Product;

class AttributeController extends AdminController
{

    public $icon = 'sliders';

    public function actions()
    {
        return [
            'sortableOptions' => [
                'class' => \panix\engine\grid\sortable\Action::class,
                'modelClass' => AttributeOption::class,
            ],
            'sortable' => [
                'class' => \panix\engine\grid\sortable\Action::class,
                'modelClass' => Attribute::class,
            ],
            'switch' => [
                'class' => \panix\engine\actions\SwitchAction::class,
                'modelClass' => Attribute::class,
            ],
            'delete' => [
                'class' => \panix\engine\actions\DeleteAction::class,
                'modelClass' => Attribute::class,
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new AttributeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        $this->buttons = [
            [
                'icon' => 'add',
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
     * @param bool $id
     * @return string
     */
    public function actionUpdate($id = false)
    {
        $model = Attribute::findModel($id, Yii::t('shop/admin', 'NO_FOUND_ATTR'));

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

        $isNew = $model->isNewRecord;
        if ($model->load($post) && $model->validate()) {
            $model->save();
            $this->saveOptions($model);
            if($isNew){
                $this->redirect(['update','id'=>$model->id]);
            }else{
                $this->redirectPage($isNew, $post);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Save attribute options
     * @param Attribute $model
     */
    protected function saveOptions($model)
    {
        $dontDelete = [];
        if (!empty($_POST['options'])) {

//echo \yii\helpers\VarDumper::dumpAsString($_POST['options'],10,true);die;


            foreach ($_POST['options'] as $id => $val) {
                if (isset($val[0]) && $val[0] != '') {
                    $index = 0;
                    $attributeOption = AttributeOption::find()
                        ->where(['id' => $id, 'attribute_id' => $model->id])
                        ->one();

                    if (!$attributeOption) {
                        $attributeOption = new AttributeOption;
                        $attributeOption->attribute_id = $model->id;
                    }
                    $attributeOption->save(false);


                    foreach (Yii::$app->languageManager->languages as $lang) {
                        /*$attributeLangOption = AttributeOption::find()
                            ->translate($lang->id)
                            ->where([AttributeOption::tableName() . '.id' => $attributeOption->id])
                            ->one();*/


                        $attributeLangOption = AttributeOptionTranslate::find()
                            ->where(['object_id' => $attributeOption->id, 'language_id' => $lang->id])
                            ->one();

                        if (!$attributeLangOption) {
                            $attributeLangOption = new AttributeOptionTranslate;
                            $attributeLangOption->object_id = $attributeOption->id;
                            $attributeLangOption->language_id = $lang->id;

                        }


                        $attributeLangOption->value = $val[$index];
                        $attributeLangOption->save(false);

                        ++$index;
                    }
                    array_push($dontDelete, $attributeOption->id);
                }
            }
        }

        if (count($dontDelete)) {
            $optionsToDelete = AttributeOption::find()->where([
                'AND', 'attribute_id=' . $model->id,
                ['NOT IN', 'id', $dontDelete]
            ])->all();
        } else {
            // Clear all attribute options
            $optionsToDelete = AttributeOption::find()->where(['attribute_id' => $model->id])->all();
        }


        if (!empty($optionsToDelete)) {
            foreach ($optionsToDelete as $o) {
                $o->delete();
            }
        }
    }

    /**
     * Delete attribute
     *
     * @param array $id
     * @return \yii\web\Response
     */
    public function actionDelete($id = array())
    {
        if (Yii::$app->request->isPost) {
            $model = Attribute::find()->where(['id' => $id])->all();

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

    public function getAddonsMenu()
    {
        return [
            [
                'label' => Yii::t('shop/admin', 'ATTRIBUTE_GROUP'),
                'url' => ['/admin/shop/attribute-group'],
                'visible' => true
            ],
            /*[
                'label' => Yii::t('shop/admin', 'ATTRIBUTE_GROUP'),
                //'url' => ['/admin/shop/attribute-group'],
                'visible' => true,
                'items' => [
                    [
                        'label' => Yii::t('shop/admin', 'ATTRIBUTE_GROUP'),
                        'url' => ['/admin/shop/attribute-group'],
                        'visible' => true
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'ATTRIBUTE_GROUP'),
                        'url' => ['/admin/shop/attribute-group'],
                        'visible' => true
                    ],
                ]

            ],*/
        ];
    }

}
