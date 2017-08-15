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
class AttributesRender extends CWidget {

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
        $groups = array();
        foreach ($this->getModels() as $model) {
            /* if (isset($model->group)) {
              $groups[$model->group->name][] = array(
              'name' => $model->title,
              'value' => $model->renderValue($this->_attributes[$model->name])
              );
              } else { */
            $data[$model->title] = $model->renderValue($this->_attributes[$model->name]);
            //}
        }

        if (!empty($data)) {
            Yii::app()->controller->renderPartial($this->list, array(
                'data' => $data,
                'groups' => $groups,
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

    public function getModelsLanguage($lang) {
        if (is_array($this->_models))
            return $this->_models;

        $this->_models = array();
        $cr = new CDbCriteria;
        $cr->addInCondition('t.name', array_keys($this->_attributes));
        $query = ShopAttribute::model()
                ->language($lang)
                ->displayOnFront()
                ->sorting()
                ->findAll($cr);

        foreach ($query as $m)
            $this->_models[$m->name] = $m;

        return $this->_models;
    }

    public function getData(ShopProduct $model) {

        $cacheId = 'product_attributes_'.strtotime($model->date_update).'_'.strtotime($model->date_create);
        $result = Yii::app()->cache->get($cacheId);
        if ($result === false) {
            foreach (Yii::app()->languageManager->languages as $lang => $l) {
                $result[$lang] = array();
                $productModel = ShopProduct::model()->language($l->id)->findByPk($model->id);
                $this->_attributes = $productModel->getEavAttributes();
                foreach ($this->getModelsLanguage($l->id) as $data) {
                    $result[$lang][$data->name] = (object) array(
                        'name' => $data->title,
                        'value' => $data->renderValue($this->_attributes[$data->name]),

                    );
                }
            }
            Yii::app()->cache->set($cacheId, $result, Yii::app()->settings->get('app', 'cache_time'));
        }
        return (object) $result[Yii::app()->language];
    }

}
