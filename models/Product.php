<?php

namespace panix\mod\shop\models;

use app\common\modules\sitemap\Sitemap;
use app\common\modules\sitemap\behaviors\SitemapBehavior;
use panix\mod\user\models\User;
use Yii;
use panix\engine\CMS;
use panix\engine\behaviors\TranslateBehavior;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\query\ProductQuery;
use panix\mod\shop\models\translate\ProductTranslate;
use panix\mod\shop\models\RelatedProduct;
use panix\mod\shop\models\ProductCategoryRef;
use panix\mod\shop\models\ProductVariant;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use panix\engine\db\ActiveRecord;
use yii\web\Response;

class Product extends ActiveRecord
{

    use traits\ProductTrait;

    public $translationClass = ProductTranslate::class;

    /**
     * @var array of attributes used to configure product
     */
    private $_configurable_attributes;
    private $_configurable_attribute_changed = false;

    /**
     * @var array
     */
    private $_configurations;
    private $_related;
    public $file;


    const route = '/admin/shop/default';
    const MODULE_ID = 'shop';

    public static function find()
    {
        return new ProductQuery(get_called_class());
    }

    public function labels()
    {
        $result = [];
        $new = 3;//days of new
        if ((time() - 86400 * $new) <= $this->created_at) {
            $result[] = array(
                'class' => 'success new',
                'value' => 'Новый',
                'tooltip' => 'от ' . Yii::$app->formatter->asDate(date('Y-m-d', $this->created_at)) . ' до ' . Yii::$app->formatter->asDate(date('Y-m-d', $this->created_at + (86400 * $new)))
            );
        }


        if (isset($this->appliedDiscount)) {
            $result[] = array(
                'class' => 'danger discount',
                'value' => '-' . $this->discountSum,
                'tooltip' => '-' . $this->discountSum . ' до ' . $this->discountEndDate
            );
        }
        return $result;
    }

    public function getIsAvailable()
    {
        return $this->availability == 1;
    }

    public function beginCartForm()
    {
        $html = '';
        $html .= Html::beginForm(['/cart/add'], 'post', ['id' => 'form-add-cart-' . $this->id]);
        $html .= Html::hiddenInput('product_id', $this->id);
        $html .= Html::hiddenInput('product_price', $this->price);
        $html .= Html::hiddenInput('use_configurations', $this->use_configurations);
        $html .= Html::hiddenInput('configurable_id', 0);
        return $html;
    }

    public function endCartForm()
    {
        return Html::endForm();
    }

    public static function getSort()
    {
        return new \yii\data\Sort([
            //'defaultOrder'=>'ordern DESC',
            'attributes' => [
                '*',
                'price22' => [
                    'asc' => ['price' => SORT_ASC],
                    'desc' => ['price' => SORT_DESC],
                    //'default' => SORT_ASC,
                    //'label' => 'Цена1',
                ],
                'sku' => [
                    'asc' => ['sku' => SORT_ASC],
                    'desc' => ['sku' => SORT_DESC],
                ],
                'name' => [
                    'default' => SORT_ASC,
                    'asc' => ['translation.name' => SORT_ASC],
                    'desc' => ['translation.name' => SORT_DESC],
                ],
            ],
        ]);
    }


    public function getMainImage($size = false)
    {
        $image = $this->getImage();
        $result = [];
        if ($image) {
            $result['url'] = $image->getUrl($size);
            $result['title'] = ($image->alt_title) ? $image->alt_title : $this->name;
        } else {
            $result['url'] = CMS::placeholderUrl(['size' => $size]);
            $result['title'] = $this->name;
        }

        return (object)$result;
    }


    public function renderGridImage()
    {
        $small = $this->getMainImage("50x50");
        $big = $this->getMainImage();
        return Html::a(Html::img($small->url, ['alt' => $small->title, 'class' => 'img-thumbnail']), $big->url, ['title' => $this->name, 'data-fancybox' => 'gallery']);
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__product}}';
    }

    public function getUrl()
    {
        return ['/shop/product/view', 'seo_alias' => $this->seo_alias];
    }

    /* public function transactions() {
      return [
      self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
      ];
      } */

    const SCENARIO_INSERT = 'insert';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_INSERT] = ['use_configurations'];
        $scenarios['duplicate'] = [];
        return $scenarios;
    }

    /**
     * Decrease product quantity when added to cart
     */
    public function decreaseQuantity()
    {
        if ($this->auto_decrease_quantity && (int)$this->quantity > 0) {
            $this->quantity--;
            $this->save(false);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['price', 'commaToDot'],
            [['file'], 'file', 'maxFiles' => 10],
            [['origin_name'], 'string', 'max' => 255],
            [['image'], 'image'],
            ['seo_alias', '\panix\engine\validators\UrlValidator', 'attributeCompare' => 'name'],
            ['seo_alias', 'match',
                'pattern' => '/^([a-z0-9-])+$/i',
                'message' => Yii::t('app', 'PATTERN_URL')
            ],
            [['name', 'seo_alias'], 'trim'],
            [['full_description'], 'string'],
            ['use_configurations', 'boolean', 'on' => self::SCENARIO_INSERT],
            [['sku', 'full_description'], 'default'], // установим ... как NULL, если они пустые
            [['name', 'seo_alias', 'main_category_id', 'price'], 'required'],
            [['name', 'seo_alias'], 'string', 'max' => 255],
            [['manufacturer_id', 'type_id', 'quantity', 'views', 'added_to_cart_count', 'ordern', 'category_id', 'currency_id'], 'integer'],
            [['name', 'seo_alias', 'full_description', 'use_configurations'], 'safe'],
            //  [['c1'], 'required'], // Attribute field
            // [['c1'], 'string', 'max' => 255], // Attribute field
        ];
    }

    public function processVariants()
    {
        $result = array();
        foreach ($this->variants as $v) {
            //print_r($v);die;
            $result[$v->productAttribute->id]['attribute'] = $v->productAttribute;
            $result[$v->productAttribute->id]['options'][] = $v;
        };
        return $result;
    }

    public function beforeValidate()
    {
        // For configurable product set 0 price
        if ($this->use_configurations)
            $this->price = 0;

        return parent::beforeValidate();
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id'])->cache(3600);
    }

    /* public function getCategory2() {
      return $this->hasOne(Category::className(), ['id' => 'category_id']);
      } */

    public function getManufacturer()
    {
        return $this->hasOne(Manufacturer::class, ['id' => 'manufacturer_id'])->cache(3600);
    }

    public function getType()
    {
        return $this->hasOne(ProductType::class, ['id' => 'type_id'])->cache(3600);
    }

    public function getType2()
    {
        return $this->hasOne(ProductType::class, ['type_id' => 'id'])->cache(3600);
    }

    public function getTranslations()
    {
        return $this->hasMany($this->translationClass, ['object_id' => 'id'])->cache(3600);
    }

    public function getTranslation()
    {
        return $this->hasOne($this->translationClass, ['object_id' => 'id'])->cache(3600);
    }

    public function getRelated()
    {
        return $this->hasMany(RelatedProduct::class, ['related_id' => 'id'])->cache(3600);
    }

    public function getRelatedProductCount()
    {
        return $this->hasMany(RelatedProduct::class, ['product_id' => 'id'])->cache(3600)->count();
    }

    public function getRelatedProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])
            ->viaTable(RelatedProduct::tableName(), ['related_id' => 'id'])->cache(3600);
    }

    public function getCategorization()
    {
        return $this->hasMany(ProductCategoryRef::class, ['product' => 'id'])->cache(3600);
    }

    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category'])->via('categorization')->cache(3600);
    }

    public function getPrices()
    {
        return $this->hasMany(ProductPrices::class, ['product_id' => 'id']);
    }

    public function getMainCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category'])
            ->via('categorization', function ($query) {
                $query->where(['is_main' => 1]);
            })->cache(3600);
    }

    public function getVariants()
    {
        return $this->hasMany(ProductVariant::class, ['product_id' => 'id'])
            ->joinWith(['productAttribute', 'option'])
            ->orderBy('{{%shop__attribute_option}}.ordern')->cache(3600);
    }

//'variants' => array(self::HAS_MANY, 'ProductVariant', array('product_id'), 'with' => array('attribute', 'option'), 'order' => 'option.ordern'),

    /**
     * @param array $prices
     */
    public function processPrices(array $prices)
    {
        $dontDelete = array();

        foreach ($prices as $index => $price) {
            if ($price['value'] > 0) {

                $record = ProductPrices::find()->where(array(
                    'id' => $index,
                    'product_id' => $this->id,
                ))->one();

                if (!$record) {
                    $record = new ProductPrices;
                }
                $record->free_from = $price['free_from'];
                $record->value = $price['value'];
                $record->product_id = $this->id;
                $record->save();

                $dontDelete[] = $record->id;
            }
        }

        // Delete not used relations
        if (sizeof($dontDelete) > 0) {
            ProductPrices::deleteAll(
                ['AND', 'product_id=:id', ['NOT IN', 'id', $dontDelete]], [':id' => $this->id]);
        } else {
            // Delete all relations
            ProductPrices::deleteAll('product_id=:id', [':id' => $this->id]);
        }

    }

    /**
     * Set product categories and main category
     * @param array $categories ids.
     * @param integer $main_category Main category id.
     */
    public function setCategories(array $categories, $main_category)
    {
        $dontDelete = array();


        // if (!Category::model()->countByAttributes(array('id' => $main_category)))
        //    $main_category = 1;

        if (!in_array($main_category, $categories))
            array_push($categories, $main_category);


        foreach ($categories as $c) {
            /* $count = ProductCategoryRef::model()->countByAttributes(array(
              'category' => $c,
              'product' => $this->id,
              )); */
            $count = ProductCategoryRef::find()->where(array(
                'category' => $c,
                'product' => $this->id,
            ))->count();


            if ($count == 0) {
                $record = new ProductCategoryRef;
                $record->category = (int)$c;
                $record->product = $this->id;
                //$record->switch = $this->switch; // new param
                $record->save(false);
            }

            $dontDelete[] = $c;
        }

        // Clear main category
        ProductCategoryRef::updateAll([
            'is_main' => 0,
            // 'switch' => $this->switch
        ], 'product=:p', array(':p' => $this->id));

        // Set main category
        ProductCategoryRef::updateAll(array(
            'is_main' => 1,
            'switch' => $this->switch,
        ), 'product=:p AND category=:c', array(':p' => $this->id, ':c' => $main_category));

        // Delete not used relations
        if (sizeof($dontDelete) > 0) {
            // $cr = new CDbCriteria;
            // $cr->addNotInCondition('category', $dontDelete);
            //    $query = ShopProductCategoryRef::deleteAll(['product=:id','category NOT IN (:cats)'],[':id'=>$this->id,':cats'=>implode(',',$dontDelete)]);
            $query = ProductCategoryRef::deleteAll(
                ['AND', 'product=:id', ['NOT IN', 'category', $dontDelete]], [':id' => $this->id]);
            // ->andWhere(['not in','category',$dontDelete]);
            //  foreach($query as $q){
            // }
        } else {

            // Delete all relations 
            ProductCategoryRef::deleteAll('product=:id', [':id' => $this->id]);
        }
    }

    public function setRelatedProducts($ids = [])
    {
        $this->_related = $ids;
    }

    private function clearRelatedProducts()
    {
        RelatedProduct::deleteAll('product_id=:id', ['id' => $this->id]);
        if (Yii::$app->settings->get('shop', 'product_related_bilateral')) {
            RelatedProduct::deleteAll('related_id=:id', ['id' => $this->id]);
        }
    }

    public function afterSave($insert, $changedAttributes)
    {


        if (true) { //Yii::$app->settings->get('shop', 'auto_add_subcategories')
            // Авто добавление предков категории
            // Нужно выбирать в админки самую последнию категории по уровню.
            $category = Category::findOne($this->main_category_id);
            $categories = [];
            $tes = $category->ancestors()->excludeRoot()->all();
            foreach ($tes as $cat) {
                $categories[] = $cat->id;
            }
            $this->setCategories($categories, $this->main_category_id);
        } else {
            $mainCategoryId = 1;
            if (isset($_POST['Product']['main_category_id']))
                $mainCategoryId = $_POST['Product']['main_category_id'];

            $this->setCategories(Yii::$app->request->post('categories', array()), $mainCategoryId);
        }


        // Process related products
        if ($this->_related !== null) {
            $this->clearRelatedProducts();

            foreach ($this->_related as $id) {
                $related = new RelatedProduct;
                $related->product_id = $this->id;
                $related->related_id = (int)$id;
                if ($related->save()) {
                    //двустороннюю связь между товарами
                    if (Yii::$app->settings->get('shop', 'product_related_bilateral')) {
                        $related = new RelatedProduct;

                        $related->product_id = (int)$id;
                        $related->related_id = $this->id;
                        if (!$related->save()) {
                            throw new \yii\base\Exception('Error save product relation');
                        }
                    }
                }
            }
        }

        // Save configurable attributes
        if ($this->_configurable_attribute_changed === true) {
            // Clear
            Yii::$app->db->createCommand()->delete('{{%shop__product_configurable_attributes}}', 'product_id = :id', array(':id' => $this->id));

            foreach ($this->_configurable_attributes as $attr_id) {
                Yii::$app->db->createCommand()->insert('{{%shop__product_configurable_attributes}}', array(
                    'product_id' => $this->id,
                    'attribute_id' => $attr_id
                ));
            }
        }

        // Process min and max price for configurable product
        if ($this->use_configurations)
            $this->updatePrices($this);
        else {
            // Check if product is configuration

            $query = (new \yii\db\Query())
                ->from('{{%shop__product_configurations}} t')
                ->where(['in', 't.configurable_id', [$this->id]])
                ->all();


            /* $query = Yii::$app->db->createCommand()
              ->from('{{%shop__product_configurations}} t')
              ->where(['in', 't.configurable_id', [$this->id]])
              ->queryAll();
             */
            foreach ($query as $row) {
                $model = Product::findOne($row['product_id']);
                if ($model)
                    $this->updatePrices($model);
            }
        }


        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Update price and max_price for configurable product
     * @param Product $model
     */
    public function updatePrices__(Product $model)
    {
        // Get min and max prices
        $query = Yii::$app->db->createCommand()
            ->select('MIN(t.price) as min_price, MAX(t.price) as max_price')
            ->from('{{%shop__product}} t')
            ->where(array('in', 't.id', $model->getConfigurations(true)))
            ->queryRow();

        // Update
        Yii::$app->db->createCommand()->update('{{%shop__product}}', array(
            'price' => $query['min_price'],
            'max_price' => $query['max_price']
        ), 'id=:id', array(':id' => $model->id));
    }

    public function updatePrices(Product $model)
    {
        $query = (new \yii\db\Query())
            ->select('MIN(t.price) as min_price, MAX(t.price) as max_price')
            ->from('{{%shop__product}} t')
            ->where(['in', 't.id', $model->getConfigurations(true)])
            ->one();


        // Update
        Yii::$app->db->createCommand()->update('{{%shop__product}}', array(
            'price' => $query['min_price'],
            'max_price' => $query['max_price']
        ), 'id=:id', array(':id' => $model->id))->execute();
    }

    /**
     * @return array of product ids
     */
    public function getConfigurations($reload = false)
    {
        if (is_array($this->_configurations) && $reload === false)
            return $this->_configurations;


        $query = (new \yii\db\Query())
            ->select('t.configurable_id')
            ->from('{{%shop__product_configurations}} as t')
            ->where('t.product_id=:id', [':id' => $this->id])
            ->groupBy('t.configurable_id');
        // ->one();
        $this->_configurations = $query->createCommand()->queryColumn();
        /* $this->_configurations = Yii::$app->db->createCommand()
          ->select('t.configurable_id')
          ->from('{{%shop__product_configurations}} t')
          ->where('product_id=:id', array(':id' => $this->id))
          ->group('t.configurable_id')
          ->queryColumn(); */

        return $this->_configurations;
    }

    public function getFrontPrice()
    {
        $currency = Yii::$app->currency;
        if ($this->appliedDiscount) {
            $price = $currency->convert($this->discountPrice, $this->currency_id);
        } else {
            $price = $currency->convert($this->price, $this->currency_id);
        }
        return $price;
    }

    public function priceRange()
    {
        $price = $this->getFrontPrice();
        $max_price = Yii::$app->currency->convert($this->max_price);

        if ($this->use_configurations && $max_price > 0)
            return Yii::$app->currency->number_format($price) . ' - ' . Yii::$app->currency->number_format($max_price);

        return Yii::$app->currency->number_format($price);
    }

    public function afterDelete()
    {
        $this->clearRelatedProducts();
        RelatedProduct::deleteAll('related_id=:id', array('id' => $this->id));

        // Delete categorization
        ProductCategoryRef::deleteAll([
            'product' => $this->id
        ]);


        //  $this->removeImages();
        $image = $this->getImages();

        if ($image) {
            //get path to resized image
            $this->removeImages();
        }
        // Clear configurable attributes
        Yii::$app->db->createCommand()->delete('{{%shop__product_configurable_attributes}}', 'product_id=:id', [':id' => $this->id])->execute();
        // Delete configurations
        Yii::$app->db->createCommand()->delete('{{%shop__product_configurations}}', 'product_id=:id', [':id' => $this->id])->execute();
        Yii::$app->db->createCommand()->delete('{{%shop__product_configurations}}', 'configurable_id=:id', [':id' => $this->id])->execute();
        /* if (Yii::app()->hasModule('wishlist')) {
          Yii::import('mod.wishlist.models.WishlistProducts');
          $wishlistProduct = WishlistProducts::model()->findByAttributes(array('product_id' => $this->id));
          if ($wishlistProduct)
          $wishlistProduct->delete();
          }
          // Delete from comapre if install module "comapre"
          if (Yii::app()->hasModule('comapre')) {
          Yii::import('mod.comapre.components.CompareProducts');
          $comapreProduct = new CompareProducts;
          $comapreProduct->remove($this->id);
          } */
        parent::afterDelete();
    }

    public function setConfigurable_attributes(array $ids)
    {
        $this->_configurable_attributes = $ids;
        $this->_configurable_attribute_changed = true;
    }

    /**
     * @return array
     */
    public function getConfigurable_attributes()
    {
        if ($this->_configurable_attribute_changed === true)
            return $this->_configurable_attributes;

        if ($this->_configurable_attributes === null) {

            $query = new \yii\db\Query;
            $query->select('attribute_id')
                ->from('{{%shop__product_configurable_attributes}}')
                ->where(['product_id' => $this->id])
                ->groupBy('attribute_id');
            $this->_configurable_attributes = $query->createCommand()->queryColumn();
            /*    $this->_configurable_attributes = Yii::app()->db->createCommand()
              ->select('t.attribute_id')
              ->from('{{shop__product_configurable_attributes}} t')
              ->where('t.product_id=:id', array(':id' => $this->id))
              ->group('t.attribute_id')
              ->queryColumn(); */
        }

        return $this->_configurable_attributes;
    }

    // public function getMainImageUrl() {
    //  return $this->getImage()->getUrl('50x50');
    // }

    /*
      // 'related' => array(self::HAS_MANY, 'RelatedProduct', 'product_id'),
      'relatedProducts' => array(self::HAS_MANY, 'Product', array('related_id' => 'id'), 'through' => 'related'),
      //'relatedProductCount' => array(self::STAT, 'RelatedProduct', 'product_id'),
     *  */

    //use EavTrait; // need for full support label of fields
    //public function getEavAttributes() {
    //     return $this->hasMany(mazurva\eav\models\EavAttribute::className(), ['categoryId' => 'id']);
    // }
    public function __get($name)
    {
        if (substr($name, 0, 4) === 'eav_') {
            if ($this->getIsNewRecord())
                return null;

            $attribute = substr($name, 4);

            $eavData = $this->getEavAttributes();

            if (isset($eavData[$attribute]))
                $value = $eavData[$attribute];
            else
                return null;

            $attributeModel = Attribute::find()->where(['name' => $attribute])->one();
            //$attributeModel = Attribute::find(['name' => $attribute])->one();
            return $attributeModel->renderValue($value);
        }
        return parent::__get($name);
    }

    public function behaviors()
    {


        $a = [];

        $a['sitemap'] = [
            'class' => SitemapBehavior::class,
            /**'batchSize' => 100,*/
            'scope' => function ($model) {
                /** @var \yii\db\ActiveQuery $model */
                $model->select(['seo_alias', 'created_at']);
                $model->andWhere(['switch' => 1]);
            },
            'dataClosure' => function ($model) {
                /** @var self $model */
                return [
                    //'loc' => Url::to($model->seo_alias, true),
                    'loc' => Url::to($model->getUrl(), true),
                    //'loc' => $model->getUrl(),
                    'lastmod' => $model->updated_at,
                    'changefreq' => Sitemap::DAILY,
                    'priority' => 0.8
                ];
            }
        ];
        if (Yii::$app->getModule('images'))
            $a['imagesBehavior'] = [
                'class' => '\panix\mod\images\behaviors\ImageBehavior',
            ];
        $a['slug'] = [
            'class' => '\yii\behaviors\SluggableBehavior',
            'attribute' => 'name',
            'slugAttribute' => 'seo_alias',
        ];
        $a['eav'] = [
            'class' => '\panix\mod\shop\components\EavBehavior',
            'tableName' => '{{%shop__product_attribute_eav}}'
        ];
        if (Yii::$app->getModule('seo'))
            $a['seo'] = [
                'class' => '\app\common\modules\seo\components\SeoBehavior',
                'url' => $this->getUrl()
            ];
        $a['translate'] = [
            'class' => TranslateBehavior::class,
            'translationAttributes' => [
                'name',
                'full_description'
            ]
        ];
        if (Yii::$app->getModule('discounts') && Yii::$app->id !== 'console')
            $a['discounts'] = [
                'class' => '\panix\mod\discounts\components\DiscountBehavior'
            ];

        return ArrayHelper::merge($a, parent::behaviors());
    }

    /*public static function formatPrice($price)
    {
        $c = Yii::$app->settings->get('shop');
        return iconv("windows-1251", "UTF-8", number_format($price, $c->price_penny, $c->price_thousand, $c->price_decimal));
    }*/

    /**
     * Replaces comma to dot
     * @param $attr
     */
    public function commaToDot($attr)
    {
        $this->$attr = str_replace(',', '.', $this->$attr);
    }

    public function getPriceByQuantity($q = 1)
    {

        return false;///delete
        $cr = new CDbCriteria();
        $cr->condition = '`t`.`product_id`=:pid AND `t`.`order_from` <= :from';
        $cr->params = array(
            ':pid' => $this->id,
            ':from' => $q
        );
        $cr->order = 'order_from DESC';
        return ShopProductPrices::model()->find($cr);
    }

    public static function calculatePrices($product, array $variants, $configuration, $quantity = 1)
    {
        if (($product instanceof Product) === false)
            $product = Product::findOne($product);

        if (($configuration instanceof Product) === false && $configuration > 0)
            $configuration = Product::findOne($configuration);

        if ($configuration instanceof Product) {
            $result = $configuration->price;
        } else {
            // if ($quantity > 1 && ($pr = $product->getPriceByQuantity($quantity))) {
            //$calcPrice = $item['model']->value;
            //   $result = $pr->value;
            //} else {
            $result = $product->appliedDiscount ? $product->discountPrice : $product->price;
            // }
        }

        // if $variants contains not models
        if (!empty($variants) && ($variants[0] instanceof ProductVariant) === false)
            $variants = ProductVariant::findAll($variants);

        foreach ($variants as $variant) {
            // Price is percent
            if ($variant->price_type == 1)
                $result += ($result / 100 * $variant->price);
            else
                $result += $variant->price;
        }

        return $result;
    }

}
