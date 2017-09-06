<?php

namespace panix\mod\shop\models;

use yii\helpers\ArrayHelper;
use panix\mod\shop\models\AttributeOptionTranslate;

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
class AttributeOption extends \panix\engine\WebModel {

    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return '{{%shop_attribute_option}}';
    }

    public function rules() {
        return array(
            array('id, value, attribute_id, ordern', 'safe', 'on' => 'search'),
        );
    }

    public function getOptionTranslate() {
        return $this->hasMany(AttributeOptionTranslate::className(), ['object_id' => 'id']);
    }

    public function behaviors() {
        return ArrayHelper::merge([
                    'translate' => [
                        'class' => \panix\engine\behaviors\TranslateBehavior::className(),
                        'translationRelation' => 'optionTranslate',
                        'translationAttributes' => [
                            'value',
                        ]
                    ],
                        ], parent::behaviors());
    }

    public function search() {
        $criteria = new CDbCriteria;

        $criteria->with = array('optionTranslate');

        $criteria->compare('`t`.`id`', $this->id);
        $criteria->compare('`option_translate`.`value`', $this->value, true);
        $criteria->compare('`t`.`ordern`', $this->ordern);
        if (isset($_GET['id'])) {
            $criteria->compare('`t`.`attribute_id`', $_GET['id']);
        }
        $sort = new CSort;
        $sort->defaultOrder = '`t`.`ordern` ASC';
        $sort->attributes = array(
            '*',
            'value' => array(
                'asc' => '`option_translate`.`value`',
                'desc' => '`option_translate`.`value` DESC',
            ),
        );

        return new ActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => $sort
        ));
    }

}
