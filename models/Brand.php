<?php

namespace panix\mod\shop\models;

use panix\engine\Html;
use panix\mod\shop\components\ExternalFinder;
use panix\mod\sitemap\behaviors\SitemapBehavior;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use panix\engine\db\ActiveRecord;
use panix\mod\shop\models\query\BrandQuery;
use panix\mod\shop\models\translate\BrandTranslate;

/**
 * Class Brand
 * @property integer $id
 * @property string $slug
 * @property string $name BrandTranslate
 * @property string $description
 * @property Product[] $productsCount
 *
 */
class Brand extends ActiveRecord
{

    const MODULE_ID = 'shop';
    const route = '/admin/shop/brand';

    /**
     * @inheritdoc
     * @return BrandQuery
     */
    public static function find()
    {
        return new BrandQuery(get_called_class());
    }

    public function getGridColumns()
    {
        return [
            'image' => [
                'class' => 'panix\engine\grid\columns\ImageColumn',
                'attribute' => 'image',
                'value' => function ($model) {
                    return Html::a(Html::img($model->getImageUrl('image', '50x50'), ['alt' => $model->name, 'class' => 'img-thumbnail_']), $model->getImageUrl('image'), ['title' => $model->name, 'data-fancybox' => 'gallery']);
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
            'products' => [
                'header' => static::t('PRODUCTS_COUNT'),
                'format' => 'html',
                'attribute' => 'productsCount',
                'contentOptions' => ['class' => 'text-center'],
                'value' => function ($model) {
                    return Html::a($model->productsCount, ['/admin/shop/product', 'ProductSearch[brand_id]' => $model->id]);
                }
            ],
            'created_at' => [
                'attribute' => 'created_at',
                'class' => 'panix\engine\grid\columns\jui\DatepickerColumn',
            ],
            'updated_at' => [
                'attribute' => 'updated_at',
                'class' => 'panix\engine\grid\columns\jui\DatepickerColumn',
            ],
            'DEFAULT_CONTROL' => [
                'class' => 'panix\engine\grid\columns\ActionColumn',
            ],
            'DEFAULT_COLUMNS' => [
                [
                    'class' => \panix\engine\grid\sortable\Column::class,
                ],
                ['class' => 'panix\engine\grid\columns\CheckboxColumn'],
            ],
        ];
    }

    public static function getSort()
    {
        return new \yii\data\Sort([
            'attributes' => [
                'name' => [
                    'asc' => ['name' => SORT_ASC],
                    'desc' => ['name' => SORT_DESC],
                ],
            ],
        ]);
    }

    public function getUrl()
    {
        return ['/shop/brand/view', 'slug' => $this->slug];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__brand}}';
    }


    /**
     * Products count relation
     * @return int|string
     */
    public function getProductsCount()
    {
        return $this->hasOne(Product::class, ['brand_id' => 'id'])->count();
    }

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
                'message' => Yii::t('app/default', 'PATTERN_URL')
            ],
            ['slug', '\panix\engine\validators\UrlValidator', 'attributeCompare' => 'name'],
            [['description'], 'string'],
            [['description', 'image'], 'default'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['ordern'], 'integer'],
            [['name', 'slug'], 'safe'],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => ['png', 'jpg']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $a = [];
        if (Yii::$app->getModule('seo'))
            $a['seo'] = [
                'class' => '\panix\mod\seo\components\SeoBehavior',
                'url' => $this->getUrl()
            ];
        if (Yii::$app->getModule('sitemap')) {
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
                        'loc' => $model->getUrl(),
                        'lastmod' => $model->updated_at,
                        //'name' => $model->name,
                        'changefreq' => SitemapBehavior::CHANGEFREQ_DAILY,
                        'priority' => 0.8
                    ];
                }
            ];
        }
        $a['uploadFile'] = [
            'class' => 'panix\engine\behaviors\UploadFileBehavior',
            'files' => [
                'image' => '@uploads/brand',
            ],
            'options' => [
                'watermark' => false
            ]
        ];
        $a['translate'] = [
            'class' => '\panix\mod\shop\components\TranslateBehavior',
            'translationAttributes' => ['name', 'description']
        ];
        return ArrayHelper::merge($a, parent::behaviors());
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        if (Yii::$app->hasModule('csv')) {
            $external = new ExternalFinder('{{%csv}}');
            $external->deleteObject(ExternalFinder::OBJECT_BRAND, $this->id);
        }
        parent::afterDelete();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        TagDependency::invalidate(Yii::$app->cache, 'brand-'.$this->id);
        parent::afterSave($insert, $changedAttributes);
    }
}
