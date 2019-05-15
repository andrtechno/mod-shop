<?php

namespace panix\mod\shop\models;

use yii\base\BaseObject;

/**
 * Present Category as JsTree node.
 */
class CategoryNode extends BaseObject
{

    /**
     * @var \panix\mod\shop\models\Category
     */
    protected $model;

    /**
     * @var string category name
     */
    protected $_name;

    /**
     * @var integer category id
     */
    protected $_id;

    /**
     * @var bool category switch
     */
    protected $_switch;

    /**
     * @var boolean
     */
    protected $_hasChildren;

    /**
     * @var array category children
     */
    protected $_children;
    protected $options = [];


    public function __construct($model, $options = [])
    {
        $this->options = &$options;
        $this->model = &$model;
        parent::__construct([]);
        return $this;
    }

    /**
     * Create nodes from array
     *
     * @param array $options
     * @param array $model
     * @return array
     */
    public static function fromArray($model, $options = [])
    {
        $result = [];
        foreach ($model as $row) {
            //if(isset($options['switch'])){
            // if($row->switch) //$options['switch'] ||
            $result[] = new CategoryNode($row, $options);
            // }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function getHasChildren()
    {
        return (boolean) $this->model->children()->count();
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return self::fromArray($this->model->children()->all(), $this->options);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->model->name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->model->id;
    }

    /**
     * @return string
     */
    public function getSwitch()
    {
        return $this->model->switch;
    }

}
