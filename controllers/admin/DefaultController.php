<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use yii\helpers\Html;
use panix\mod\shop\models\ShopProduct;
use panix\mod\shop\models\search\ShopProductSearch;
use panix\engine\controllers\AdminController;
use panix\engine\grid\sortable\SortableGridAction;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\Attribute;

class DefaultController extends AdminController {
    public $tab_errors = [];
    public function actions() {
        return [
            'dnd_sort' => [
                'class' => SortableGridAction::className(),
                'modelName' => ShopProduct::className(),
            ],
            'delete' => [
                'class' => 'panix\engine\grid\actions\DeleteAction',
                'modelClass' => ShopProduct::className(),
            ],
        ];
    }

    public function actionIndex() {
        $this->pageName = Yii::t('shop/admin', 'PRODUCTS');
        $this->buttons = [
            [
                'icon' => 'icon-add',
                'label' => Yii::t('shop/admin', 'CREATE_PRODUCT'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->breadcrumbs = [
            $this->pageName
        ];

        $searchModel = new ShopProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false) {


        if ($id === true) {
            $model = new ShopProduct;
        } else {
            $model = $this->findModel($id);
        }


        $this->pageName = Yii::t('shop/default', 'MODULE_NAME');


        if (!$model->isNewRecord) {
            $this->buttons[] = [
                'icon' => 'icon-eye',
                'label' => Yii::t('shop/admin', 'VIEW_PRODUCT'),
                'url' => $model->getUrl(),
                'options' => ['class' => 'btn btn-info', 'target' => '_blank']
            ];
        }

        $this->buttons[] = [
            'icon' => 'icon-add',
            'label' => Yii::t('shop/admin', 'CREATE_PRODUCT'),
            'url' => ['create'],
            'options' => ['class' => 'btn btn-success']
        ];

        $this->breadcrumbs[] = [
            'label' => $this->pageName,
            'url' => ['index']
        ];
        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/admin', 'PRODUCTS'),
            'url' => ['index']
        ];
        $this->breadcrumbs[] = Yii::t('app', 'UPDATE');
        $post = Yii::$app->request->post();




        // Apply use_configurations, configurable_attributes, type_id
        if (Yii::$app->request->get('ShopProduct'))
            $model->attributes = Yii::$app->request->get('ShopProduct');



        if ($model->mainCategory)
            $model->main_category_id = $model->mainCategory->id;

        
        // On create new product first display "Choose type" form first.
        if ($model->isNewRecord && isset($_GET['ShopProduct']['type_id'])) {
           // $type_id = $model->type_id;

            if (ProductType::find()->where(['id' => $model->type_id])->count() === 0)
                $this->error404(Yii::t('shop/admin', 'ERR_PRODUCT_TYPE'));
        }

        // Or set selected category from type pre-set.
        if ($model->type && !Yii::$app->request->isPost && $model->isNewRecord)
            $model->main_category_id = $model->type->main_category;

        //$model->setScenario("admin");


        $title = ($model->isNewRecord) ? Yii::t('shop/admin', 'CREATE_PRODUCT') :
                Yii::t('shop/admin', 'UPDATE_PRODUCT');
        $this->pageName = $title;
        if ($model->type)
            $title .= ' "' . Html::encode($model->type->name) . '"';
        
        
        // Set configurable attributes on new record
        if ($model->isNewRecord) {
            if ($model->use_configurations && isset($_GET['ShopProduct']['configurable_attributes']))
                $model->configurable_attributes = $_GET['ShopProduct']['configurable_attributes'];
        }

        if ($model->load($post) && $model->validate() && $this->validateAttributes($model)) {


            $model->setRelatedProducts(Yii::$app->request->post('RelatedProductId'), []);


            $model->save();
            $mainCategoryId = 1;
            if (isset($_POST['ShopProduct']['main_category_id']))
                $mainCategoryId = $_POST['ShopProduct']['main_category_id'];
            $model->setCategories(Yii::$app->request->post('categories', []), $mainCategoryId);

            $model->file = \yii\web\UploadedFile::getInstances($model, 'file');
            if ($model->file) {
                foreach ($model->file as $file) {
                    $uniqueName = \panix\engine\CMS::gen(10);
                    $file->saveAs('uploads/' . $uniqueName . '_' . $file->baseName . '.' . $file->extension);
                    $model->attachImage('uploads/' . $uniqueName . '_' . $file->baseName . '.' . $file->extension);
                }
            }
            $this->processAttributes($model);
            // Process variants
            //$this->processVariants($model);
            $this->processConfigurations($model);

            Yii::$app->session->addFlash('success', \Yii::t('app', 'SUCCESS_CREATE'));
            if ($model->isNewRecord) {
                return Yii::$app->getResponse()->redirect(['/admin/shop']);
            } else {
                return Yii::$app->getResponse()->redirect(['/admin/shop/default/update', 'id' => $model->id]);
            }
        }

        echo $this->render('update', [
            'model' => $model,
        ]);
    }
    /**
     * Validate required shop attributes
     * @param ShopProduct $model
     * @return bool
     */
    public function validateAttributes(ShopProduct $model) {
        $attributes = $model->type->shopAttributes;

        if (empty($attributes) || $model->use_configurations) {
            return true;
        }

        $errors = false;
        foreach ($attributes as $attr) {
            if ($attr->required && empty($_POST['Attribute'][$attr->name])) {
                $this->tab_errors['attributes'] = true;
                $errors = true;
                $model->addError($attr->name, Yii::t('shop/admin', 'FIEND_REQUIRED', ['field' => $attr->title]));
            }
        }

        return !$errors;
    }
    /**
     * Load attributes relative to type and available for product configurations.
     * Used on creating new product.
     */
    public function actionLoadConfigurableOptions() {
        // For configurations that  are available only dropdown and radio lists.
        // $cr = new CDbCriteria;
        //$cr->addInCondition('type', array(ShopAttribute::TYPE_DROPDOWN, ShopAttribute::TYPE_RADIO_LIST));
        //$type = ProductType::model()->with(array('shopAttributes'))->findByPk($_GET['type_id'], $cr);

        $type = ProductType::find($_GET['type_id'])
                ->joinWith('shopAttributes')
                ->where([Attribute::tableName() . '.type' => [Attribute::TYPE_DROPDOWN, Attribute::TYPE_RADIO_LIST]])
                ->one();

//echo($type->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);die;
        $data = array();
        if ($type->shopAttributes) {
            $data = array('status' => 'success');
            foreach ($type->shopAttributes as $attr) {
                $data['response'][] = array(
                    'id' => $attr->id,
                    'title' => $attr->title,
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Ошибка не найден не один атрибут'
            );
        }

        echo json_encode($data);
        die;
    }

    protected function processConfigurations(ShopProduct $model) {
        $productPks = Yii::$app->request->post('ConfigurationsProductGrid_c0', array());

        // Clear relations
        Yii::$app->db->createCommand()->delete('{{%shop_product_configurations}}', 'product_id=:id', [':id' => $model->id])->execute();

        if (!sizeof($productPks))
            return;

        foreach ($productPks as $pk) {
            Yii::$app->db->createCommand()->insert('{{%shop_product_configurations}}', [
                'product_id' => $model->id,
                'configurable_id' => $pk
            ])->execute();
        }
    }

    protected function processAttributes(ShopProduct $model) {
        $attributes = Yii::$app->request->post('Attribute', []);
        if (empty($attributes))
            return false;

        $deleteModel = ShopProduct::findOne($model->id);
        //$deleteModel->deleteEavAttributes(array(), true);

        // Delete empty values
        /*foreach ($attributes as $key => $val) {
            if (is_string($val) && $val === '')
                $attributes->remove($key);
        }*/

        return $model->setEavAttributes($attributes, true);
    }

    /**
     * Save product variants
     * @param ShopProduct $model
     */
    protected function processVariants(ShopProduct $model) {
        $dontDelete = array();

        if (!empty($_POST['variants'])) {
            foreach ($_POST['variants'] as $attribute_id => $values) {
                $i = 0;
                foreach ($values['option_id'] as $option_id) {
                    // Try to load variant from DB
                    $variant = ProductVariant::find()
                         ->where(['product_id' => $model->id,
                        'attribute_id' => $attribute_id,
                        'option_id' => $option_id])
                            ->one();
                    // If not - create new.
                    if (!$variant)
                        $variant = new ProductVariant();

                    $variant->setAttributes(array(
                        'attribute_id' => $attribute_id,
                        'option_id' => $option_id,
                        'product_id' => $model->id,
                        'price' => $values['price'][$i],
                        'price_type' => $values['price_type'][$i],
                        'sku' => $values['sku'][$i],
                            ), false);

                    $variant->save(false);
                    array_push($dontDelete, $variant->id);
                    $i++;
                }
            }
        }

        if (!empty($dontDelete)) {
            $cr = new CDbCriteria;
            $cr->addNotInCondition('id', $dontDelete);
            $cr->addCondition('product_id=' . $model->id);
            ShopProductVariant::model()->deleteAll($cr);
        } else
            ShopProductVariant::model()->deleteAllByAttributes(array('product_id' => $model->id));
    }

    protected function findModel($id) {
        $model = Yii::$app->getModule("shop")->model("ShopProduct");
        if (($model = $model::findOne($id)) !== null) {
            return $model;
        } else {
            $this->error404();
        }
    }

}
