<?php

namespace panix\mod\shop\controllers\admin;

use panix\engine\CMS;
use panix\engine\taggable\Tag;
use panix\mod\shop\components\EavBehavior;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Kit;
use panix\mod\shop\models\ProductImage;
use panix\mod\shop\models\TypeAttribute;
use Yii;
use yii\caching\TagDependency;
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
            'image-delete' => [
                'class' => 'panix\engine\actions\DeleteAction',
                'modelClass' => ProductImage::class,
                'successMassage' => 'Картинка удалена'
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['set-products', 'assign-categories', 'duplicate-products', 'update-is-active', 'update-views'])) {
            $this->enableCsrfValidation = false;
        }
        /*if (in_array($action->id, ['create', 'update'])) {
            $count = Product::find()->count();
            if ($count >= Yii::$app->params['plan'][Yii::$app->params['plan_id']]['product_limit']) {
                throw new HttpException(403, Yii::t('app/default', 'Достигнут лимит товаров {count} шт.', ['count' => $count]));
            }
        }*/

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {

        $searchModel = new ProductSearch();

        $searchModel->preload = false;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        //$searchModel->behaviors()['eav']['preload']=false;

        // $searchModel->attachBehavior('eav',ArrayHelper::merge($searchModel->behaviors()['eav'],['preload'=>false]));
        // CMS::dump($searchModel->behaviors['eav']);die;

        $this->pageName = Yii::t('shop/admin', 'PRODUCTS');
        if (Yii::$app->user->can("/{$this->module->id}/{$this->id}/*") || Yii::$app->user->can("/{$this->module->id}/{$this->id}/create")) {
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

        if (Yii::$app->user->can("/{$this->module->id}/{$this->id}/*") || Yii::$app->user->can("/{$this->module->id}/{$this->id}/create")) {
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
        if ($model->use_configurations) {
            // $model->setScenario('configurable');
        }

        $result = [];
        $attributes = (isset($model->type->shopAttributes)) ? $model->type->shopAttributes : [];
        foreach ($attributes as $a) {
            if ($a->group_id) {
                $result[$a->group->name][] = $a;
            } else {
                $result['Без группы'][] = $a;
            }

        }
        $eavList = [];
        foreach ($result as $group_name => $attributes) {
            foreach ($attributes as $a) {
                /** @var Attribute|\panix\mod\shop\components\EavBehavior $a */
                // Repopulate data from POST if exists
                if (isset($_POST['Attribute'][$a->name])) {
                    $value = $_POST['Attribute'][$a->name];
                } else {

                    $value = $model->getEavAttribute($a->name);
                    if ($a->select_many && in_array($a->type, [Attribute::TYPE_SELECT_MANY])) {
                        $value = [$value];
                    }

                }
                $model->_old_eav[$a->name] = $value;
                $eavList[$group_name][] = [
                    'attribute' => $a,
                    'value' => $value
                ];
            }
        }
//CMS::dump($eavList);die;


        if ($model->load($post) && $model->validate() && $this->validateAttributes($model) && $this->validatePrices($model)) {
            $model->setRelatedProducts(Yii::$app->request->post('RelatedProductId', []));
            //$model->setKitProducts(Yii::$app->request->post('kitProductId', []));

            if ($model->label)
                $model->label = implode(",", $model->label);

            if ($model->save()) {
                //$model->processConfigurations(Yii::$app->request->post('ConfigurationsProduct', []));
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
                    $categories = ArrayHelper::merge($categories, Yii::$app->request->post('categories', []));
                } else {

                    $categories = Yii::$app->request->post('categories', []);
                }

                $model->setCategories($categories, $mainCategoryId);
                $model->processPrices((isset(Yii::$app->request->post('Product')['prices'])) ? (array)Yii::$app->request->post('Product')['prices'] : []);
                $this->processAttributes($model);
                // Process variants
                $this->processVariants($model);
                $this->processKits($model);
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
            'eavList' => $eavList
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

    protected function processAttributes(Product $model)
    {
        $attributes = Yii::$app->request->post('Attribute', []);
        if (empty($attributes))
            return false;


        /**
         * @var EavBehavior|Product $deleteModel
         * @var EavBehavior|Product $model
         */
        //$deleteModel = Product::findOne($model->id);
        //$deleteModel->deleteEavAttributes([], true); //WARN!!! DUPLICATE DELETE QUERY
        // Delete empty values
        /*foreach ($attributes as $key => $val) {
            if (is_string($val) && $val === '') {
                unset($attributes[$key]);
            }
        }*/


        $reAttributes = [];
        foreach ($attributes as $key => $val) {

            if (in_array($key, [Attribute::TYPE_TEXT, Attribute::TYPE_TEXTAREA, Attribute::TYPE_YESNO])) {
                foreach ($val as $k => $value) {
                    $reAttributes[$k] = '"' . $value . '"';
                    if (is_string($value) && $value === '') {
                        unset($reAttributes[$k]);
                    }
                }
            } else {
                foreach ($val as $k => $value) {
                    //  if(!is_array($v)){
                    $reAttributes[$k] = $value;
                    //  }

                    if (is_array($value)) {
                        $diff = array_diff($model->_old_eav[$k], $reAttributes[$k]);
                        if ($diff) {
                            /* @todo need testing */
                            foreach ($diff as $i) {
                                TagDependency::invalidate(Yii::$app->cache, $k . '-' . $i);
                            }


                        }

                    } else {
                        if ($model->_old_eav[$k] != $reAttributes[$k]) {
                            //clear cache
                            TagDependency::invalidate(Yii::$app->cache, $k . '-' . $model->_old_eav[$k]);
                            TagDependency::invalidate(Yii::$app->cache, $k . '-' . $reAttributes[$k]);
                        }
                    }


                    if (is_string($value) && $value === '') {
                        unset($reAttributes[$k]);
                    }
                }
            }
        }


        //CMS::dump($reAttributes);
        // CMS::dump($model->_old_eav);
        // die;


        return $model->setEavAttributes($reAttributes, true);
    }

    /**
     * Save product variants
     * @param Product $model
     */
    protected function processKits(Product $model)
    {
        $dontDelete = [];

        if (!empty($_POST['kits'])) {

            foreach ($_POST['kits'] as $product_id => $data) {
                $i = 0;

                // Try to load variant from DB
                $kit = Kit::find()
                    ->where(['owner_id' => $model->id, 'product_id' => $product_id])
                    ->one();
                // If not - create new.
                if (!$kit)
                    $kit = new Kit();

                $kit->setAttributes([
                    'owner_id' => $model->id,
                    'product_id' => $product_id,
                    'price' => $data['price'],

                ], false);

                $kit->save(false);
                array_push($dontDelete, $kit->id);
                $i++;

            }
        }

        if (!empty($dontDelete)) {
            //$cr = new CDbCriteria;
            //$cr->addNotInCondition('id', $dontDelete);
            //$cr->addCondition();
            Kit::deleteAll(
                ['AND', 'owner_id=:id', ['NOT IN', 'id', $dontDelete]], [':id' => $model->id]);
            /* ProductVariant::find()->where(['NOT IN','id',$dontDelete])->deleteAll([
              'product_id'=>$model->id
              ]); */
        } else {
            //ProductVariant::find()->where(['product_id'=>$model->id])->deleteAll();
            Kit::deleteAll(['owner_id' => $model->id]);
        }

    }

    /**
     * Save product variants
     * @param Product $model
     */
    protected function processVariants(Product $model)
    {
        $dontDelete = [];

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

                    $variant->setAttributes([
                        'attribute_id' => $attribute_id,
                        'option_id' => $option_id,
                        'product_id' => $model->id,
                        'currency_id' => $values['currency'][$i],
                        'price' => $values['price'][$i],
                        'price_type' => $values['price_type'][$i],
                        'sku' => $values['sku'][$i],
                    ], false);


                    $diff = array_diff($variant->oldAttributes, $variant->attributes);

                    if ($diff) {
                        //  CMS::dump(Yii::$app->currency->currencies[$values['currency'][$i]]['rate']);die;
                        //if (isset($changedAttributes['price_purchase'])) {
                        if ($variant->oldAttributes['price'] <> $variant->attributes['price']) {
                            Yii::$app->getDb()->createCommand()->insert('{{%shop__product_price_history_test}}', [
                                'product_id' => $model->id,
                                'currency_id' => $values['currency'][$i],
                                'currency_rate' => ($values['currency'][$i]) ? Yii::$app->currency->currencies[$values['currency'][$i]]['rate'] : null,
                                'price' => $diff['price'],
                                // 'price_purchase' => $this->price_purchase,
                                'created_at' => time(),
                                'type' => ($variant->oldAttributes['price'] < $variant->attributes['price']) ? 1 : 0
                            ])->execute();
                        }
                        // }
                    }
                    // CMS::dump(array_diff($variant->attributes, $variant->oldAttributes));
                    //  die;

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
            return $this->asJson([
                'buttonText' => Product::t('GRID_OPTION_SETCATEGORY'),
                'html' => $this->renderAjax('window/category_assign_window')
            ]);
            //return $this->renderAjax('window/category_assign_window');
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

            return $this->asJson([
                'buttonText' => Product::t('GRID_OPTION_COPY'),
                'html' => $this->renderAjax('window/duplicate_products_window')
            ]);
            //return $this->renderAjax('window/duplicate_products_window');
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
            if ($ids) {
                $result['success'] = true;
                $result['message'] = 'Копия упешно создана';
            } else {
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
            Yii::$app->assetManager->bundles['yii\web\JqueryAsset'] = false;
            return $this->asJson([
                'buttonText' => Product::t('GRID_OPTION_SETPRICE'),
                'html' => $this->render('window/products_price_window', ['model' => $model])
            ]);

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
            if ($price) {
                $products = Product::findAll($product_ids);
                foreach ($products as $p) {
                    if (isset($p)) {
                        if (!$p->currency_id || !$p->use_configurations) { //запрещаем редактирование товаров с привязанной ценой и/или концигурациями
                            if ($price['Product']['price']) {
                                $p->price = $price['Product']['price'];
                                $p->save(false);
                                $result['success'] = true;
                                $result['message'] = 'Цена успешно изменена';
                            } else {
                                $result['message'] = 'Цена указана не верна';
                            }

                        }
                    }
                }
            } else {
                $result['message'] = 'Error 100';
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
            $json = [];
            $json['success'] = true;
            $json['message'] = 'Ошибка';

            $categories_ids = Yii::$app->request->post('category_ids', []);
            $products = Yii::$app->request->post('product_ids');
            $main_category = (int)Yii::$app->request->post('main_category');
            if (!$categories_ids) {
                //    $categories = [];
            }
            if (empty($products) || empty($main_category)) {
                $json['success'] = false;
            }
            if (!$main_category) {
                $json['success'] = false;
                $json['message'] = 'Не выбрана основная категория';
            }
            if ($json['success']) {
                $products = Product::find()->where(['id' => $products])->all();

                foreach ($products as $p) {
                    /** @var Product $p */
                    if ($main_category) {
                        $p->main_category_id = $main_category;
                        $p->save(false);
                    }
                    //if($categories){
                    //    $p->setCategories($categories, $main_category);
                    //}

                    $category = Category::findOne($main_category);
                    $categories = [];
                    if ($category) {
                        $tes = $category->ancestors()->excludeRoot()->all();
                        foreach ($tes as $cat) {
                            $categories[] = $cat->id;
                        }

                    }
                    $categories = ArrayHelper::merge($categories, $categories_ids);
                    $p->setCategories($categories, $main_category);
                }
                $json['message'] = 'Выбранным товарам категории изменены';
            }

            return $this->asJson($json);
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
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionUpdateViews()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result['success'] = false;
        if (Yii::$app->request->isAjax) {
            $ids = Yii::$app->request->post('ids');
            $models = Product::find()->where(['id' => $ids])->all();
            foreach ($models as $product) {
                /** @var Product $product */
                if ($product->views > 0) {
                    $product->views = 0;
                    $product->save(false);
                }
            }
            $result['success'] = true;
            $result['message'] = Yii::t('shop/Product', 'SUCCESS_UPDATE_VIEWS');
            return $this->asJson($result);

        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', 403));
        }
    }

    public function actionCreate()
    {
        return $this->actionUpdate(false);
    }

    public function actionApplyConfigurationsFilter()
    {
        $product = Product::findOne(Yii::$app->request->get('product_id'));

        // On create new product
        if (!$product) {
            $product = new Product();
            $product->configurable_attributes = Yii::$app->request->get('configurable_attributes');
        }

        return $this->render('tabs/_configurations', [
            'product' => $product,
            'clearConfigurations' => true // Show all products
        ]);
    }

    public function actionConfigurations($id)
    {
        $action = Yii::$app->request->post('action');
        $product = Product::findOne(Yii::$app->request->post('product_id'));
        $result['success'] = false;
        if ($product) {
            if ($action) {
                $result['success'] = true;
                $result['message'] = 'Конфигурация успешно добавлена';
                $product->removeConfigure($id, 'insert');
            } else {
                $result['success'] = true;
                $result['message'] = 'Конфигурация успешно убрана';
                $product->removeConfigure($id, 'delete');
            }

        }
        return $this->asJson($result);

    }

    public function actionLoadAttributes($type_id)
    {
        $attributes = \panix\mod\shop\models\TypeAttribute::findAll(['type_id' => $type_id]);
        $result = [];
        foreach ($attributes as $attribute) {
            foreach ($attribute->currentAttributes as $r) {
                $result[$r->name] = [];
                $result[$r->name]['label'] = $r->title;
                $result[$r->name]['type'] = $r->type;
                $result[$r->name]['inputName'] = "ProductSearch[eav][{$r->name}]";
                $result[$r->name]['inputId'] = "product-search-eav-{$r->name}";
                $result[$r->name]['sort'] = $r->sort;
                if ($r->options) {
                    $os = $r->getOptions();
                    if ($r->sort == SORT_ASC) {
                        $os->orderBy([AttributeOption::tableName() . '.value' => SORT_ASC]);
                    } elseif ($r->sort == SORT_DESC) {
                        $os->orderBy([AttributeOption::tableName() . '.value' => SORT_DESC]);
                    }

                    $res = $os->all();

                    foreach ($res as $opt) {
                        $result[$r->name]['options'][] = [
                            'key' => $opt->id,
                            'value' => $opt->value
                        ];
                    }

                }
            }
        }

        return $this->asJson($result);
    }

    public function actionApplyRelatedFilter()
    {
        if (Yii::$app->request->isAjax || Yii::$app->request->isPjax) {
            $product = Product::findOne(Yii::$app->request->get('product_id'));

            // On create new product
            if (!$product) {
                //  $product = new Product();
                // $product->configurable_attributes = Yii::$app->request->get('configurable_attributes');
            }

            return $this->render('tabs/_related', [
                'model' => $product,
                'exclude' => []
                //'clearConfigurations' => true // Show all products
            ]);

        } else {
            throw new ForbiddenHttpException(Yii::t('app/error', '304-5'));
        }
    }


    public function actionKitAdd()
    {
        $owner = Yii::$app->request->get('owner');
        $product = Yii::$app->request->get('product_id');
        $price = Yii::$app->request->post('price');


        $kit = Kit::find()
            ->where(['owner_id' => $owner, 'product_id' => $product])
            ->one();
        // If not - create new.
        if (!$kit)
            $kit = new Kit();

        $kit->setAttributes([
            'owner_id' => $owner,
            'product_id' => $product,
            'price' => $price,

        ], false);

        $kit->save(false);


        $result['success'] = true;
        return $this->asJson($result);
    }

}
