<?php

namespace panix\mod\shop\models;

use Yii;
use panix\engine\WebModel;
use panix\engine\behaviors\TranslateBehavior;
use panix\mod\shop\models\ShopCategory;
use panix\mod\shop\models\ShopManufacturer;
use panix\mod\shop\models\query\ShopProductQuery;
use panix\mod\shop\models\translate\ShopProductTranslate;
use panix\mod\shop\models\ShopRelatedProduct;
use yii\helpers\ArrayHelper;
use salopot\attach\behaviors\AttachFileBehavior;
use salopot\attach\behaviors\AttachImageBehavior;

class ShopProduct extends WebModel {

    private $_related;

    const MODULE_ID = 'shop';

    public static function find() {
        return new ShopProductQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%shop_product}}';
    }

    public function getUrl() {
        return ['/shop/product/view', 'url' => $this->seo_alias];
    }

    public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }

    public $file;

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

    public function getCategory() {
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
                        'class' => \rico\yii2images\behaviors\ImageBehave::className(),
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

}
