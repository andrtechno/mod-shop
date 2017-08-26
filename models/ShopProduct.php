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
    public $exclude = null;
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

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
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

    public function getRelated2() {
        return $this->hasMany(ShopRelatedProduct::className(), ['product_id' => 'id']);
    }

    public function getRelatedProductCount() {
        return $this->hasMany(ShopRelatedProduct::className(), ['product_id' => 'id'])->count();
    }

    public function getRelatedProducts________() {
      //  return $this->hasMany(ShopProduct::className(), ['id' => 'product_id'])->via('related');
        return $this->hasMany(ShopProduct::className(), ['related_id' => 'id'])
                ->via('related');
                //->viaTable(ShopRelatedProduct::tableName(), ['product_id' => 'id']);
    }
    
    public function getRelatedProducts() {
        return $this->hasMany(ShopRelatedProduct::className(), ['related_id' => 'id'])
              ->via('related2');
               // ->viaTable(ShopProduct::tableName(), ['id' => 'product_id']);
    }

    public function setRelatedProducts($ids = []) {
        $this->_related = $ids;
    }

    private function clearRelatedProducts() {
        return ShopRelatedProduct::deleteAll('product_id=:id', ['id' => $this->id]);
    }

    public function afterSave($insert, $changedAttributes) {

        // Process related products
        if ($this->_related !== null) {
            $this->clearRelatedProducts();

            foreach ($this->_related as $id) {
                $related = Yii::$app->getModule("shop")->model("ShopRelatedProduct");
                $related->product_id = $this->id;
                $related->related_id = $id;
                $related->save();

                //двустороннюю связь между товарами
                if (Yii::$app->settings->get('shop', 'product_related_bilateral')) {
                    $related = Yii::$app->getModule("shop")->model("ShopRelatedProduct");
                    $related->product_id = $id;
                    $related->related_id = $this->id;
                    $related->save();
                }
            }
        }
 
        parent::afterSave($insert, $changedAttributes);
    }

    /*
      // 'related' => array(self::HAS_MANY, 'ShopRelatedProduct', 'product_id'),
      'relatedProducts' => array(self::HAS_MANY, 'ShopProduct', array('related_id' => 'id'), 'through' => 'related'),
      //'relatedProductCount' => array(self::STAT, 'ShopRelatedProduct', 'product_id'),
     *  */

    public function behaviors() {
        return ArrayHelper::merge([
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
                    'image' => [
                        'class' => AttachImageBehavior::className(),
                        'attributeName' => 'image',
                        'relativeTypeDir' => '/uploads',
                        'types' => array(
                            'thumb' => array(
                                //'format' => 'gif', //"gif", "jpeg", "png", "wbmp", "xbm"
                                'process' => function($behavior, $image) {
                                    return $image->thumbnail(new \Imagine\Image\Box(500, 500));
                                }
                            ),
                            'background' => array(
                                'process' => function($behavior, $image) {
                                    $image = $image->thumbnail(new \Imagine\Image\Box(500, 500));
                                    $image->effects()->grayscale();
                                    return $image;
                                },
                            ),
                            'main' => array(
                                //'processOn' => AttachImageBehavior::PT_DEMAND, //PT_RENDER, PT_BASE64_ENCODED,
                                'process' => function($behavior, $image) {
                                    $watermark = \yii\imagine\Image::getImagine()->open(Yii::getAlias('@webroot/uploads') . DIRECTORY_SEPARATOR . 'watermark.png');
                                    $size = $image->getSize();
                                    $wSize = $watermark->getSize();
                                    $bottomRight = new \Imagine\Image\Point($size->getWidth() - $wSize->getWidth(), $size->getHeight() - $wSize->getHeight());
                                    //$top_left = new \Imagine\Image\Point(0, 0);
                                    //$position_center = new \Imagine\Image\Point($size->getWidth() / 2 - $wSize->getWidth() / 2, $size->getHeight() / 2 - $wSize->getHeight() / 2);
                                    $image->paste($watermark, $bottomRight);
                                    $image = $image->thumbnail(new \Imagine\Image\Box(500, 500));
                                    return $image;
                                },
                            ),
                        ),
                    ]
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
