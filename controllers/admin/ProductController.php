<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\search\ProductSearch;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\ProductVariant;
use yii\web\ForbiddenHttpException;

class ProductController extends AdminController {

    public $tab_errors = [];

    public function actions() {
        return [
            'sortable' => [
                'class' => \panix\engine\grid\sortable\Action::class,
                'modelClass' => Product::class,
            ],
            'delete' => [
                'class' => 'panix\engine\actions\DeleteAction',
                'modelClass' => Product::class,
            ],
        ];
    }

    public function actionIndex() {
        \panix\mod\shop\assets\admin\ProductIndex::register($this->view);
        $this->pageName = Yii::t('shop/admin', 'PRODUCTS');
        $this->buttons = [
            [
                'icon' => 'icon-add',
                'label' => Yii::t('shop/admin', 'CREATE_PRODUCT'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->breadcrumbs[] = $this->pageName;

        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false) {


        if ($id === true) {
            $model = new Product;
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

        $post = Yii::$app->request->post();




        // Apply use_configurations, configurable_attributes, type_id
        if (Yii::$app->request->get('Product'))
            $model->attributes = Yii::$app->request->get('Product');



        if ($model->mainCategory)
            $model->main_category_id = $model->mainCategory->id;


        // On create new product first display "Choose type" form first.
        if ($model->isNewRecord && isset($_GET['Product']['type_id'])) {
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


        $this->breadcrumbs[] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];

        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/admin', 'PRODUCTS'),
            'url' => ['index']
        ];
        $this->breadcrumbs[] = $title;


        // Set configurable attributes on new record
        if ($model->isNewRecord) {

            if ($model->use_configurations && isset($_GET['Product']['configurable_attributes']))
                $model->configurable_attributes = $_GET['Product']['configurable_attributes'];
        }








        if ($model->load($post) && $model->validate() && $this->validateAttributes($model)) {

            $model->setRelatedProducts(Yii::$app->request->post('RelatedProductId'), []);

            $model->save();

            $mainCategoryId = 1;
            if (isset($_POST['Product']['main_category_id']))
                $mainCategoryId = $_POST['Product']['main_category_id'];
            $model->setCategories(Yii::$app->request->post('categories', []), $mainCategoryId);

            $model->file = \yii\web\UploadedFile::getInstances($model, 'file');
            
            
            if(Yii::$app->request->post('AttachmentsMainId')){
                $test = $model->getImages();
                //print_r($test);
                //sdie;
            }
      
             
            if ($model->file) {
               
                foreach ($model->file as $file) {
                    
                    
                    $uniqueName = \panix\engine\CMS::gen(10);
                    $file->saveAs('uploads/' . $uniqueName . '_' . $file->baseName . '.' . $file->extension);
                    $model->attachImage('uploads/' . $uniqueName . '_' . $file->baseName . '.' . $file->extension);
                }
            }
            $this->processAttributes($model);
            // Process variants
            $this->processVariants($model);
            $this->processConfigurations($model);

            Yii::$app->session->setFlash('success', \Yii::t('app', 'SUCCESS_CREATE'));
            if ($model->isNewRecord) {
                return Yii::$app->getResponse()->redirect(['/admin/shop/product']);
            } else {
                return Yii::$app->getResponse()->redirect(['/admin/shop/product/update', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionAddOptionToAttribute() {
        $attribute = Attribute::findOne($_GET['attr_id']);

        if (!$attribute)
            $this->error404(Yii::t('shop/admin', 'ERR_LOAD_ATTR'));

        $attributeOption = new AttributeOption;
        $attributeOption->attribute_id = $attribute->id;
        $attributeOption->value = $_GET['value'];
        $attributeOption->save(false);

        echo Json::encode([
            'message' => 'Опция успешно добавлена',
            'id' => $attributeOption->id
        ]);
        Yii::$app->end();
    }

    public function actionRenderVariantTable() {
        $attribute = Attribute::findOne($_GET['attr_id']);

        if (!$attribute)
            $this->error404(Yii::t('shop/admin', 'ERR_LOAD_ATTR'));

        return $this->renderPartial('tabs/variants/_table', array(
                    'attribute' => $attribute
        ));
    }

    /**
     * Validate required shop attributes
     * @param Product $model
     * @return bool
     */
    public function validateAttributes(Product $model) {
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

    protected function processConfigurations(Product $model) {
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

    protected function processAttributes(Product $model) {
        $attributes = Yii::$app->request->post('Attribute', []);
        if (empty($attributes))
            return false;

        $deleteModel = Product::findOne($model->id);
        //$deleteModel->deleteEavAttributes(array(), true);
        // Delete empty values
        /* foreach ($attributes as $key => $val) {
          if (is_string($val) && $val === '')
          $attributes->remove($key);
          } */

        return $model->setEavAttributes($attributes, true);
    }

    /**
     * Save product variants
     * @param Product $model
     */
    protected function processVariants(Product $model) {
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
            //$cr = new CDbCriteria;
            //$cr->addNotInCondition('id', $dontDelete);
            //$cr->addCondition();
            ProductVariant::deleteAll(
                    ['AND', 'product_id=:id', ['NOT IN', 'id', $dontDelete]], [':id' => $model->id]);
            /* ProductVariant::find()->where(['NOT IN','id',$dontDelete])->deleteAll([
              'product_id'=>$model->id
              ]); */
        } else {
            //ProductVariant::find()->where(['product_id'=>$model->id])->deleteAll();
            ProductVariant::deleteAll(['product_id' => $model->id]);
        }
    }

    protected function findModel($id) {
        $model = new Product;
        if (($model = $model::findOne($id)) !== null) {
            return $model;
        } else {
            $this->error404();
        }
    }

    /**
     * Render popup windows
     * 
     * @return type
     * @throws HttpException
     */
    public function actionRenderCategoryAssignWindow() {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('window/category_assign_window');
        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', '403'));
        }
    }

    /**
     * Render popup windows
     * 
     * @return type
     * @throws HttpException
     */
    public function actionRenderDuplicateProductsWindow() {
        if (Yii::$app->request->isAjax) {
            return $this->renderPartial('window/duplicate_products_window');
        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', '403'));
        }
    }

    /**
     * Render popup windows
     * 
     * @return type
     * @throws HttpException
     */
    public function actionRenderProductsPriceWindow() {
        if (Yii::$app->request->isAjax) {
            $model = new Product;
            return $this->renderPartial('window/products_price_window', ['model' => $model]);
        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', '403'));
        }
    }

    /**
     * Set price products
     * 
     * @throws HttpException
     */
    public function actionSetProducts() {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            $product_ids = $request->post('products', array());
            parse_str($request->post('data'), $price);
            $products = Product::findAll($product_ids);
            foreach ($products as $p) {
                if (isset($p)) {
                    if (!$p->currency_id || !$p->use_configurations) { //запрещаем редактирование товаров с привязанной ценой и/или концигурациями
                        $p->price = $price['Product']['price'];
                        $p->save(false);
                    }
                }
            }

            if (Yii::$app->request->isAjax) {
                echo Json::encode([
                    'message' => 'Success'
                ]);
                Yii::$app->end();
            }
        } else {
            throw new HttpException(403, Yii::t('app/error', '403'));
        }
    }

    /**
     * Duplicate products
     */
    public function actionDuplicateProducts() {
        //TODO: return ids to find products
        $product_ids = Yii::$app->request->post('products', array());
        parse_str(Yii::$app->request->post('duplicate'), $duplicates);

        if (!isset($duplicates['copy']))
            $duplicates['copy'] = array();

        $duplicator = new \panix\mod\shop\components\ProductsDuplicator;
        $ids = $duplicator->createCopy($product_ids, $duplicates['copy']);
        echo '/admin/shop/product/?Product[id]=' . implode(',', $ids);
    }

    /**
     * Assign categories to products
     * 
     * @return type
     */
    public function actionAssignCategories() {
        $categories = Yii::$app->request->post('category_ids');
        $products = Yii::$app->request->post('product_ids');

        if (empty($categories) || empty($products))
            return;

        $products = Product::find()->where(['id' => $products])->all();

        foreach ($products as $p)
            $p->setCategories($categories, Yii::$app->request->post('main_category'));
        if (Yii::$app->request->isAjax) {
            echo Json::encode([
                'message' => 'Выбранным товарам категории изменены'
            ]);
            Yii::$app->end();
        }
    }

    public function actionUpdateIsActive() {
        $ids = Yii::$app->request->post('ids');
        $switch = (int) Yii::$app->request->post('switch');
        $models = Product::find()->where(['id' => $ids])->all();
        foreach ($models as $product) {
            if (in_array($switch, array(0, 1))) {
                $product->switch = $switch;
                $product->save();
            }
        }

        echo Json::encode([
            'message' => Yii::t('app', 'SUCCESS_UPDATE')
        ]);
        Yii::$app->end();
    }

}