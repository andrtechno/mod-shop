<?php

/**
 * 
 * array(
  'class' => 'mod.shop.components.AttributesColumns',
  'attrname' => 'size',
  'header' => 'Размеры',
  'htmlOptions' => array('class' => 'text-center')
  );
 */
Yii::import('ext.adminList.columns.DataColumn');

class AttributesColumns extends DataColumn {

    /**
     * @var array model attributes loaded with getEavAttributes method
     */
    protected $_attributes;

    /**
     * @var array of ShopAttribute models
     */
    protected $_models;
    public $attrname;
    private $query;
    private $filterName;
    public function getFilterCellContent() {
        /* if (is_string($this->filter))
          return $this->filter;
          elseif ($this->filter !== false && $this->grid->filter !== null && $this->name !== null && strpos($this->name, '.') === false) {
          if (is_array($this->filter))
          return CHtml::activeDropDownList($this->grid->filter, $this->name, $this->filter, array('id' => false, 'prompt' => '', 'class' => 'form-control'));
          elseif ($this->filter === null)
          return CHtml::activeTextField($this->grid->filter, $this->name, array('id' => false, 'class' => 'form-control'));
          } else
          return parent::getFilterCellContent(); */
        return CHtml::dropDownList('Product[eav]['.$this->filterName.']', isset($_GET['Product']['eav'])?$_GET['Product']['eav']:null, $this->filter, array('prompt' => '', 'class' => 'form-control'));
    }

    /**
     * Initializes the column.
     * This method registers necessary client script for the checkbox column.
     */
    public function init() {
        $this->query = Attribute::model()
                ->sorting()
                ->findByAttributes(array('name' => $this->attrname));
        if ($this->query) {
            $this->header = $this->query->title;
        }
        $this->filterName = $this->query->name;
        $this->filter = Html::listData($this->query->options, 'id', 'value');

        if ($this->attrname === null)
            throw new CException(Yii::t('zii', 'Either "attrname" must be specified for AttributesColumns.'));
    }

    public function getList($data) {
        $this->_attributes = $data->getEavAttributes();
        $dataResult = $this->getModels($data, false);
        $result = array();
        if ($dataResult) {
            foreach ($dataResult as $model) {
                $result[] = array(
                    'class' => 'mod.shop.components.AttributesColumns',
                    'filter' => true,
                    'header' => $model->title,
                    'attrname' => $model->name,
                    'value' => (isset($this->_attributes[$model->name])) ? $model->renderValue($this->_attributes[$model->name]) : false
                );
            }
        }
        return $result;
    }

    protected function renderHeaderCellContent() {

        if ($this->query) {
            echo $this->query->title;
        } else {
            parent::renderHeaderCellContent();
        }
    }

    /**
     * Renders the data cell content.
     * This method renders a checkbox in the data cell.
     * @param integer $row the row number (zero-based)
     * @param mixed $data the data associated with the row
     */
    protected function renderDataCellContent($row, $data) {
        if ($this->value !== null)
            $value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row));
        elseif ($this->name !== null)
            $value = CHtml::value($data, $this->name);
        else
            $value = $this->grid->dataProvider->keys[$row];

        $this->_attributes = $data->getEavAttributes();

        foreach ($this->getModels($value) as $model) {
            $dataResult[$model->name] = array(
                'title' => $model->title,
                'value' => $model->renderValue($this->_attributes[$model->name])
            );
        }

        if (!empty($dataResult)) {
            echo $dataResult[$this->attrname]['value'];
        }
    }

    protected function getModels($data, $useCondition = true) {
        //$this->_models = array();
        //$cacheId = 'product_attributes_' . strtotime($data->date_update) . '_' . $data->id;
        //$this->_models = Yii::app()->cache->get($cacheId);
        //if ($this->_models === false) {
        $cr = new CDbCriteria;
        if ($useCondition)
            $cr->addInCondition('t.name', array_keys($this->_attributes));

        $query = Attribute::model()
                ->displayOnFront()
                ->sorting()
                ->findAll($cr);

        foreach ($query as $m) {
            $this->_models[$m->name] = $m;
        }
        //  Yii::app()->cache->set($cacheId, $this->_models, Yii::app()->settings->get('app', 'cache_time'));
        // }
        return $this->_models;
    }

}
