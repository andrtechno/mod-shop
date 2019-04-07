<?php

namespace panix\mod\shop\components;

use panix\engine\Html;
use panix\mod\shop\models\Attribute;
use Yii;
class AttributeData {

    public $model;
    public $attributes;


    public function __construct($model) {
        $this->model = $model;
        $this->attributes = $model->getEavAttributes();
    }

    /**
     * @return array of used attribute models
     */
    public function getModels($lang = false) {
        $_models = [];

        $query = Attribute::find()
            ->where(['IN', 'name', array_keys($this->attributes)])
            ->displayOnFront()
            ->sort()
            ->all();

        foreach ($query as $m)
            $_models[$m->name] = $m;

        return $_models;
    }

    public function getData() {
        $result=[];
        foreach (Yii::$app->languageManager->languages as $lang => $l) {
            $result[$lang] = array();
            foreach ($this->getModels($l->id) as $data) {
                $result[$lang][$data->name] = (object) array(
                    'name' => $data->title,
                    'value' => $data->renderValue($this->attributes[$data->name]),
                );
            }
        }

        return (object) $result[Yii::$app->language];
    }

    /**
     * Для авто заполнение short_description товара
     * @param type $object Модель товара
     * @return string
     */
    public function getStringAttr() {
        $data = array();
        foreach ($this->getModels() as $model)
            $data[$model->title] = $model->renderValue($this->attributes[$model->name]);
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

}
