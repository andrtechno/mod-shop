<?php

namespace panix\mod\shop\components;

use panix\mod\shop\models\Attribute;

/**
 * 
 *
  [
    'class' => 'panix\mod\shop\components\AttributesColumns',
    'attribute' => 'size',
    'header' => 'Размеры',
    'contentOptions' => ['class' => 'text-center']
  ];
 * 
 * 
 */
class AttributesColumns extends \yii\grid\DataColumn {

    /**
     * @var array model attributes loaded with getEavAttributes method
     */
    protected $_attributes;

    /**
     * @var array of Attribute models
     */
    protected $_models;



    public function getList($data) {
        $this->_attributes = $data->getEavAttributes();
        $dataResult = $this->getModels($data, false);
        $result = array();
        if ($dataResult) {
            foreach ($dataResult as $model) {
                $result[] = array(
                    'class' => 'panix\mod\shop\components\AttributesColumns',
                    'filter' => true,
                    'header' => $model->title,
                    'attribute' => $model->name,
                    'value' => (isset($this->_attributes[$model->name])) ? $model->renderValue($this->_attributes[$model->name]) : false
                );
            }
        }
        return $result;
    }

    protected function renderDataCellContent($model, $key, $index) {
        $this->_attributes = $model->getEavAttributes();

        foreach ($this->getModels() as $model) {

            $dataResult[$model->name] = array(
                'title' => $model->title,
                'value' => (isset($this->_attributes[$model->name])) ? $model->renderValue($this->_attributes[$model->name]) : false
            );

        }

        if (!empty($dataResult)) {
            return $dataResult[$this->attribute]['value'];
        }

        return parent::renderDataCellContent($model, $key, $index);
    }

    protected function getModels($data = false, $useCondition = true) {

        // $query = Attribute::find();
        if ($useCondition) {
            //    $query->where(['name' => array_keys($this->_attributes)]);
        }

        $query = Attribute::find()
                ->displayOnFront()
                ->sorting()
                //->where(['IN', 'name', array_keys($this->_attributes)])
                ->all();

        foreach ($query as $m) {
            $this->_models[$m->name] = $m;
        }

        return $this->_models;
    }

}
