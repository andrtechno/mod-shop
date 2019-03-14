<?php

namespace panix\mod\shop\components;

use panix\engine\Html;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Product;
use Yii;

class AttributesRender extends \yii\base\Widget {

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
            if (isset($this->_attributes[$model->name])) {
                $data[$model->title] = $model->renderValue($this->_attributes[$model->name]);
            }
        }


        if ($data) {
            return $this->render($this->list, array(
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
        //$cr = new CDbCriteria;
        //$cr->addInCondition('t.name', array_keys($this->_attributes));

        $query = Attribute::getDb()->cache(function () {
            return Attribute::find(['IN', 'name', array_keys($this->_attributes)])
                ->displayOnFront()
                ->sorting()
                ->all();
        }, 3600);
        /*$query = Attribute::find(['IN', 'name', array_keys($this->_attributes)])
                ->displayOnFront()
                ->sorting()
                ->all();*/

        foreach ($query as $m)
            $this->_models[$m->name] = $m;

        return $this->_models;
    }

    public function getModelsLanguage($lang) {
        if (is_array($this->_models))
            return $this->_models;

        $this->_models = array();
        //$cr = new CDbCriteria;
        //$cr->addInCondition('t.name', array_keys($this->_attributes));
        /*$query = Attribute::find(['IN', 'name', array_keys($this->_attributes)])
                //->language($lang)
                ->displayOnFront()
                ->sorting()
                ->all();*/
        $query = Attribute::getDb()->cache(function () {
            return Attribute::find(['IN', 'name', array_keys($this->_attributes)])
                ->displayOnFront()
                ->sorting()
                ->all();
        }, 3600);



        foreach ($query as $m)
            $this->_models[$m->name] = $m;

        return $this->_models;
    }

    public function getData(Product $model) {

        $cacheId = 'product_attributes_' . strtotime($model->updated_at) . '_' . strtotime($model->created_at);
        $result = Yii::$app->cache->get($cacheId);
        if ($result === false) {
            foreach (Yii::$app->languageManager->languages as $lang => $l) {
                $result[$lang] = array();
                $productModel = Product::find($model->id)
                        //->language($l->id)
                        ->one();
                $this->_attributes = $productModel->getEavAttributes();
                foreach ($this->getModelsLanguage($l->id) as $data) {
                    if (isset($this->_attributes[$data->name])) {
                        $result[$lang][$data->name] = (object) [
                                    'name' => $data->title,
                                    'value' => $data->renderValue($this->_attributes[$data->name]),
                        ];
                    }
                }
            }
            Yii::$app->cache->set($cacheId, $result, Yii::$app->settings->get('app', 'cache_time'));
        }
        return (object) $result[Yii::$app->language];
    }

}
