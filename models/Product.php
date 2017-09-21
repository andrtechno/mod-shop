<?php

namespace panix\mod\shop\models;

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


class Product extends \panix\engine\db\ActiveRecord {

    use traits\ProductTrait;
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
    public $main_category_id;

    const route = '/admin/shop/default';
    const MODULE_ID = 'shop';



    public static function find() {
        return new ProductQuery(get_called_class());
    }

    public function getIsAvailable() {
        return $this->availability == 1;
    }

    public function beginCartForm() {
        $html = '';
        $html .= Html::beginForm(['/cart/add'], 'post', ['id' => 'form-add-cart-' . $this->id]);
        $html .= Html::hiddenInput('product_id', $this->id);
        $html .= Html::hiddenInput('product_price', $this->price);
        $html .= Html::hiddenInput('use_configurations', $this->use_configurations);
        $html .= Html::hiddenInput('configurable_id', 0);
        return $html;
    }

    public static function getSort() {
        $sort = new \yii\data\Sort([
            'attributes' => [
                'date_create',
                'views',
                'added_to_cart_count',
                'price' => [
                    'asc' => ['price' => SORT_ASC],
                    'desc' => ['price' => SORT_DESC],
                ],
                'name' => [
                    'asc' => ['name' => SORT_ASC],
                    'desc' => ['name' => SORT_DESC],
                ],
            ],
        ]);
        return $sort;
    }

    public function getMainImageTitle() {
        if ($this->getImage())
            return ($this->getImage()->name) ? $this->getImage()->name : $this->name;
    }

    public function getMainImageUrl($size = false) {
        if ($this->getImage()) {

            if ($size) {
                return $this->getImage()->getUrl($size);
            } else {
                return $this->getImage()->getUrl();
            }
        } else {
            return CMS::placeholderUrl(array('size' => $size));
        }
    }

    public function renderGridImage() {
        return ($this->getImage()) ? Html::a(Html::img($this->getMainImageUrl("50x50"), ['alt' => $this->getMainImageTitle(), 'class' => 'img-thumbnail']), $this->getMainImageUrl(), ['title' => $this->name, 'data-fancybox' => 'gallery']) : Html::img($this->getMainImageUrl("50x50"), ['alt' => $this->getMainImageTitle(), 'class' => 'img-thumbnail']);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%shop_product}}';
    }

    public function getUrl() {
        return ['/shop/default/view', 'seo_alias' => $this->seo_alias];
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
        return $scenarios;
    }
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['file'], 'file', 'maxFiles' => 10],
            [['origin_name'], 'string', 'max' => 255],
            [['image'], 'image'],
            [['name', 'seo_alias'], 'trim'],
            [['full_description'], 'string'],
            ['use_configurations', 'boolean', 'on' => self::SCENARIO_INSERT],
            [['sku', 'full_description'], 'default'], // установим ... как NULL, если они пустые
            [['name', 'seo_alias', 'main_category_id'], 'required'],
            [['name', 'seo_alias'], 'string', 'max' => 255],
            [['manufacturer_id', 'type_id', 'quantity', 'views', 'added_to_cart_count', 'ordern', 'category_id'], 'integer'],
            [['name', 'seo_alias', 'full_description','use_configurations'], 'safe'],
                //  [['c1'], 'required'], // Attribute field
                // [['c1'], 'string', 'max' => 255], // Attribute field
        ];
    }
    public function processVariants() {
        $result = array();
        foreach ($this->variants as $v) {
            //print_r($v);die;
            $result[$v->productAttribute->id]['attribute'] = $v->productAttribute;
            $result[$v->productAttribute->id]['options'][] = $v;
        };
        return $result;
    }
    public function beforeValidate() {
        // For configurable product set 0 price
        if ($this->use_configurations)
            $this->price = 0;

        return parent::beforeValidate();
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /* public function getCategory2() {
      return $this->hasOne(Category::className(), ['id' => 'category_id']);
      } */

    public function getManufacturer() {
        return $this->hasOne(Manufacturer::className(), ['id' => 'manufacturer_id']);
    }

    public function getType() {
        return $this->hasOne(ProductType::className(), ['id' => 'type_id']);
    }

    public function getType2() {
        return $this->hasOne(ProductType::className(), ['type_id' => 'id']);
    }

    public function getTranslations() {
        return $this->hasMany(ProductTranslate::className(), ['object_id' => 'id']);
    }

    public function getRelated() {
        return $this->hasMany(RelatedProduct::className(), ['related_id' => 'id']);
    }

    public function getRelatedProductCount() {
        return $this->hasMany(RelatedProduct::className(), ['product_id' => 'id'])->count();
    }

    public function getRelatedProducts() {
        return $this->hasMany(Product::className(), ['id' => 'product_id'])
                        ->viaTable(RelatedProduct::tableName(), ['related_id' => 'id']);
    }

    public function getCategorization() {
        return $this->hasMany(ProductCategoryRef::className(), ['product' => 'id']);
    }

    public function getCategories() {
        return $this->hasMany(Category::className(), ['id' => 'category'])->via('categorization');
    }

    public function getMainCategory() {
        return $this->hasOne(Category::className(), ['id' => 'category'])
                        ->via('categorization', function($query) {
                            $query->where(['is_main' => 1]);
                        });
    }

    public function getVariants() {
        return $this->hasMany(ProductVariant::className(), ['product_id' => 'id'])
                        ->joinWith(['productAttribute', 'option'])
                        ->orderBy('{{%shop_attribute_option}}.ordern');
    }

//'variants' => array(self::HAS_MANY, 'ProductVariant', array('product_id'), 'with' => array('attribute', 'option'), 'order' => 'option.ordern'),

    /**
     * Set product categories and main category
     * @param array $categories ids.
     * @param integer $main_category Main category id.
     */
    public function setCategories(array $categories, $main_category) {
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
                $record->category = (int) $c;
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
                            ['AND',
                        'product=:id',
                        ['NOT IN', 'category', $dontDelete]
                            ], [':id' => $this->id]);
            // ->andWhere(['not in','category',$dontDelete]);
            //  foreach($query as $q){
            // }
        } else {

            // Delete all relations 
            ProductCategoryRef::deleteAll('product=:id', [':id' => $this->id]);
        }
    }

    public function setRelatedProducts($ids = []) {
        $this->_related = $ids;
    }

    private function clearRelatedProducts() {
        RelatedProduct::deleteAll('product_id=:id', ['id' => $this->id]);
        if (Yii::$app->settings->get('shop', 'product_related_bilateral')) {
            RelatedProduct::deleteAll('related_id=:id', ['id' => $this->id]);
        }
    }

    public function afterSave($insert, $changedAttributes) {
        // Process related products
        if ($this->_related !== null) {
            $this->clearRelatedProducts();

            foreach ($this->_related as $id) {
                $related = new RelatedProduct;
                $related->product_id = $this->id;
                $related->related_id = (int) $id;
                if ($related->save()) {
                    //двустороннюю связь между товарами
                    if (Yii::$app->settings->get('shop', 'product_related_bilateral')) {
                        $related = new RelatedProduct;

                        $related->product_id = (int) $id;
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
            Yii::$app->db->createCommand()->delete('{{%shop_product_configurable_attributes}}', 'product_id = :id', array(':id' => $this->id));

            foreach ($this->_configurable_attributes as $attr_id) {
                Yii::$app->db->createCommand()->insert('{{%shop_product_configurable_attributes}}', array(
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
                    ->from('{{%shop_product_configurations}} t')
                    ->where(['in', 't.configurable_id', [$this->id]])
                    ->all();



            /* $query = Yii::$app->db->createCommand()
              ->from('{{%shop_product_configurations}} t')
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
    public function updatePrices__(Product $model) {
        // Get min and max prices
        $query = Yii::$app->db->createCommand()
                ->select('MIN(t.price) as min_price, MAX(t.price) as max_price')
                ->from('{{%shop_product}} t')
                ->where(array('in', 't.id', $model->getConfigurations(true)))
                ->queryRow();

        // Update
        Yii::$app->db->createCommand()->update('{{%shop_product}}', array(
            'price' => $query['min_price'],
            'max_price' => $query['max_price']
                ), 'id=:id', array(':id' => $model->id));
    }

    public function updatePrices(Product $model) {
        $query = (new \yii\db\Query())
                ->select('MIN(t.price) as min_price, MAX(t.price) as max_price')
                ->from('{{%shop_product}} t')
                ->where(['in', 't.id', $model->getConfigurations(true)])
                ->one();




        // Update
        Yii::$app->db->createCommand()->update('{{%shop_product}}', array(
            'price' => $query['min_price'],
            'max_price' => $query['max_price']
                ), 'id=:id', array(':id' => $model->id))->execute();
    }
    /**
     * @return array of product ids
     */
    public function getConfigurations($reload = false) {
        if (is_array($this->_configurations) && $reload === false)
            return $this->_configurations;

        
        $query = (new \yii\db\Query())
                ->select('t.configurable_id')
                ->from('{{%shop_product_configurations}} as t')
                ->where('t.product_id=:id', [':id' => $this->id])
                ->groupBy('t.configurable_id');
               // ->one();
          $this->_configurations = $query->createCommand()->queryColumn();
       /* $this->_configurations = Yii::$app->db->createCommand()
                ->select('t.configurable_id')
                ->from('{{%shop_product_configurations}} t')
                ->where('product_id=:id', array(':id' => $this->id))
                ->group('t.configurable_id')
                ->queryColumn();*/

        return $this->_configurations;
    }
    public function getDisplayPrice($currency_id = null) {
        $currency = Yii::$app->currency;
        if ($this->appliedDiscount) {
            $price = $currency->convert($this->originalPrice, $currency_id);
        } else {
            $price = $currency->convert($this->price, $currency_id);
        }
        return $price;
    }

    public function priceRange() {
        $price = $this->getDisplayPrice();
        $max_price = Yii::$app->currency->convert($this->max_price);

        if ($this->use_configurations && $max_price > 0)
            return self::formatPrice($price) . ' - ' . self::formatPrice($max_price);

        return self::formatPrice($price);
    }

    public function afterDelete() {
        $this->clearRelatedProducts();
        RelatedProduct::deleteAll('related_id=:id', array('id' => $this->id));

        // Delete categorization
        ProductCategoryRef::find()->deleteAll([
            'product' => $this->id
        ]);


        //  $this->removeImages();
        $image = $this->getImages();

        if ($image) {
            //get path to resized image
            $this->removeImages();
        }
        // Clear configurable attributes
        Yii::$app->db->createCommand()->delete('{{%shop_product_configurable_attributes}}', 'product_id=:id',[':id' => $this->id])->execute();
        // Delete configurations
        Yii::$app->db->createCommand()->delete('{{%shop_product_configurations}}', 'product_id=:id', [':id' => $this->id])->execute();
        Yii::$app->db->createCommand()->delete('{{%shop_product_configurations}}', 'configurable_id=:id', [':id' => $this->id])->execute();
        /*if (Yii::app()->hasModule('wishlist')) {
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
        }*/
        parent::afterDelete();
    }

    public function setConfigurable_attributes(array $ids) {
        $this->_configurable_attributes = $ids;
        $this->_configurable_attribute_changed = true;
    }

    /**
     * @return array
     */
    public function getConfigurable_attributes() {
        if ($this->_configurable_attribute_changed === true)
            return $this->_configurable_attributes;

        if ($this->_configurable_attributes === null) {

            $query = new \yii\db\Query;
            $query->select('attribute_id')
                    ->from('{{%shop_product_configurable_attributes}}')
                    ->where(['product_id' => $this->id])
                    ->groupBy('attribute_id');
            $this->_configurable_attributes = $query->createCommand()->queryColumn();
            /*    $this->_configurable_attributes = Yii::app()->db->createCommand()
              ->select('t.attribute_id')
              ->from('{{shop_product_configurable_attributes}} t')
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
    public function __get($name) {
        if (substr($name, 0, 4) === 'eav_') {
            if ($this->getIsNewRecord())
                return null;

            $attribute = substr($name, 4);

            $eavData = $this->getEavAttributes();

            if (isset($eavData[$attribute]))
                $value = $eavData[$attribute];
            else
                return null;


            $attributeModel = Attribute::find(['name' => $attribute])->one();
            return $attributeModel->renderValue($value);
        }
        return parent::__get($name);
    }

    public function behaviors() {
        return ArrayHelper::merge([
                    'imagesBehavior' => [
                        'class' => \panix\mod\images\behaviors\ImageBehavior::className(),
                    ],
                    /* 'eav' => [
                      'class' => \mazurva\eav\EavBehavior::className(),
                      'valueClass' => \mazurva\eav\models\EavAttributeValue::className(), // this model for table object_attribute_value
                      ], */
                    /* 'eav' => [
                      'class' => \mirocow\eav\EavBehavior::className(),
                      // это модель для таблицы object_attribute_value
                      'valueClass' => \mirocow\eav\models\EavAttributeValue::className(),
                      ], */
                    'eav' => [
                        'class' => \panix\mod\shop\components\EavBehavior::className(),
                        'tableName' => '{{%shop_product_attribute_eav}}'
                    ],
                    'translate' => [
                        'class' => TranslateBehavior::className(),
                        'translationAttributes' => [
                            'name',
                            'full_description'
                        ]
                    ],
                    'discountsBehavior' => [
                        'class' => \panix\mod\discounts\components\DiscountBehavior::className()
                    ],
                        ], parent::behaviors());
    }

    public static function formatPrice($price) {
        $c = Yii::$app->settings->get('shop');
        return iconv("windows-1251", "UTF-8", number_format($price, $c['price_penny'], chr($c['price_thousand']), chr($c['price_decimal'])));
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEavAttributes2($attributes = []) {
        return \mirocow\eav\models\EavAttribute::find()
                        ->joinWith('entity')
                        ->where([
                            //'categoryId' => $this->categories[0]->id,
                            'entityModel' => $this::className()
                        ])
                        ->orderBy(['order' => SORT_ASC]);
    }

    public static function calculatePrices($product, array $variants, $configuration) {
        if (($product instanceof Product) === false)
            $product = Product::findOne($product);

        if (($configuration instanceof Product) === false && $configuration > 0)
            $configuration = Product::findOne($configuration);

        if ($configuration instanceof Product) {
            $result = $configuration->price;
        } else {
            //  if ($product->currency_id) {
            //      $result = $product->price;
            //  } else {
            $result = $product->price;
            // $result = $product->getFrontPrice();
            //   }
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
