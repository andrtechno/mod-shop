<?php

namespace panix\mod\shop\models;

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
 * @method ShopCategory useInFilter()
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

    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return '{{%shop_attribute}}';
    }

    public function getAttrtranslate() {
        return $this->hasMany(AttributeTranslate::className(), ['object_id' => 'id']);
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return [
            [['name', 'title'], 'required'],
            [['name', 'title', 'abbreviation'], 'string', 'max' => 255],
            [['required', 'use_in_compare', 'use_in_filter', 'select_many', 'display_on_front', 'use_in_variants'], 'boolean'],
            // array('name', 'unique'),
            // array('name', 'match',
            //     'pattern' => '/^([a-z0-9_-])+$/i',
            //      'message' => self::t('PATTERN_NAME')
            //  ),
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
                        ], parent::behaviors());
    }

    public function getOptions() {
        return $this->hasMany(AttributeOption::className(), ['attribute_id' => 'id']);
    }

    public function getTypes() {
        return $this->hasMany(TypeAttribute::className(), ['attribute_id' => 'id']);
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
                return Html::dropDownList($name, $value, $data, []);
                //return Yii::app()->controller->widget('ext.bootstrap.selectinput.SelectInput',array('data'=>$data,'value'=>$value,'htmlOptions'=>array('name'=>$name,'empty'=>Yii::t('app','EMPTY_LIST'))),true);
                break;
            case self::TYPE_SELECT_MANY:
                $data = ArrayHelper::map($this->options, 'id', 'value');
                return Html::dropDownList($name . '[]', $value, $data, ['multiple' => 'multiple']);
                break;
            case self::TYPE_RADIO_LIST:
                $data = ArrayHelper::map($this->options, 'id', 'value');
                return Html::radioList($name, (string) $value, $data, ['separator' => '']);
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
        switch ($this->type):
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
        endswitch;
    }

    /**
     * @return string html id based on name
     */
    public function getIdByName() {
        $name = 'Attribute[' . $this->name . ']';
        return Html::getIdByName($name);
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

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return ActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search2() {
        $criteria = new CDbCriteria;

        $criteria->with = array('attr_translate');

        $criteria->compare('`t`.`id`', $this->id);
        $criteria->compare('`t`.`name`', $this->name, true);
        $criteria->compare('`attr_translate`.`title`', $this->title, true);
        $criteria->compare('`attr_translate`.`abbreviation`', $this->abbreviation, true);
        $criteria->compare('`t`.`type`', $this->type);
        $criteria->compare('`t`.`ordern`', $this->ordern);
        $sort = new CSort;
        $sort->defaultOrder = '`t`.`ordern` DESC';
        $sort->attributes = array(
            '*',
            'abbreviation' => array(
                'asc' => '`attr_translate`.`abbreviation`',
                'desc' => '`attr_translate`.`abbreviation` DESC',
            ),
            'title' => array(
                'asc' => '`attr_translate`.`title`',
                'desc' => '`attr_translate`.`title` DESC',
            ),
        );

        return new ActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => $sort
        ));
    }

    public function afterDelete() {
        // Delete options
        foreach ($this->options as $o)
            $o->delete();

        // Delete relations used in product type.
        ShopTypeAttribute::model()->deleteAllByAttributes(array('attribute_id' => $this->id));

        // Delete attributes assigned to products
        $conn = $this->getDbConnection();
        $command = $conn->createCommand("DELETE FROM `{{shop_product_attribute_eav}}` WHERE `attribute`='{$this->name}'");
        $command->execute();

        return parent::afterDelete();
    }

}
