<?php

namespace panix\mod\shop\models;


use panix\mod\sitemap\behaviors\SitemapBehavior;
use panix\mod\user\models\User;
use Yii;
use panix\engine\CMS;
use panix\mod\shop\models\query\ProductQuery;
use panix\mod\shop\models\translate\ProductTranslate;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use panix\engine\db\ActiveRecord;

/**
 * Class Product
 * @property integer $id Product id
 * @property integer $manufacturer_id Manufacturer
 * @property integer $type_id Type
 * @property integer $supplier_id Supplier
 * @property integer $currency_id Currency
 * @property integer $use_configurations
 * @property string $slug
 * @property string $name Product name
 * @property float $price Price
 * @property float $max_price Max price
 * @property float $price_purchase
 * @property boolean $unit Unit
 * @property boolean $sku Product article
 * @property integer $quantity
 * @property integer $availability
 * @property integer $main_category_id
 * @property integer $auto_decrease_quantity
 * @property integer $views Views product on frontend
 * @property integer $created_at
 * @property integer $updated_at
 * @property boolean $switch
 * @property integer $added_to_cart_count
 * @property integer $votes
 * @property integer $rating
 * @property array $manufacturer
 * @property string $discount Discount
 * @property string $video Youtube video URL
 * @property boolean $appliedDiscount See [[\panix\mod\discounts\components\DiscountBehavior]] //Discount
 * @property float $originalPrice See [[\panix\mod\discounts\components\DiscountBehavior]]
 * @property float $discountPrice See [[\panix\mod\discounts\components\DiscountBehavior]]
 * @property integer $ordern
 * @property boolean $isAvailable
 * @property Category $categories
 * @property array $eavAttributes
 * @property Kit $kit
 * @property ProductPrices[] $prices
 * @property ProductVariant[] $variants
 */
class Product extends ActiveRecord
{

    use traits\ProductTrait;

    public $translationClass = ProductTranslate::class;

    const SCENARIO_INSERT = 'insert';

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
    private $_kit;
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
            $result[] = [
                'class' => 'success new',
                'value' => 'Новинка',
                'tooltip' => 'от ' . Yii::$app->formatter->asDate(date('Y-m-d', $this->created_at)) . ' до ' . Yii::$app->formatter->asDate(date('Y-m-d', $this->created_at + (86400 * $new)))
            ];
        }


        if (isset($this->appliedDiscount)) {
            $result[] = [
                'class' => 'danger discount',
                'value' => '-' . $this->discountSum,
                'tooltip' => '-' . $this->discountSum . ' до ' . $this->discountEndDate
            ];
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
                'price' => [
                    'asc' => ['price' => SORT_ASC],
                    'desc' => ['price' => SORT_DESC],
                    //'default' => SORT_ASC,
                    //'label' => 'Цена1',
                ],
                'sku' => [
                    'asc' => ['sku' => SORT_ASC],
                    'desc' => ['sku' => SORT_DESC],
                ],
                'created_at' => [
                    'asc' => ['created_at' => SORT_ASC],
                    'desc' => ['created_at' => SORT_DESC],
                    'label' => 'по дате добавления'
                ],
                'name' => [
                    'default' => SORT_ASC,
                    'asc' => ['translation.name' => SORT_ASC],
                    'desc' => ['translation.name' => SORT_DESC],
                ],
                'commentsCount',
            ],
        ]);
    }


    public function getMainImage($size = false)
    {
        /** @var $image \panix\mod\images\behaviors\ImageBehavior|\panix\mod\images\models\Image */
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

    /**
     * @param string $size Default value 50x50.
     * @return string
     */
    public function renderGridImage($size = '50x50')
    {
        $small = $this->getMainImage($size);
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
        return ['/shop/product/view', 'slug' => $this->slug];
    }

    /* public function transactions() {
      return [
      self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
      ];
      } */


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
     * @param string $img default, hqdefault, mqdefault, sddefault, maxresdefault OR 0,1,2,3
     * @return string
     */
    public function getVideoPreview($img = 'default')
    {
        return "https://img.youtube.com/vi/" . CMS::parse_yturl($this->video) . "/{$img}.jpg";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['price', 'commaToDot'],
            [['file'], 'file', 'maxFiles' => 10],
            [['name', 'slug', 'video'], 'string', 'max' => 255],
            ['video', 'url'],
            [['image'], 'image'],
            ['slug', '\panix\engine\validators\UrlValidator', 'attributeCompare' => 'name'],
            ['slug', 'match',
                'pattern' => '/^([a-z0-9-])+$/i',
                'message' => Yii::t('app', 'PATTERN_URL')
            ],
            [['name', 'slug'], 'trim'],
            [['full_description', 'discount'], 'string'],
            ['use_configurations', 'boolean', 'on' => self::SCENARIO_INSERT],
            ['enable_comments', 'boolean'],
            [['sku', 'full_description', 'unit', 'video'], 'default'], // установим ... как NULL, если они пустые
            [['name', 'slug', 'main_category_id', 'price', 'unit'], 'required'],
            ['price', 'double'],
            [['manufacturer_id', 'type_id', 'quantity', 'views', 'availability', 'added_to_cart_count', 'ordern', 'category_id', 'currency_id', 'unit', 'supplier_id'], 'integer'],
            [['name', 'slug', 'full_description', 'use_configurations'], 'safe'],
            //  [['c1'], 'required'], // Attribute field
            // [['c1'], 'string', 'max' => 255], // Attribute field
        ];
    }

    public function getUnits()
    {
        return [
            1 => self::t('UNIT_THING'),
            2 => self::t('UNIT_METER'),
            3 => self::t('UNIT_BOX'),
        ];
    }

    public function processVariants()
    {
        $result = [];
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
    public function getKit()
    {
        return $this->hasMany(Kit::class, ['owner_id' => 'id']);
    }


    public function getManufacturer()
    {
        return $this->hasOne(Manufacturer::class, ['id' => 'manufacturer_id'])
            ->cache(3600 * 24, new TagDependency(['tags' => 'product-manufacturer-' . $this->manufacturer_id]));
    }

    public function getType()
    {
        return $this->hasOne(ProductType::class, ['id' => 'type_id'])
            ->cache(3600 * 24, new TagDependency(['tags' => 'product-type-' . $this->type_id]));
    }

    public function getType2()
    {
        return $this->hasOne(ProductType::class, ['type_id' => 'id']);
    }

    public function getRelated()
    {
        return $this->hasMany(RelatedProduct::class, ['related_id' => 'id']);
    }

    public function getRelatedProductCount()
    {
        return $this->hasMany(RelatedProduct::class, ['product_id' => 'id'])->count();
    }

    public function getRelatedProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])
            ->viaTable(RelatedProduct::tableName(), ['related_id' => 'id']);
    }


    public function getKitProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])
            ->viaTable(Kit::tableName(), ['owner_id' => 'id']);
    }

    public function getCategorization()
    {
        return $this->hasMany(ProductCategoryRef::class, ['product' => 'id']);
    }

    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category'])->via('categorization');
    }

    public function getPrices()
    {
        return $this->hasMany(ProductPrices::class, ['product_id' => 'id']);
    }

    public function getMainCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category'])
            ->via('categorization', function ($query) {
                /** @var Query $query */
                $query->where(['is_main' => 1]);
            });
    }

    public function getVariants()
    {
        return $this->hasMany(ProductVariant::class, ['product_id' => 'id'])
            ->joinWith(['productAttribute', 'option'])
            ->orderBy(AttributeOption::tableName() . '.ordern');
    }

//'variants' => array(self::HAS_MANY, 'ProductVariant', array('product_id'), 'with' => array('attribute', 'option'), 'order' => 'option.ordern'),

    /**
     * @param array $prices
     */
    public function processPrices(array $prices)
    {
        $dontDelete = [];

        foreach ($prices as $index => $price) {
            if ($price['value'] > 0) {

                $record = ProductPrices::find()->where(array(
                    'id' => $index,
                    'product_id' => $this->id,
                ))->one();

                if (!$record) {
                    $record = new ProductPrices;
                }
                $record->from = $price['from'];
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
        $dontDelete = [];

        if (!Category::find()->where(['id' => $main_category])->count())
            $main_category = 1;

        if (!in_array($main_category, $categories))
            array_push($categories, $main_category);


        foreach ($categories as $c) {

            $count = ProductCategoryRef::find()->where([
                'category' => $c,
                'product' => $this->id,
            ])->count();


            if ($count == 0) {
                $record = new ProductCategoryRef;
                $record->category = (int)$c;
                $record->product = $this->id;
                if ($this->scenario == 'duplicate') {
                    $record->switch = 1;
                } else {
                    $record->switch = ($this->switch) ? $this->switch : 1;
                }
                $record->save(false);
            }

            $dontDelete[] = $c;
        }

        // Clear main category
        ProductCategoryRef::updateAll([
            'is_main' => 0,
            'switch' => $this->switch
        ], 'product=:p', [':p' => $this->id]);

        // Set main category
        ProductCategoryRef::updateAll([
            'is_main' => 1,
            'switch' => $this->switch,
        ], 'product=:p AND category=:c', [':p' => $this->id, ':c' => $main_category]);

        // Delete not used relations
        if (sizeof($dontDelete) > 0) {
            // $cr = new CDbCriteria;
            // $cr->addNotInCondition('category', $dontDelete);
            //    $query = ShopProductCategoryRef::deleteAll(['product=:id','category NOT IN (:cats)'],[':id'=>$this->id,':cats'=>implode(',',$dontDelete)]);
            ProductCategoryRef::deleteAll(
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
        RelatedProduct::deleteAll(['product_id' => $this->id]);
        if (Yii::$app->settings->get('shop', 'product_related_bilateral')) {
            RelatedProduct::deleteAll(['related_id' => $this->id]);
        }
    }

    public function setKitProducts($ids = [])
    {
        $this->_kit = $ids;
    }

    private function clearKitProducts()
    {
        Kit::deleteAll(['owner_id' => $this->id]);

    }

    public function afterSave($insert, $changedAttributes)
    {


        if (true) { //Yii::$app->settings->get('shop', 'auto_add_subcategories')
            // Авто добавление в предков категории
            // Нужно выбирать в админки самую последнию категории по уровню.
            $category = Category::findOne($this->main_category_id);
            $categories = [];
            if ($category) {
                $tes = $category->ancestors()->excludeRoot()->all();
                foreach ($tes as $cat) {
                    $categories[] = $cat->id;
                }
            }
            $this->setCategories($categories, $this->main_category_id);
        } else {
            $mainCategoryId = 1;
            if (isset($_POST['Product']['main_category_id']))
                $mainCategoryId = $_POST['Product']['main_category_id'];

            $this->setCategories(Yii::$app->request->post('categories', []), $mainCategoryId);
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

        if ($this->_kit !== null) {
            $this->clearKitProducts();

            foreach ($this->_kit as $id) {
                $kit = new Kit;
                $kit->owner_id = $this->id;
                $kit->product_id = (int)$id;
                $kit->save();
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

            $query = (new Query())
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
        $query = (new Query())
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


        $query = (new Query())
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
        RelatedProduct::deleteAll(['related_id' => $this->id]);

        $this->clearKitProducts();
        Kit::deleteAll(['owner_id' => $this->id]);

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
        Yii::$app->db->createCommand()->delete('{{%shop__product_configurable_attributes}}', ['product_id' => $this->id])->execute();
        // Delete configurations
        Yii::$app->db->createCommand()->delete('{{%shop__product_configurations}}', ['product_id' => $this->id])->execute();
        Yii::$app->db->createCommand()->delete('{{%shop__product_configurations}}', ['configurable_id' => $this->id])->execute();
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

            $query = new Query;
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

            $table = Attribute::tableName();
            $dependency = new DbDependency();
            $dependency->sql = "SELECT MAX(updated_at) FROM {$table}";


            if ($this->getIsNewRecord())
                return null;

            $attribute = substr($name, 4);

            $eavData = $this->getEavAttributes();

            if (isset($eavData[$attribute]))
                $value = $eavData[$attribute];
            else
                return null;

            $attributeModel = Attribute::find()->where(['name' => $attribute])->cache(3600 * 24, $dependency)->one();
            return $attributeModel->renderValue($value);
        }
        return parent::__get($name);
    }

    public function behaviors()
    {


        $a = [];

        $a['sitemap'] = [
            'class' => SitemapBehavior::class,
            //'batchSize' => 100,
            'scope' => function ($model) {
                /** @var \yii\db\ActiveQuery $model */
                $model->select(['slug', 'updated_at']);
                $model->andWhere(['switch' => 1]);
            },
            'dataClosure' => function ($model) {
                /** @var self $model */
                return [
                    //'loc' => Url::to($model->slug, true),
                    'loc' => Url::to($model->getUrl(), true),
                    'lastmod' => $model->updated_at,
                    'changefreq' => SitemapBehavior::CHANGEFREQ_DAILY,
                    'priority' => 0.9
                ];
            }
        ];
        // if (Yii::$app->getModule('images'))
        $a['imagesBehavior'] = [
            'class' => '\panix\mod\images\behaviors\ImageBehavior',
        ];
        $a['slug'] = [
            'class' => '\yii\behaviors\SluggableBehavior',
            'attribute' => 'name',
            'slugAttribute' => 'slug',
        ];
        $a['eav'] = [
            'class' => '\panix\mod\shop\components\EavBehavior',
            'tableName' => '{{%shop__product_attribute_eav}}'
        ];
        if (Yii::$app->getModule('seo'))
            $a['seo'] = [
                'class' => '\panix\mod\seo\components\SeoBehavior',
                'url' => $this->getUrl()
            ];

        if (Yii::$app->getModule('comments')) {
            $a['comments'] = [
                'class' => '\panix\mod\comments\components\CommentBehavior',
                //'handlerClass' => static::class,
                'owner_title' => 'name', // Attribute name to present comment owner in admin panel
            ];
        }
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
        if ($q >= 1) {
            return ProductPrices::find()
                ->where(['product_id' => $this->id, 'from' => $q])
                ->orderBy(['from' => SORT_DESC])
                ->one();
        }
    }

    /**
     * @param $product Product
     * @param array $variants
     * @param $configuration
     * @param int $quantity
     * @return float|int|mixed|null
     */
    public static function calculatePrices($product, array $variants, $configuration, $quantity = 1)
    {
        // print_r($product);die;
        if (($product instanceof Product) === false)
            $product = Product::findOne($product);

        if (($configuration instanceof Product) === false && $configuration > 0)
            $configuration = Product::findOne($configuration);

        if ($configuration instanceof Product) {
            $result = $configuration->price;
        } else {

            if ($quantity > 1 && ($pr = $product->getPriceByQuantity($quantity))) {
                if ($product->currency_id) {
                    $result = Yii::$app->currency->convert($pr->value, $product->currency_id);
                } else {
                    $result = $pr->value;
                }
            } else {
                if ($product->currency_id) {
                    $result = Yii::$app->currency->convert($product->appliedDiscount ? $product->discountPrice : $product->price, $product->currency_id);
                } else {
                    $result = Yii::$app->currency->convert($product->appliedDiscount ? $product->discountPrice : $product->price, $product->currency_id);
                    //$result = ($product->appliedDiscount) ? $product->discountPrice : $product->price;
                }

            }
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
