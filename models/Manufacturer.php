<?php

namespace panix\mod\shop\models;

use panix\engine\Html;
use Yii;
use yii\helpers\ArrayHelper;
use panix\engine\db\ActiveRecord;
use panix\mod\shop\models\query\ManufacturerQuery;
use panix\mod\shop\models\translate\ManufacturerTranslate;

class Manufacturer extends ActiveRecord
{

    const MODULE_ID = 'shop';
    const route = '/admin/shop/manufacturer';
    public $translationClass = ManufacturerTranslate::class;


    /**
     * @inheritdoc
     * @return ManufacturerQuery
     */
    public static function find()
    {
        return new ManufacturerQuery(get_called_class());
    }

    public function getGridColumns()
    {
        return [
            'image' => [
                'class' => 'panix\engine\grid\columns\ImageColumn',
                'attribute' => 'image',
                'value' => function ($model) {
                    return Html::a(Html::img($model->getImageUrl('image', '50x50'), ['alt' => $model->name, 'class' => 'img-thumbnail_']), $model->getImageUrl('image'), ['title' => $this->name, 'data-fancybox' => 'gallery']);
                }
            ],
            'name' => [
                'attribute' => 'name',
                'format' => 'html',
                'contentOptions' => ['class' => 'text-left'],
                'value' => function ($model) {
                    return Html::a($model->name, $model->getUrl(), ['target' => '_blank']);
                }
            ],
            'DEFAULT_CONTROL' => [
                'class' => 'panix\engine\grid\columns\ActionColumn',
            ],
            'DEFAULT_COLUMNS' => [
                [
                    'class' => \panix\engine\grid\sortable\Column::class,
                    'url' => ['/admin/shop/default/sortable']
                ],
                ['class' => 'panix\engine\grid\columns\CheckboxColumn'],
            ],
        ];
    }

    public static function getSort()
    {
        return new \yii\data\Sort([
            'attributes' => [
                //'date_create',
                'name' => [
                    'asc' => ['name' => SORT_ASC],
                    'desc' => ['name' => SORT_DESC],
                ],
            ],
        ]);
    }

    public static function dropdown()
    {
        // get and cache data
        static $dropdown;
        if ($dropdown === null) {

            // get all records from database and generate
            $models = static::find()->all();
            foreach ($models as $model) {
                $dropdown[$model->id] = $model->name;
            }
        }

        return $dropdown;
    }

    public function getUrl()
    {
        return ['/shop/manufacturer/view', 'slug' => $this->slug];
    }

    public function transactions222()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
            // 'update'=>self::OP_UPDATE
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__manufacturer}}';
    }

    public function getTranslations()
    {
        return $this->hasMany($this->translationClass, ['object_id' => 'id']);
    }

    /*public function getTranslation()
    {
        return $this->hasOne(ManufacturerTranslate::class, ['object_id' => 'id']);
    }*/

    public function getProductsCount()
    {
        return $this->hasOne(Product::class, ['manufacturer_id' => 'id'])->count();
    }

    // public $image;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'slug'], 'required'],
            [['name', 'slug'], 'trim'],
            ['slug', 'match',
                'pattern' => '/^([a-z0-9-])+$/i',
                'message' => Yii::t('app', 'PATTERN_URL')
            ],
            ['slug', '\panix\engine\validators\UrlValidator', 'attributeCompare' => 'name'],
            [['description'], 'string'],
            [['description'], 'default', 'value' => null],
            [['name', 'slug'], 'string', 'max' => 255],
            [['ordern'], 'integer'],
            [['name', 'slug'], 'safe'],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
        ];
    }

    public function behaviors()
    {
        $a = [];
         if (Yii::$app->getModule('seo'))
           $a['seo'] = [
                'class' => '\panix\mod\seo\components\SeoBehavior',
                'url' => $this->getUrl()
            ];

        $a['translate'] = [
            'class' => 'panix\engine\behaviors\TranslateBehavior',
            'translationAttributes' => [
                'name',
                'description'
            ]
        ];
        $a['upload'] = [
            'class' => 'panix\engine\behaviors\UploadFileBehavior',
            'files' => [
                'image' => '@uploads/manufacturer',
            ],
            'options' => [
                'watermark' => false
            ]
        ];

        return ArrayHelper::merge($a, parent::behaviors());
    }

}
