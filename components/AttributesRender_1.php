<?php

/**
 * Render product attributes table.
 * Basically used on product view page.
 * Usage:
 *     $this->widget('application.modules.shop.widgets.SAttributesTableRenderer', array(
 *        // SProduct model
 *        'model'=>$model,
 *         // Optional. Html table attributes.
 *        'htmlOptions'=>array('class'=>'...', 'id'=>'...', etc...)
 *    ));
 */
class AttributesRender_1 extends CWidget {
    public $list = '_list';
    /**
     * @var ActiveRecord with EAV behavior enabled
     */
    public $model;

    /**
     * @var array table element attributes
     */
    public $htmlOptions = array();

    /**
     * @var array model attributes loaded with getEavAttributes method
     */
    protected $_attributes;

    /**
     * @var array of ShopAttribute models
     */
    protected $_models;

    /**
     * Render attributes table
     */
    public function run() {
        $this->_attributes = $this->model->getEavAttributes();

        $data = array();
        foreach ($this->getModels() as $model)
            $data[$model->title] = $model->renderValue($this->_attributes[$model->name]);

        if (!empty($data)) {
            Yii::app()->controller->renderPartial($this->list, array(
                'data' => $data
            ));
        }
    }

    /**
     * Для авто заполнение short_description товара
     * @param type $object Модель товара
     * @return string
     */
    public function getStringAttr($object) {
        $this->_attributes = $object->getEavAttributes();

        $data = array();
        foreach ($this->getModels() as $model)
            $data[$model->title] = $model->renderValue($this->_attributes[$model->name]);
        $content = '';
        if (!empty($data)) {
            $numItems = count($data);
            $i = 0;
            foreach ($data as $title => $value) {
                if (++$i === $numItems) { //last element
                    $content .= Html::encode($title) . ': ' . Html::encode($value);
                } else {
                    $content .= Html::encode($title) . ': ' . Html::encode($value) . ' / ';
                }
            }
        }
        return $content;
    }

    /**
     * @return array of used attribute models
     */
    public function getModels() {
        if (is_array($this->_models))
            return $this->_models;

        $this->_models = array();
        $cr = new CDbCriteria;
        $cr->addInCondition('t.name', array_keys($this->_attributes));
        $query = ShopAttribute::model()
                ->displayOnFront()
                ->sorting()
                ->findAll($cr);

        foreach ($query as $m)
            $this->_models[$m->name] = $m;

        return $this->_models;
    }

}
