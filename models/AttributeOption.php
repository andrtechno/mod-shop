<?php

namespace panix\mod\shop\models;

use Yii;
use yii\caching\DbDependency;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use panix\mod\shop\models\translate\AttributeOptionTranslate;
use panix\mod\shop\models\query\AttributeOptionsQuery;
use panix\engine\db\ActiveRecord;

/**
 * Shop options for dropdown and multiple select
 * This is the model class for table "AttributeOptions".
 *
 * The followings are the available columns in table 'AttributeOptions':
 * @property integer $id
 * @property integer $attribute_id
 * @property string $value
 * @property integer $position
 */
class AttributeOption extends ActiveRecord
{

    public $translationClass = AttributeOptionTranslate::class;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%shop__attribute_option}}';
    }

    public static function find()
    {
        return new AttributeOptionsQuery(get_called_class());
    }

    public function rules()
    {
        return [
            [['id', 'value', 'attribute_id', 'ordern'], 'safe'],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }

    public function getTranslations()
    {
        return $this->hasMany($this->translationClass, ['object_id' => 'id']);
    }

    public function getProductsCount()
    {
        $query = $this->hasMany(ProductAttributesEav::class, ['value' => 'id']);


        $dependencyQuery = $query;
        $dependencyQuery->select('COUNT(*)');
        $dependency = new DbDependency([
            'sql' => $dependencyQuery->createCommand()->rawSql,
        ]);


        //print_r($query->createCommand()->rawSql);die;
        return $query->cache(3200, $dependency)->count();
    }


    public function getAttr()
    {
        return $this->hasOne(Attribute::class, ['id' => 'attribute_id']);
    }


    public function behaviors()
    {
        return ArrayHelper::merge([
            'translate' => [
                'class' => \panix\engine\behaviors\TranslateBehavior::class,
                //'translationRelation' => 'optionTranslate',
                'translationAttributes' => [
                    'value',
                ]
            ],
        ], parent::behaviors());
    }

    public function beforeSave2($insert)
    {
        if (parent::beforeSave($insert)) {
            // Записываем в кеш данные об атрибуте
            // чтобы в EEavBehavior избавится от не нужных данных в запросах.


            Yii::$app->cache->delete("attribute_" . $this->attr->name);

            $options = Yii::$app->cache->get("attribute_" . $this->attr->name);
            if ($options === false) {
                $options[$this->attr->name] = array();
                if ($this->attr->options) {
                    foreach ($this->attr->options as $option) {
                        $options[$this->attr->name][] = $option->id;
                    }
                }
                Yii::$app->cache->set("attribute_" . $this->attr->name, $options);
            }
            return true;
        }
    }

}
