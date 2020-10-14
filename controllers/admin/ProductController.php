<?php

namespace panix\mod\shop\controllers\admin;

use panix\engine\CMS;
use panix\mod\shop\components\EavBehavior;
use panix\mod\shop\models\Category;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\search\ProductSearch;
use panix\engine\controllers\AdminController;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\ProductVariant;
use yii\helpers\StringHelper;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

class ProductController extends AdminController
{

    public $tab_errors = [];

    public function actions()
    {
        return [
            'sortable' => [
                'class' => 'panix\engine\grid\sortable\Action',
                'modelClass' => Product::class,
                'successMessage' => Yii::t('shop/admin', 'SORT_PRODUCT_SUCCESS_MESSAGE')
            ],
            'delete' => [
                'class' => 'panix\engine\actions\DeleteAction',
                'modelClass' => Product::class,
            ],
            'switch' => [
                'class' => 'panix\engine\actions\SwitchAction',
                'modelClass' => Product::class,
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['render-products-price-window', 'set-products'])) {
            $this->enableCsrfValidation = false;
        }
        if (in_array($action->id, ['create', 'update'])) {
            $count = Product::find()->count();
            if ($count >= Yii::$app->params['plan'][Yii::$app->params['plan_id']]['product_limit']) {
                throw new HttpException(403, Yii::t('app/default', 'Достигнут лимит товаров {count} шт.', ['count' => $count]));
            }
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {

        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        $this->pageName = Yii::t('shop/admin', 'PRODUCTS');
        if (Yii::$app->user->can("/{$this->module->id}/{$this->id}/*") ||  Yii::$app->user->can("/{$this->module->id}/{$this->id}/create")) {
            $this->buttons = [
                [
                    'icon' => 'add',
                    'label' => Yii::t('shop/admin', 'CREATE_PRODUCT'),
                    'url' => ['create'],
                    'options' => ['class' => 'btn btn-success']
                ]
            ];
        }
        $this->view->params['breadcrumbs'][] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false)
    {
        /** @var Product|\panix\mod\images\behaviors\ImageBehavior $model */
        $model = Product::findModel($id);
        $isNew = $model->isNewRecord;
        $this->pageName = Yii::t('shop/default', 'MODULE_NAME');

        if (Yii::$app->user->can("/{$this->module->id}/{$this->id}/*") ||  Yii::$app->user->can("/{$this->module->id}/{$this->id}/create")) {
            if (!$isNew && $model->switch) {
                $this->buttons[] = [
                    'icon' => 'eye',
                    'label' => Yii::t('shop/admin', 'VIEW_PRODUCT'),
                    'url' => $model->getUrl(),
                    'options' => ['class' => 'btn btn-info', 'target' => '_blank']
                ];
            }
        }
        $post = Yii::$app->request->post();


        // Apply use_configurations, configurable_attributes, type_id
        if (Yii::$app->request->get('Product'))
            $model->attributes = Yii::$app->request->get('Product');


        // On create new product first display "Choose type" form first.
        if ($isNew && isset($_GET['Product']['type_id'])) {
            // $type_id = $model->type_id;

            if (ProductType::find()->where(['id' => $model->type_id])->count() === 0)
                $this->error404(Yii::t('shop/admin', 'ERR_PRODUCT_TYPE'));
        }


        //if ($model->mainCategory)
        //    $model->main_category_id = $model->mainCategory->id;


        // Or set selected category from type pre-set.
        if ($model->type && !Yii::$app->request->isPost && $model->isNewRecord) {
            $model->main_category_id = $model->type->main_category;
        }

        //$model->setScenario("admin");


        $title = ($isNew) ? Yii::t('shop/admin', 'CREATE_PRODUCT') :
            Yii::t('shop/admin', 'UPDATE_PRODUCT');

        $this->pageName = $title;

        if ($model->type)
            $title .= ' "' . Html::encode($model->type->name) . '"';
        //print_r(Yii::$app->request->post('categories'));
        //print_r($_POST['categories']);die;
        $this->view->params['breadcrumbs'][] = [
            'label' => $this->module->info['label'],
            'url' => $this->module->info['url'],
        ];

        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('shop/admin', 'PRODUCTS'),
            'url' => ['index']
        ];
        $this->view->params['breadcrumbs'][] = $title;


        // Set configurable attributes on new record
        if ($isNew) {
            if ($model->use_configurations && isset($_GET['Product']['configurable_attributes']))
                $model->configurable_attributes = $_GET['Product']['configurable_attributes'];
        }
        if ($model->use_configurations){
            $model->setScenario('configurable');
        }
        if ($model->load($post) && $model->validate() && $this->validateAttributes($model) && $this->validatePrices($model)) {
            $model->setRelatedProducts(Yii::$app->request->post('RelatedProductId', []));
            $model->setKitProducts(Yii::$app->request->post('kitProductId', []));


            /*$model->file = \yii\web\UploadedFile::getInstances($model, 'file');
            $data=[];
            if ($model->file) {

                foreach ($model->file as $file) {
                    $image= $model->attachImage($file);
                    $data[]=[
                        'filePath'=>$image->filePath,
                        'is_main'=>$image->is_main
                    ];
                }

             //   $model->images_data = json_encode($data);
            }*/

            if ($model->save()) {

                $mainCategoryId = 1;
                if (isset(Yii::$app->request->post('Product')['main_category_id']))
                    $mainCategoryId = Yii::$app->request->post('Product')['main_category_id'];

                if (true) { //Yii::$app->settings->get('shop', 'auto_add_subcategories')
                    // Авто добавление в предков категории
                    // Нужно выбирать в админки самую последнию категории по уровню.
                    $category = Category::findOne($mainCategoryId);
                    $categories = [];
                    if ($category) {
                        $tes = $category->ancestors()->excludeRoot()->all();
                        foreach ($tes as $cat) {
                            $categories[] = $cat->id;
                        }

                    }
                    $categories = ArrayHelper::merge($categories,Yii::$app->request->post('categories', []));
                } else {

                    $categories = Yii::$app->request->post('categories', []);
                }

                $model->setCategories($categories, $mainCategoryId);



                if (isset(Yii::$app->request->post('Product')['prices']) && !empty(Yii::$app->request->post('Product')['prices'])) {
                    $model->processPrices(Yii::$app->request->post('Product')['prices']);
                }
                $this->processAttributes($model);
                // Process variants
                $this->processVariants($model);
                $this->processConfigurations($model);

                $model->file = \yii\web\UploadedFile::getInstances($model, 'file');
                if ($model->file) {
                    foreach ($model->file as $file) {
                        $model->attachImage($file);
                    }
                }
            }


            return $this->redirectPage($isNew, $post);
        } else {

            // print_r($model->getErrors());
            foreach ($model->getErrors() as $key => $error) {
                Yii::$app->session->setFlash('error', $error[0]);
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function validatePrices(Product $model)
    {
        $pricesPost = Yii::$app->request->post('ProductPrices', array());

        $errors = false;
        $orderFrom = [];

        foreach ($pricesPost as $index => $price) {
            $orderFrom[] = $price['from'];
            if ($price['value'] >= $model->price) {
                $errors = true;
                $model->addError('price', $model::t('ERROR_PRICE_MAX_BASIC'));
            }
        }

        if (count($orderFrom) !== count(array_unique($orderFrom))) {
            $errors = true;
            $model->addError('price', $model::t('ERROR_PRICE_DUPLICATE_ORDER_FROM'));
        }

        return !$errors;
    }

    public function actionAddOptionToAttribute()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $attribute = Attribute::findOne($_GET['attr_id']);

        if (!$attribute)
            $this->error404(Yii::t('shop/admin', 'ERR_LOAD_ATTR'));

        $attributeOption = new AttributeOption;
        $attributeOption->attribute_id = $attribute->id;
        $attributeOption->value = $_GET['value'];
        $attributeOption->save(false);

        return [
            'message' => 'Опция успешно добавлена',
            'id' => $attributeOption->id
        ];
    }

    public function actionRenderVariantTable()
    {
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
    public function validateAttributes(Product $model)
    {
        $attributes = $model->type->shopAttributes;

        if (empty($attributes) || $model->use_configurations) {
            return true;
        }

        $errors = false;
        foreach ($attributes as $attr) {
            if ($attr->required && empty($_POST['Attribute'][$attr->type][$attr->name])) {
                $this->tab_errors['attributes'] = true;
                $errors = true;
                $model->addError($attr->name, Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $attr->title]));
                //$attr->addError($attr->name, Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $attr->title]));
            }
        }

        return !$errors;
    }

    /**
     * Load attributes relative to type and available for product configurations.
     * Used on creating new product.
     *
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionLoadConfigurableOptions()
    {
        $data = [];
        $data['success'] = false;
        if (Yii::$app->request->isAjax) {
            // For configurations that  are available only dropdown and radio lists.
            // $cr = new CDbCriteria;
            //$cr->addInCondition('type', array(ShopAttribute::TYPE_DROPDOWN, ShopAttribute::TYPE_RADIO_LIST));
            //$type = ProductType::model()->with(array('shopAttributes'))->findByPk($_GET['type_id'], $cr);

            $type = ProductType::find()
                ->joinWith('shopAttributes')
                ->where([
                    'type_id' => Yii::$app->request->get('type_id'),
                    Attribute::tableName() . '.type' => [Attribute::TYPE_DROPDOWN, Attribute::TYPE_RADIO_LIST]
                ])
                ->one();


            if ($type) {
                if ($type->shopAttributes) {
                    $data['success'] = true;
                    foreach ($type->shopAttributes as $attr) {
                        $data['response'][] = [
                            'id' => $attr->id,
                            'title' => $attr->title,
                        ];
                    }
                } else {
                    $data['message'] = 'Ошибка не найден не один атрибут';
                }
            }
            return $this->asJson($data);
        } else {
            throw new ForbiddenHttpException();
        }

    }

    protected function processConfigurations(Product $model)
    {
        $productPks = Yii::$app->request->post('ConfigurationsProduct', []);

        // Clear relations
        Yii::$app->db->createCommand()->delete('{{%shop__product_configurations}}', ['product_id'=>$model->id])->execute();

        if (!sizeof($productPks))
            return;

        foreach ($productPks as $pk) {
            Yii::$app->db->createCommand()->insert('{{%shop__product_configurations}}', [
                'product_id' => $model->id,
                'configurable_id' => $pk
            ])->execute();
        }
    }

    protected function processAttributes(Product $model)
    {
        $attributes = Yii::$app->request->post('Attribute', []);
        if (empty($attributes))
            return false;


        /**
         * @var EavBehavior|Product $deleteModel
         * @var EavBehavior|Product $model
         */
        $deleteModel = Product::findOne($model->id);
        $deleteModel->deleteEavAttributes([], true);
        // Delete empty values
        /*foreach ($attributes as $key => $val) {
            if (is_string($val) && $val === '') {
                unset($attributes[$key]);
            }
        }*/

        $reAttributes = [];
        foreach ($attributes as $key => $val) {

            if (in_array($key, [Attribute::TYPE_TEXT, Attribute::TYPE_TEXTAREA, Attribute::TYPE_YESNO])) {
                foreach ($val as $k => $v) {
                    $reAttributes[$k] = '"' . $v . '"';
                    if (is_string($v) && $v === '') {
                        unset($reAttributes[$k]);
                    }
                }
            } else {
                foreach ($val as $k => $v) {
                    $reAttributes[$k] = $v;
                    if (is_string($v) && $v === '') {
                        unset($reAttributes[$k]);
                    }
                }
            }
        }

        return $model->setEavAttributes($reAttributes, true);
    }

    /**
     * Save product variants
     * @param Product $model
     */
    protected function processVariants(Product $model)
    {
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

    /**
     * Render popup windows
     *
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionRenderCategoryAssignWindow()
    {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('window/category_assign_window');
        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', '403'));
        }
    }


    public function actionDuplicateProducts_TEST()
    {
        $result['success'] = false;
        if (Yii::$app->request->isAjax) {

            if (Yii::$app->request->isPost) {
                $this->enableCsrfValidation = false;
                Yii::$app->response->format = Response::FORMAT_JSON;
                //TODO: return ids to find products
                $product_ids = Yii::$app->request->post('products', []);
                parse_str(Yii::$app->request->post('duplicate'), $duplicates);

                if (!isset($duplicates['copy']))
                    $duplicates['copy'] = [];

                $duplicator = new \panix\mod\shop\components\ProductsDuplicator;
                $ids = $duplicator->createCopy($product_ids, $duplicates['copy']);
                //return $this->redirect('/admin/shop/product/?Product[id]=' . implode(',', $ids));
                $result['success'] = true;
                $result['message'] = 'Копия упешно создана';
                return $result;
            } else {
                return $this->renderAjax('window/duplicate_products_window');
            }


        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', '403'));
        }
    }

    /**
     * Render popup windows
     *
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionRenderDuplicateProductsWindow()
    {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('window/duplicate_products_window');
        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', '403'));
        }
    }

    /**
     * Duplicate products
     */
    public function actionDuplicateProducts()
    {
        $result['success'] = false;

        if (Yii::$app->request->isAjax) {
            //TODO: return ids to find products
            $product_ids = Yii::$app->request->post('products', []);
            parse_str(Yii::$app->request->post('duplicate'), $duplicates);

            if (!isset($duplicates['copy']))
                $duplicates['copy'] = [];

            $duplicator = new \panix\mod\shop\components\ProductsDuplicator;
            $ids = $duplicator->createCopy($product_ids, $duplicates['copy']);
            if($ids){
                $result['success'] = true;
                $result['message'] = 'Копия упешно создана';
            }else{
                $result['message'] = 'Ошибка копирование.';
            }
            //return $this->redirect('/admin/shop/product/?Product[id]=' . implode(',', $ids));

            return $this->asJson($result);
        } else {
            throw new ForbiddenHttpException();
        }
    }

    /**
     * Render popup windows
     *
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionRenderProductsPriceWindow()
    {
        if (Yii::$app->request->isAjax) {
            $model = new Product;
            return $this->render('window/products_price_window', ['model' => $model]);
        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', '403'));
        }
    }

    /**
     * Set price products
     *
     * @throws ForbiddenHttpException
     */
    public function actionSetProducts()
    {
        $result['success'] = false;
        $request = Yii::$app->request;
        if ($request->isAjax) {
            $product_ids = $request->post('products', []);
            parse_str($request->post('data'), $price);
            $products = Product::findAll($product_ids);
            foreach ($products as $p) {
                if (isset($p)) {
                    if (!$p->currency_id || !$p->use_configurations) { //запрещаем редактирование товаров с привязанной ценой и/или концигурациями
                        $p->price = $price['Product']['price'];
                        $p->save(false);
                        $result['success'] = true;
                        $result['message'] = 'Цена успешно изменена';
                    }
                }
            }
            return $this->asJson($result);
        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', '403'));
        }
    }


    /**
     * Assign categories to products
     *
     * @return Response|boolean
     * @throws ForbiddenHttpException
     */
    public function actionAssignCategories()
    {
        //$this->enableCsrfValidation=false;
        if (Yii::$app->request->isAjax) {
            $categories = Yii::$app->request->post('category_ids');
            $products = Yii::$app->request->post('product_ids');

            if (empty($categories) || empty($products))
                return false;

            $products = Product::find()->where(['id' => $products])->all();

            foreach ($products as $p) {
                /** @var Product $p */
                $p->setCategories($categories, Yii::$app->request->post('main_category'));
            }
            return $this->asJson(['message' => 'Выбранным товарам категории изменены']);
        } else {
            throw new ForbiddenHttpException();
        }
    }

    /**
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionUpdateIsActive()
    {
        if (Yii::$app->request->isAjax) {
            $ids = Yii::$app->request->post('ids');
            $switch = (int)Yii::$app->request->post('switch');
            $models = Product::find()->where(['id' => $ids])->all();
            foreach ($models as $product) {
                /** @var Product $product */
                if (in_array($switch, [0, 1])) {
                    $product->switch = $switch;
                    $product->save();
                }
            }
            return $this->asJson(['message' => Yii::t('app/default', 'SUCCESS_UPDATE')]);
        } else {
            throw new ForbiddenHttpException();
        }
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionUpdateViews()
    {

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $ids = Yii::$app->request->post('id');
            $models = Product::find()->where(['id' => $ids])->all();
            foreach ($models as $product) {
                /** @var Product $product */
                if ($product->views > 0) {
                    $product->views = 0;
                    $product->save(false);
                }
            }

            return ['message' => Product::t('SUCCESS_UPDATE_VIEWS')];

        } else {
            throw new ForbiddenHttpException();
        }
    }

    public function actionCreate()
    {
        return $this->actionUpdate(false);
    }
}
