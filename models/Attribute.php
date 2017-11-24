<?php

namespace panix\mod\shop\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use panix\mod\shop\models\AttributeTranslate;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\query\AttributeQuery;

/**
 * This is the model class for table "Attribute".
 *
 * The followings are the available columns in table 'Attribute':
 * @property integer $id
 * @property string $name
 * @property string $title
 * @property integer $type
 * @property boolean $display_on_front
 * @property integer $ordern
 * @property boolean $required
 * @property boolean $use_in_compare
 * @property boolean $use_in_filter Display attribute options as filter on front
 * @property boolean $use_in_variants Use attribute and its options to configure products
 * @property boolean $select_many Allow to filter products on front by more than one option value.
 * @method Category useInFilter()
 */
class Attribute extends \panix\engine\db\ActiveRecord {

    const TYPE_TEXT = 1;
    const TYPE_TEXTAREA = 2;
    const TYPE_DROPDOWN = 3;
    const TYPE_SELECT_MANY = 4;
    const TYPE_RADIO_LIST = 5;
    const TYPE_CHECKBOX_LIST = 6;
    const TYPE_YESNO = 7;
    const MODULE_ID = 'shop';

    public static function find() {
        return new AttributeQuery(get_called_class());
    }

    public function getGridColumns() {
        return [
            [
                'attribute' => 'title',
                'contentOptions' => ['class' => 'text-left'],
            ],
            'name',
            'DEFAULT_CONTROL' => [
                'class' => 'panix\engine\grid\columns\ActionColumn',
            ],
            'DEFAULT_COLUMNS' => [
                [
                    'class' => \panix\engine\grid\sortable\Column::className(),
                    'url' => ['/admin/shop/attribute/sortable']
                ],
                ['class' => 'panix\engine\grid\columns\CheckboxColumn'],
            ],
        ];
    }

    public function transactions() {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }

    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return '{{%shop_attribute}}';
    }

    public function getAttrtranslate() {
        return $this->hasMany(AttributeTranslate::className(), ['object_id' => 'id']);
    }
/*
    public function ___getAttrtranslateOne() {
        return $this->hasOne(AttributeTranslate::className(), ['object_id' => 'id']);
    }
*/
    public function getOptions() {
        return $this->hasMany(AttributeOption::className(), ['attribute_id' => 'id']);
    }

    public function getTypes() {
        return $this->hasMany(TypeAttribute::className(), ['attribute_id' => 'id']);
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return [
            [['name', 'title'], 'required'],
            [['name', 'title'], 'trim'],
            ['name', '\panix\engine\validators\UrlValidator',
                'attributeCompare' => 'title',
                'attributeSlug' => 'name',
                'message'=>'Индификатор занят'
                ],
            [['name', 'title', 'abbreviation'], 'string', 'max' => 255],
            [['required', 'use_in_compare', 'use_in_filter', 'select_many', 'display_on_front', 'use_in_variants'], 'boolean'],
            // array('name', 'unique'),
            ['name', 'match',
                'pattern' => '/^([a-z0-9-])+$/i',
                'message' => Yii::t('app', 'PATTERN_URL')
            ],
            //  array('type, ordern, group_id', 'numerical', 'integerOnly' => true),
            [['id', 'name', 'title', 'type'], 'safe'],
        ];
    }

    public function behaviors() {
        return ArrayHelper::merge([
                    'translate' => [
                        'class' => \panix\engine\behaviors\TranslateBehavior::className(),
                        'translationRelation' => 'attrtranslate',
                        'translationAttributes' => [
                            'title',
                            'abbreviation'
                        ]
                    ],
                                'slug' => [
                        'class' => \yii\behaviors\SluggableBehavior::className(),
                        'attribute' => 'title',
                        'slugAttribute' => 'name',
                    ],
                        ], parent::behaviors());
    }

    /**
     * Get types as key value list
     * @static
     * @return array
     */
    public static function getTypesList() {
        return array(
            self::TYPE_TEXT => 'Text',
            self::TYPE_TEXTAREA => 'Textarea',
            self::TYPE_DROPDOWN => 'Dropdown (Filter)',
            self::TYPE_SELECT_MANY => 'Multiple Select (Filter)',
            self::TYPE_RADIO_LIST => 'Radio List (Filter)',
            self::TYPE_CHECKBOX_LIST => 'Checkbox List (Filter)',
            self::TYPE_YESNO => 'Yes/No',
        );
    }

    public static function getSort() {
        return new \yii\data\Sort([
            'attributes' => [
                'title' => [
                    'asc' => ['title' => SORT_ASC],
                    'desc' => ['title' => SORT_DESC],
                ],
            ],
        ]);
    }

    /**
     * @return string html field based on attribute type
     */
    public function renderField($value = null) {

        $name = 'Attribute[' . $this->name . ']';
        switch ($this->type) {
            case self::TYPE_TEXT:
                return Html::textInput($name, $value, array('class' => 'form-control'));
                break;
            case self::TYPE_TEXTAREA:
                return Html::textarea($name, $value, array('class' => 'form-control'));
                break;
            case self::TYPE_DROPDOWN:
                $data = ArrayHelper::map($this->options, 'id', 'value');
                return Html::dropDownList($name, $value, $data, ['class' => 'form-control pull-left', 'prompt' => Yii::t('app', 'EMPTY_LIST')]);
                //return Yii::app()->controller->widget('ext.bootstrap.selectinput.SelectInput',array('data'=>$data,'value'=>$value,'htmlOptions'=>array('name'=>$name,'empty'=>Yii::t('app','EMPTY_LIST'))),true);
                break;
            case self::TYPE_SELECT_MANY:
                $data = ArrayHelper::map($this->options, 'id', 'value');
                return Html::dropDownList($name . '[]', $value, $data, ['class' => 'form-control pull-left', 'multiple' => 'multiple', 'prompt' => Yii::t('app', 'EMPTY_LIST')]);
                break;
            case self::TYPE_RADIO_LIST:
                $data = ArrayHelper::map($this->options, 'id', 'value');
                return Html::radioList($name, $value, $data, ['separator' => '<br/>']);
                break;
            case self::TYPE_CHECKBOX_LIST:
                $data = ArrayHelper::map($this->options, 'id', 'value');
                return Html::checkboxList($name . '[]', $value, $data, ['separator' => '']);
                break;
            case self::TYPE_YESNO:
                $data = array(
                    1 => Yii::t('app', 'YES'),
                    2 => Yii::t('app', 'NO')
                );
                return Html::dropDownList($name, $value, $data);
                break;
        }
    }

    /**
     * Get attribute value
     * @param $value
     * @return string attribute value
     */
    public function renderValue($value) {
        switch ($this->type) {
            case self::TYPE_TEXT:
            case self::TYPE_TEXTAREA:
                return $value;
                break;
            case self::TYPE_DROPDOWN:
            case self::TYPE_RADIO_LIST:
                $data = ArrayHelper::map($this->options, 'id', 'value');
                if (!is_array($value) && isset($data[$value]))
                    return $data[$value];
                break;
            case self::TYPE_SELECT_MANY:
            case self::TYPE_CHECKBOX_LIST:
                $data = ArrayHelper::map($this->options, 'id', 'value');
                $result = array();

                if (!is_array($value))
                    $value = array($value);

                foreach ($data as $key => $val) {
                    if (in_array($key, $value))
                        $result[] = $val;
                }
                return implode(', ', $result);
                break;
            case self::TYPE_YESNO:
                $data = array(
                    1 => Yii::t('app', 'YES'),
                    2 => Yii::t('app', 'NO')
                );
                if (isset($data[$value]))
                    return $data[$value];
                break;
        }
    }

    /**
     * @return string html id based on name
     */
    public function getIdByName() {
        $name = 'Attribute[' . $this->name . ']';
        return Html::getInputId($this, $this->name);
    }

    /**
     * Get type label
     * @static
     * @param $type
     * @return string
     */
    public static function getTypeTitle($type) {
        $list = self::getTypesList();
        return $list[$type];
    }

    public function afterDelete() {
        // Delete options
        foreach ($this->options as $o)
            $o->delete();

        // Delete relations used in product type.
        TypeAttribute::deleteAll(['attribute_id' => $this->id]);

        // Delete attributes assigned to products
        $conn = $this->getDb();
        $conn->createCommand()->delete('{{%shop_product_attribute_eav}}', "`attribute`='{$this->name}'")->execute();


        return parent::afterDelete();
    }

}
