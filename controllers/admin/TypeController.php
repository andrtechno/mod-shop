<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use yii\helpers\ArrayHelper;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\search\ProductTypeSearch;
use panix\mod\shop\models\Attribute;

class TypeController extends AdminController
{

    public $icon = 'icon-t';

    /**
     * Display types list
     */
    public function actionIndex()
    {


        $this->pageName = Yii::t('shop/admin', 'TYPE_PRODUCTS');
        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->breadcrumbs[] = $this->pageName;
        // $this->topButtons = array(array('label' => Yii::t('shop/admin', 'Создать тип'),
        //         'url' => $this->createUrl('create'), 'htmlOptions' => array('class' => 'btn btn-success')));

        $searchModel = new ProductTypeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());


        return $this->render('index', array(
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Update product type
     * @param bool $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id = false)
    {


        /* $this->breadcrumbs = array(
          Yii::t('shop/default', 'MODULE_NAME') => array('/admin/shop'),
          Yii::t('shop/admin', 'TYPE_PRODUCTS') => $this->createUrl('index'),
          $this->pageName
          ); */

        if ($id === true)
            $model = new ProductType;
        else
            $model = ProductType::findOne($id);

        if (!$model)
            $this->error404(Yii::t('shop/admin', 'NO_FOUND_TYPEPRODUCT'));

        $this->pageName = ($model->isNewRecord) ? Yii::t('shop/admin', 'Создание нового типа продукта') :
            Yii::t('shop/admin', 'Редактирование типа продукта');


        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/admin', 'TYPE_PRODUCTS'),
            'url' => ['/admin/shop/type'],
        ];
        $this->breadcrumbs[] = $this->pageName;

        \panix\mod\shop\bundles\admin\ProductTypeAsset::register($this->view);

        $post = Yii::$app->request->post();


        if ($model->load($post) && $model->validate()) {

            if (Yii::$app->request->post('categories')) {
                $model->categories_preset = serialize(Yii::$app->request->post('categories'));
                $model->main_category = Yii::$app->request->post('main_category');
            } else {
                //return defaults when all checkboxes were checked off
                $model->categories_preset = null;
                $model->main_category = 0;
            }

            if ($model->validate()) {
                $model->save();
                // Set type attributes

                $model->useAttributes(Yii::$app->request->post('attributes', []));
                return $this->redirect('index');
            }
        }

        $allAttributes = Attribute::find()
            ->where(['NOT IN', 'id', ArrayHelper::map($model->attributeRelation, 'attribute_id', 'attribute_id')])
            ->all();

        return $this->render('update', array(
            'model' => $model,
            'attributes' => $allAttributes,
        ));
    }

    /**
     * Delete type
     * @param array $id
     * @return \yii\web\Response
     */
    public function actionDelete($id = array())
    {
        if (Yii::$app->request->isPost) {
            $model = ProductType::model()->findAllByPk($_REQUEST['id']);

            if (!empty($model)) {
                foreach ($model as $m) {
                    if ($m->productsCount > 0) {
                        $this->error404(Yii::t('shop/admin', 'ERR_DEL_TYPE_PRODUCT'));
                    } else {
                        $m->delete();
                    }
                }
            }

            if (!Yii::$app->request->isAjax)
                return $this->redirect('index');
        }
    }

}
