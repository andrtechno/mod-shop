<?php

namespace panix\mod\shop\models;

use Yii;
use panix\engine\WebModel;
use panix\engine\CMS;
use panix\engine\behaviors\TranslateBehavior;
use panix\mod\shop\models\ShopCategory;
use panix\mod\shop\models\ShopManufacturer;
use panix\mod\shop\models\query\ShopProductQuery;
use panix\mod\shop\models\translate\ShopProductTranslate;
use panix\mod\shop\models\ShopRelatedProduct;
use panix\mod\shop\models\ShopProductCategoryRef;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ShopProduct extends WebModel {

    private $_related;
    public $file;

    const MODULE_ID = 'shop';

    public static function find() {
        return new ShopProductQuery(get_called_class());
    }

    public static function getCSort() {
        $sort = new \yii\data\Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
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
        return ['/shop/default/view', 'url' => $this->seo_alias];
    }

    public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
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
            [['sku', 'full_description'], 'default'], // установим ... как NULL, если они пустые
            [['name', 'seo_alias', 'price', 'category_id'], 'required'],
            [['name', 'seo_alias'], 'string', 'max' => 255],
            [['manufacturer_id', 'quantity', 'views', 'added_to_cart_count', 'ordern', 'category_id'], 'integer'],
            [['name', 'seo_alias', 'full_description'], 'safe'],
                //  [['c1'], 'required'], // Attribute field
                // [['c1'], 'string', 'max' => 255], // Attribute field
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getCategory2() {
        return $this->hasOne(ShopCategory::className(), ['id' => 'category_id']);
    }

    public function getManufacturer() {
        return $this->hasOne(ShopManufacturer::className(), ['id' => 'category_id']);
    }

    public function getTranslations() {
        return $this->hasMany(ShopProductTranslate::className(), ['object_id' => 'id']);
    }

    public function getRelated() {
        return $this->hasMany(ShopRelatedProduct::className(), ['related_id' => 'id']);
    }

    public function getRelatedProductCount() {
        return $this->hasMany(ShopRelatedProduct::className(), ['product_id' => 'id'])->count();
    }

    public function getRelatedProducts() {
        //  return $this->hasMany(ShopProduct::className(), ['id' => 'product_id'])->via('related');
        return $this->hasMany(ShopProduct::className(), ['id' => 'product_id'])
                        // ->via('related');
                        ->viaTable(ShopRelatedProduct::tableName(), ['related_id' => 'id']);
    }

    public function getRelatedProductsHZ() {
        //  return $this->hasMany(ShopProduct::className(), ['id' => 'product_id'])->via('related');
        return $this->hasMany(ShopProduct::className(), ['id' => 'related_id'])
                        // ->via('related');
                        ->viaTable(ShopRelatedProduct::tableName(), ['product_id' => 'id']);
    }

    public function getCategorization2() {
        return $this->hasMany(ShopProductCategoryRef::className(), ['product' => 'id']);
    }

    public function getCategorization() {
        return $this->hasMany(ShopProductCategoryRef::className(), ['id' => 'product']);
    }

    /**
     * Set product categories and main category
     * @param array $categories ids.
     * @param integer $main_category Main category id.
     */
    public function setCategories(array $categories, $main_category) {
        $dontDelete = array();


        // if (!ShopCategory::model()->countByAttributes(array('id' => $main_category)))
        //    $main_category = 1;

        if (!in_array($main_category, $categories))
            array_push($categories, $main_category);


        foreach ($categories as $c) {
            /* $count = ShopProductCategoryRef::model()->countByAttributes(array(
              'category' => $c,
              'product' => $this->id,
              )); */
            $count = ShopProductCategoryRef::find()->where(array(
                        'category' => $c,
                        'product' => $this->id,
                    ))->count();



            if ($count == 0) {
                $record = new ShopProductCategoryRef;
                $record->category = (int) $c;
                $record->product = $this->id;
                //$record->switch = $this->switch; // new param
                $record->save(false);
            }

            $dontDelete[] = $c;
        }

        // Clear main category
        ShopProductCategoryRef::updateAll([
            'is_main' => 0,
                // 'switch' => $this->switch
                ], 'product=:p', array(':p' => $this->id));

        // Set main category
        ShopProductCategoryRef::updateAll(array(
            'is_main' => 1,
            'switch' => $this->switch,
                ), 'product=:p AND category=:c', array(':p' => $this->id, ':c' => $main_category));

        // Delete not used relations
        if (sizeof($dontDelete) > 0) {
            // $cr = new CDbCriteria;
            // $cr->addNotInCondition('category', $dontDelete);
            //    $query = ShopProductCategoryRef::deleteAll(['product=:id','category NOT IN (:cats)'],[':id'=>$this->id,':cats'=>implode(',',$dontDelete)]);
            $query = ShopProductCategoryRef::deleteAll(
                            ['AND',
                        'product=:id',
                        ['NOT IN', 'category', $dontDelete]
                            ], [':id' => $this->id]);
            // ->andWhere(['not in','category',$dontDelete]);
            //  foreach($query as $q){
            // }
        } else {

            // Delete all relations 
            ShopProductCategoryRef::deleteAll('product=:id', [':id' => $this->id]);
        }
    }

    public function setRelatedProducts($ids = []) {
        $this->_related = $ids;
    }

    private function clearRelatedProducts() {
        ShopRelatedProduct::deleteAll('product_id=:id', ['id' => $this->id]);
        if (Yii::$app->settings->get('shop', 'product_related_bilateral')) {
            ShopRelatedProduct::deleteAll('related_id=:id', ['id' => $this->id]);
        }
    }

    public function afterSave($insert, $changedAttributes) {
        // Process related products
        if ($this->_related !== null) {
            $this->clearRelatedProducts();

            foreach ($this->_related as $id) {
                $related = new ShopRelatedProduct;
                $related->product_id = $this->id;
                $related->related_id = (int) $id;
                if ($related->save()) {
                    //двустороннюю связь между товарами
                    if (Yii::$app->settings->get('shop', 'product_related_bilateral')) {
                        $related = new ShopRelatedProduct;

                        $related->product_id = (int) $id;
                        $related->related_id = $this->id;
                        if (!$related->save()) {
                            throw new \yii\base\Exception('Error save product relation');
                        }
                    }
                }
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete() {
        $this->clearRelatedProducts();
        ShopRelatedProduct::deleteAll('related_id=:id', array('id' => $this->id));
        //  $this->removeImages();
        $image = $this->getImages();

        if ($image) {
            //get path to resized image
            $this->removeImages();
        }

        parent::afterDelete();
    }

    // public function getMainImageUrl() {
    //  return $this->getImage()->getUrl('50x50');
    // }

    /*
      // 'related' => array(self::HAS_MANY, 'ShopRelatedProduct', 'product_id'),
      'relatedProducts' => array(self::HAS_MANY, 'ShopProduct', array('related_id' => 'id'), 'through' => 'related'),
      //'relatedProductCount' => array(self::STAT, 'ShopRelatedProduct', 'product_id'),
     *  */

    public function behaviors() {
        return ArrayHelper::merge([
                    'imagesBehavior' => [
                        'class' => \panix\mod\images\behaviors\ImageBehavior::className(),
                    ],
                    'eav' => [
                        'class' => \mirocow\eav\EavBehavior::className(),
                        // это модель для таблицы object_attribute_value
                        'valueClass' => \mirocow\eav\models\EavAttributeValue::className(),
                    ],
                    'translate' => [
                        'class' => TranslateBehavior::className(),
                        'translationAttributes' => [
                            'name',
                            'full_description'
                        ]
                    ],
                    'verbs' => [
                        'class' => \yii\filters\VerbFilter::className(),
                        'actions' => [
                            'delete' => ['post'],
                        ],
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
    public function getEavAttributes($attributes = []) {
        return \mirocow\eav\models\EavAttribute::find()
                        ->joinWith('entity')
                        ->where([
                            //'categoryId' => $this->categories[0]->id,
                            'entityModel' => $this::className()
                        ])
                        ->orderBy(['order' => SORT_ASC]);
    }

    public static function calculatePrices($product, array $variants, $configuration) {
        if (($product instanceof ShopProduct) === false)
            $product = ShopProduct::model()->findByPk($product);

        //if (($configuration instanceof ShopProduct) === false && $configuration > 0)
        //    $configuration = ShopProduct::model()->findByPk($configuration);

        if ($configuration instanceof ShopProduct) {
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
        if (!empty($variants) && ($variants[0] instanceof ShopProductVariant) === false)
            $variants = ShopProductVariant::model()->findAllByPk($variants);

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
