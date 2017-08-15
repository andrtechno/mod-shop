<?php

namespace panix\shop\models;

use Yii;
use panix\engine\WebModel;
use panix\engine\behaviors\TranslateBehavior;
use panix\shop\models\ShopCategory;
use panix\shop\models\ShopManufacturer;
use panix\shop\models\query\ShopProductQuery;
use panix\shop\models\translate\ShopProductTranslate;
use yii\helpers\ArrayHelper;
use salopot\attach\behaviors\AttachFileBehavior;
use salopot\attach\behaviors\AttachImageBehavior;

class ShopProduct extends WebModel {

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
