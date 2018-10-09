<?php

namespace panix\mod\shop\models;

use Yii;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\query\ManufacturerQuery;
use panix\engine\behaviors\TranslateBehavior;
use panix\mod\shop\models\translate\ManufacturerTranslate;

class Manufacturer extends \panix\engine\db\ActiveRecord {

    const MODULE_ID = 'shop';
    const route = '/admin/shop/manufacturer';

    public static function find() {
        return new ManufacturerQuery(get_called_class());
    }

    public function getGridColumns() {
        return [
            'name',
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
    public static function getSort() {
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
    public static function dropdown() {
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

    public function getUrl() {
        return ['/shop/manufacturer/view', 'seo_alias' => $this->seo_alias];
    }

    public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%shop__manufacturer}}';
    }

    public function getTranslations() {
        return $this->hasMany(ManufacturerTranslate::class, ['object_id' => 'id']);
    }

    public function getProductsCount() {
        return $this->hasOne(Product::class, ['manufacturer_id' => 'id'])->count();
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'seo_alias'], 'required'],
            [['name', 'seo_alias'], 'trim'],
            ['seo_alias', 'match',
                'pattern' => '/^([a-z0-9-])+$/i',
                'message' => Yii::t('app','PATTERN_URL')
            ],
            ['seo_alias', '\panix\engine\validators\UrlValidator','attributeCompare'=>'name'],
            [['description'], 'string'],
            [['name', 'seo_alias'], 'string', 'max' => 255],
            [['ordern'], 'integer'],
            [['name', 'seo_alias'], 'safe'],
        ];
    }

    public function behaviors() {
        return ArrayHelper::merge([
                    'translate' => [
                        'class' => TranslateBehavior::class,
                        'translationAttributes' => [
                            'name',
                            'description'
                        ]
                    ],
                        ], parent::behaviors());
    }

}
