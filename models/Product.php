<?php

namespace panix\mod\shop\models;


use panix\mod\discounts\components\DiscountBehavior;
use panix\mod\images\models\Image;
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
 * @property Currency $currency
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
 * @property integer $label
 * @property integer $main_category_id
 * @property integer $auto_decrease_quantity
 * @property integer $views Views product on frontend
 * @property integer $created_at
 * @property integer $updated_at
 * @property boolean $switch
 * @property integer $added_to_cart_count
 * @property integer $votes
 * @property integer $rating
 * @property Manufacturer[] $manufacturer
 * @property Supplier[] $supplier
 * @property string $discount Discount
 * @property string $video Youtube video URL
 * @property boolean $hasDiscount See [[\panix\mod\discounts\components\DiscountBehavior]] //Discount
 * @property float $originalPrice See [[\panix\mod\discounts\components\DiscountBehavior]]
 * @property float $discountPrice See [[\panix\mod\discounts\components\DiscountBehavior]]
 * @property integer $ordern
 * @property boolean $isAvailable
 * @property Category $categories
 * @property array $eavAttributes
 * @property \panix\mod\comments\models\Comments $commentsCount
 * @property Kit $kit
 * @property ProductPrices[] $prices
 * @property ProductVariant[] $variants
 * @property ProductType $type
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
        /** @var DiscountBehavior|self $this */

        $labelsList['new'] = [
            'class' => 'success',
            'value' => self::t('LABEL_NEW')
        ];
        $labelsList['top_sale'] = [
            'class' => 'success',
            'value' => self::t('LABEL_TOP_SALE')
        ];
        $labelsList['sale'] = [
            'class' => 'primary',
            'value' => self::t('LABEL_SALE')
        ];
        $labelsList['discount'] = [
            'class' => 'danger',
            'value' => self::t('LABEL_DISCOUNT')
        ];

        $result = [];
        $new = Yii::$app->settings->get('shop', 'label_expire_new');
        if ($this->label == 1) {
            $result['new'] = $labelsList['new'];
        } elseif ($this->label == 2) {
            $result['sale'] = $labelsList['sale'];
        } elseif ($this->label == 3) {
            $result['discount'] = $labelsList['discount'];
        } elseif ($this->label == 4) {

        }
        if ($new) {
            if ((time() - 86400 * $new) <= $this->created_at) {
                $result['new'] = [
                    'class' => 'success',
                    'value' => self::t('LABEL_NEW'),
                    // 'title' => Yii::t('app/default', 'FROM_BY', Yii::$app->formatter->asDate(date('Y-m-d', $this->created_at))) . ' ' . Yii::t('app/default', 'TO_BY', Yii::$app->formatter->asDate(date('Y-m-d', $this->created_at + (86400 * $new))))
                ];
            }
        }

        if (isset($this->hasDiscount)) {
            $result['discount']['class'] = 'danger';
            $result['discount']['value'] = '-' . $this->discountSum;
            if ($this->discountEndDate) {
                $result['discount']['title'] = '-' . $this->discountSum . ' до ' . $this->discountEndDate;
            }
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

    public static function labelsList()
    {
        return [
            1 => self::t('LABEL_NEW'),
            2 => self::t('LABEL_SALE'),
            3 => self::t('LABEL_DISCOUNT')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        $rules = [];


        $auto = false;
        if ($this->isNewRecord && isset(Yii::$app->request->get('Product')['type_id'])) {
            $type = ProductType::findOne(Yii::$app->request->get('Product')['type_id']);
            if ($type && $type->product_name)
                $auto = true;
        }


        if (!$auto) {
            $rules[] = ['slug', '\panix\engine\validators\UrlValidator', 'attributeCompare' => 'name'];
            $rules[] = ['slug', 'match',
                'pattern' => '/^([a-z0-9-])+$/i',
                'message' => Yii::t('app/default', 'PATTERN_URL')
            ];
            $rules[] = [['name', 'slug'], 'required'];
        }
        $rules[] = [['main_category_id', 'price', 'unit'], 'required'];
        $rules[] = [['slug'], 'unique'];
        $rules[] = ['price', 'commaToDot'];
        $rules[] = [['file'], 'file', 'maxFiles' => Yii::$app->params['plan'][Yii::$app->params['plan_id']]['product_upload_files']];
        $rules[] = [['file'], 'validateLimit'];
        $rules[] = [['name', 'slug', 'video'], 'string', 'max' => 255];
        $rules[] = ['video', 'url'];
        $rules[] = [['image'], 'image'];

        $rules[] = [['name', 'slug'], 'trim'];
        $rules[] = [['full_description'], 'string'];
        $rules[] = ['use_configurations', 'boolean', 'on' => self::SCENARIO_INSERT];
        $rules[] = ['enable_comments', 'boolean'];
		$rules[] = [['unit'], 'default', 'value' => 1];
        $rules[] = [['sku', 'full_description', 'video', 'price_purchase', 'label', 'discount'], 'default']; // установим ... как NULL, если они пустые
        $rules[] = [['price', 'price_purchase'], 'double'];
        $rules[] = [['manufacturer_id', 'type_id', 'quantity', 'views', 'availability', 'added_to_cart_count', 'ordern', 'category_id', 'currency_id', 'supplier_id', 'label'], 'integer'];
        $rules[] = [['name', 'slug', 'full_description', 'use_configurations'], 'safe'];

        return $rules;
    }

    public function validateLimit($attribute)
    {
        $planCount = Yii::$app->params['plan'][Yii::$app->params['plan_id']]['product_upload_files'];
        $imageCount = Image::find()->where([
            'object_id' => $this->primaryKey,
            'handler_hash' => $this->getHash()
        ])->count();


        $files = $_FILES[(new \ReflectionClass($this))->getShortName()];

        if (isset($files['name'])) {
            if (!empty($files['name']['file'][0])) {
                $imageCount += count($files['name']['file']);
            }
        }

        if (($imageCount > $planCount)) {
            $this->addError($attribute, Yii::t('app/default', 'Привышен лимит изображений, доступно всего {0}', $planCount));
        }
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
            ->cache(3200, new DbDependency(['sql' => 'SELECT MAX(`updated_at`) FROM ' . Manufacturer::tableName()]));
    }

    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'supplier_id'])
            ->cache(3200, new DbDependency(['sql' => 'SELECT MAX(`updated_at`) FROM ' . Supplier::tableName()]));
    }

    public function getType()
    {
        return $this->hasOne(ProductType::class, ['id' => 'type_id']);

    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);

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
        $notDelete = [];


        if (!Category::find()->where(['id' => $main_category])->count())
            $main_category = 1;

        if (!in_array($main_category, $categories))
            array_push($categories, $main_category);


        foreach ($categories as $category) {

            $count = ProductCategoryRef::find()->where([
                'category' => (int)$category,
                'product' => $this->id,
            ])->count();


            if (!$count) {
                $record = new ProductCategoryRef;
                $record->category = (int)$category;
                $record->product = $this->id;
                if ($this->scenario == 'duplicate') {
                    $record->switch = 1;
                } else {
                    $record->switch = ($this->switch) ? $this->switch : 1;
                }
                $record->save(false);
            }

            $notDelete[] = (int)$category;
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
        if (count($notDelete) > 0) {

            ProductCategoryRef::deleteAll(
                ['AND', 'product=:id', ['NOT IN', 'category', $notDelete]], [':id' => $this->id]);

        } else {
            // Delete all relations
            ProductCategoryRef::deleteAll(['product' => $this->id]);
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

    public $auto = false;

    public function init()
    {
        if (Yii::$app->id != 'console') {
            if ($this->isNewRecord && isset(Yii::$app->request->get('Product')['type_id'])) {
                $type = ProductType::findOne(Yii::$app->request->get('Product')['type_id']);
                if ($type && $type->product_name)
                    $this->auto = true;
            }
        }
        parent::init();
    }

    public function afterSave($insert, $changedAttributes)
    {

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

        if ($this->auto) {
            $this->name = $this->replaceName();
            $this->slug = CMS::slug($this->name);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Update price and max_price for configurable product
     * @param Product $model
     */
    public function updatePrices(Product $model)
    {
        $query = (new Query())
            ->select('MIN(price) as min_price, MAX(price) as max_price')
            ->from(self::tableName())
            ->where(['in', 'id', $model->getConfigurations(true)])
            ->one();
        /*$query = (new Query())
            ->select('MIN(t.price) as min_price, MAX(t.price) as max_price')
            ->from('{{%shop__product}} t')
            ->where(['in', 't.id', $model->getConfigurations(true)])
            ->one();*/

        // Update
        Yii::$app->db->createCommand()->update(self::tableName(), [
            'price' => $query['min_price'],
            'max_price' => $query['max_price']
        ], 'id=:id', [':id' => $model->id])->execute();
    }

    /**
     * @param boolean $reload
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
        if ($this->hasDiscount) {
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


		$this->removeImages();

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
		  

        if (Yii::$app->hasModule('wishlist')) {
            Yii::$app->db->createCommand()->delete(\panix\mod\wishlist\models\WishListProducts::tableName(), ['product_id' => $this->id])->execute();
        }
		  
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

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (substr($name, 0, 4) === 'eav_') {

            $table = Attribute::tableName();
            $dependency = new DbDependency();
            $dependency->sql = "SELECT MAX(updated_at) FROM {$table}";


            if ($this->getIsNewRecord())
                return null;

            $attribute = substr($name, 4);
            /** @var \panix\mod\shop\components\EavBehavior $this */
            $eavData = $this->getEavAttributes();

            if (isset($eavData[$attribute]))
                $value = $eavData[$attribute];
            else
                return null;


            $attributeModel = Attribute::getDb()->cache(function ($db) use ($attribute) {
                $q = Attribute::find()->where(['name' => $attribute]);

                $result = $q->one();
                return $result;
            });


            //$attributeModel = Attribute::find()->where(['name' => $attribute])->cache(3600 * 24, $dependency)->one();
            return $attributeModel->renderValue($value);
        }
        return parent::__get($name);
    }

    public function getEavList()
    {

    }

    public function behaviors()
    {
        $a = [];
        if (Yii::$app->getModule('sitemap')) {
            $a['sitemap'] = [
                'class' => SitemapBehavior::class,
                //'batchSize' => 100,
                'scope' => function ($model) {
                    /** @var \yii\db\ActiveQuery $model */
                    $model->select(['slug', 'updated_at']);
                    $model->where(['switch' => 1]);
                },
                'dataClosure' => function ($model) {
                    /** @var self $model */
                    return [
                        'loc' => $model->getUrl(),
                        'lastmod' => $model->updated_at,
                        'changefreq' => SitemapBehavior::CHANGEFREQ_DAILY,
                        'priority' => 0.9
                    ];
                }
            ];
        }
        // if (Yii::$app->getModule('images'))
        $a['imagesBehavior'] = [
            'class' => '\panix\mod\images\behaviors\ImageBehavior',
            'path' => '@uploads/store/product'
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
        return ProductPrices::find()
            ->where(['product_id' => $this->id])
            ->andWhere(['<=', 'from', $q])
            ->orderBy(['from' => SORT_DESC])
            ->one();
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

            // if ($quantity > 1 && ($pr = $product->getPriceByQuantity($quantity))) {
            if ($product->prices && $quantity > 1) {
                $pr = $product->getPriceByQuantity($quantity);
                $result = $pr->value;
                // if ($product->currency_id) {
                //$result = Yii::$app->currency->convert($pr->value, $product->currency_id);
                //} else {
                //     $result = $pr->value;
                //}
            } else {
                if ($product->currency_id) {
                    $result = Yii::$app->currency->convert($product->hasDiscount ? $product->discountPrice : $product->price, $product->currency_id);
                } else {
                    $result = Yii::$app->currency->convert($product->hasDiscount ? $product->discountPrice : $product->price, $product->currency_id);
                    //$result = ($product->hasDiscount) ? $product->discountPrice : $product->price;
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
