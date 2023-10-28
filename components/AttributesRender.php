<?php

namespace panix\mod\shop\components;

use panix\engine\CMS;
use Yii;
use yii\base\Widget;
use panix\engine\Html;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Product;
use yii\helpers\ArrayHelper;

class AttributesRender extends Widget
{

    public $view = '_list';

    /**
     * @var Attribute with EAV behavior enabled
     */
    public $model;

    /**
     * @var array table element attributes
     */
    public $htmlOptions = [];

    /**
     * @var array model attributes loaded with getEavAttributes method
     */
    protected $_attributes;

    /**
     * @var array of Attribute models
     */
    protected $_models;

    /**
     * Render attributes table
     */
    public function run()
    {

        $eav = $this->model;
        /** @var \panix\mod\shop\components\EavBehavior $eav */
        $this->_attributes = $eav->getEavAttributes();


        $data = [];
        $groups = [];
        foreach ($this->getModels() as $model) {
            /** @var Attribute $model */
            $abbr = ($model->abbreviation) ? ' ' . $model->abbreviation : '';

            $value = $model->renderValue($this->_attributes[$model->name]) . $abbr;
            $data_json = ArrayHelper::map($model->options, 'id', 'data');

            if (Yii::$app->settings->get('shop', 'group_attribute')) {
                if ($model->group_id) {
                    $groups[$model->group->name][] = [
                        'id' => $model->id,
                        'name' => $model->title,
                        'hint' => $model->hint,
                        'value' => $value,
                        'data' => $data_json[$this->_attributes[$model->name]]
                    ];
                }
            }

            $data[$model->name] = [
                'id' => $this->_attributes[$model->name],
                'type' => $model->type,
                'use_in_filter' => $model->use_in_filter,
                'hasUrl' => in_array($model->type, [Attribute::TYPE_CHECKBOX_LIST, Attribute::TYPE_SELECT_MANY, Attribute::TYPE_DROPDOWN]),
                'title' => $model->title,
                'value' => $value,
                'data' => $data_json[$this->_attributes[$model->name]]
            ];
        }

        return $this->render('@shop/views/product/' . $this->view, [
            'data' => $data,
            'model' => $this->model,
            'groups' => $groups,
        ]);

    }

    /**
     * Для авто заполнение short_description товара
     * @param object $object Модель товара
     * @return string
     */
    public function getStringAttr($object)
    {
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
    public function getModels()
    {
        if (is_array($this->_models))
            return $this->_models;

        $this->_models = [];

        // $query = Attribute::getDb()->cache(function () {
        $query = Attribute::find()
            ->where(['IN', 'name', array_keys($this->_attributes)])
            ->displayOnFront()
            ->sort()
            ->all();
        // }, 3600);

        foreach ($query as $m)
            $this->_models[$m->name] = $m;

        return $this->_models;
    }

    public function ___getModelsLanguage($lang)
    {
        if (is_array($this->_models))
            return $this->_models;

        $this->_models = [];
        //$cr = new CDbCriteria;
        //$cr->addInCondition('t.name', array_keys($this->_attributes));
        /*$query = Attribute::find(['IN', 'name', array_keys($this->_attributes)])
                //->language($lang)
                ->displayOnFront()
                ->sort()
                ->all();*/
        $query = Attribute::getDb()->cache(function () {
            return Attribute::find()
                ->where(['IN', 'name', array_keys($this->_attributes)])
                ->displayOnFront()
                ->sort()
                ->all();
        }, 3600);


        foreach ($query as $m)
            $this->_models[$m->name] = $m;

        return $this->_models;
    }

    public function getData(Product $model)
    {
        //todo added depandancy
        $cacheId = "product_{$model->primaryKey}_attributes";
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
                        $result[$lang][$data->name] = (object)[
                            'name' => $data->title,
                            'value' => $data->renderValue($this->_attributes[$data->name]),
                        ];
                    }
                }
            }
            Yii::$app->cache->set($cacheId, $result, Yii::$app->settings->get('app', 'cache_time'));
        }
        return (object)$result[Yii::$app->language];
    }

}
