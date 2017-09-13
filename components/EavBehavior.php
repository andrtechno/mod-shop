<?php
namespace panix\mod\shop\components;

use Yii;
use yii\db\ActiveRecord;
use panix\mod\shop\components\yii\CAttributeCollection;
use panix\mod\shop\components\yii\CList;



class EavBehavior extends \yii\base\Behavior {

    /**
     * @access public
     * @var string name of the table where data is stored. Required to be set on init behavior.
     * @default ''
     */
    public $tableName = '';

    /**
     * @access public
     * @var string prefix for each attribute.
     * @default ''
     */
    public $attributesPrefix = '';

    /**
     * @access protected
     * @var string owner model FK name. If not set automatically assign to model's primaryKey.
     * @default ''
     */
    protected $modelTableFk = '';

    /**
     * @access public
     * @var string name of the column to store entity name.
     * @default 'entity'
     */
    public $entityField = 'entity';

    /**
     * @access public
     * @var string name of the column to store attribute key.
     * @default 'attribute'
     */
    public $attributeField = 'attribute';

    /**
     * @access public
     * @var string name of the column to store value.
     * @default 'value'
     */
    public $valueField = 'value';

    /**
     * @access public
     * @var string caching component Id.
     * @default ''
     */
    public $cacheId = 'cache';

    /**
     * @access protected
     * @var ICache cache component object.
     * @default NULL
     */
    protected $cache = NULL;

    /**
     * @access protected
     * @var CAttributeCollection attributes store.
     * @default new CAttributeCollection
     */
    protected $attributes = NULL;

    /**
     * @access protected
     * @var CList changed attributes list.
     * @default new CList
     */
    protected $changedAttributes = NULL;

    /**
     * @access protected
     * @var CList safe attributes list.
     * @default new CList
     */
    protected $safeAttributes = NULL;

    /**
     * @access public
     * @var boolean loaded attributes after find model.
     * @default TRUE
     */
    public $preload = TRUE;
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            //ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',

        ];
    }
    /**
     * Returns owner model id.
     * @access protected
     * @return mixed
     */
    protected function getModelId() {
        return $this->owner->{$this->getModelTableFk()};
    }

    /**
     * Returns key for caching model attributes.
     * @access protected
     * @return string
     */
    protected function getCacheKey() {
        return __CLASS__ . $this->tableName . $this->attributesPrefix . $this->owner->tableName() . $this->getModelId();
    }

    /**
     * Set owner model FK name.
     * @param string owner model FK name.
     * @return void
     */
    public function setModelTableFk($modelTableFk) {
        if (is_string($modelTableFk) && !empty($modelTableFk)) {
            $this->modelTableFk = $modelTableFk;
        }
    }

    /**
     * Returns owner model FK name.
     * @access protected
     * @throws CException
     * @return string
     */
    protected function getModelTableFk() {
        // Check required property modelTableFk.
        if (empty($this->modelTableFk) || !$this->owner->hasAttribute($this->modelTableFk)) {
            // If property modelTableFk not set, trying to get a primary key from model table.
            $this->modelTableFk = $this->owner->getTableSchema()->primaryKey[0];

            if (!is_string($this->modelTableFk)) {
                throw new \yii\base\UnknownPropertyException(Yii::t('app', 'Table "{table}" does not have a primary key defined.', array('{table}' => $this->owner->getTableSchema())));
            }
        }
        return $this->modelTableFk;
    }

    /**
     * Strip prefix from attribute key.
     * @access protected
     * @param string attribute key
     * @return string
     */
    protected function stripPrefix($attribute) {
        // Remove prefix if exists.
        if (!empty($this->attributesPrefix) && strpos($attribute, $this->attributesPrefix) === 0) {
            $attribute = substr($attribute, strlen($this->attributesPrefix));
        }
        return $attribute;
    }

    /**
     * Set safe attributes array.
     * @param array safe attributes.
     * @return void
     */
    public function setSafeAttributes($safeAttributes) {
        $this->safeAttributes->copyFrom($safeAttributes);
    }

    /**
     * Return safe attributes key. If not set returns all keys.
     * @access protected
     * @return array
     */
    protected function getSafeAttributesArray() {
        return $this->safeAttributes->count() == 0 ? $this->attributes->keys : $this->safeAttributes->toArray();
    }

    /**
     * @access protected
     * @param string attribute key
     * @return boolean
     */
    protected function hasSafeAttribute($attribute) {
        if ($this->safeAttributes->count() > 0) {
            return $this->safeAttributes->contains($attribute);
        }
        return TRUE;
    }

    /**
     * @return void
     */
    public function __construct() {
        // Prepare attributes collection.
        $this->attributes = new CAttributeCollection;
        $this->attributes->caseSensitive = TRUE;
        // Prepare safe attributes list.
        $this->safeAttributes = new CList;
        // Prepare changed attributes list.
        $this->changedAttributes = new CList;
    }

    /**
     * @throws CException
     * @param CComponent
     * @return void
     */
    public function attach($owner) {
        // Check required property tableName.
        if (!is_string($this->tableName) || empty($this->tableName)) {
            throw new CException(self::t('yii', 'Property "{class}.{property}" is not defined.', array('{class}' => get_class($this), '{property}' => 'tableName')));
        }
        // Prepare translate component for behavior messages.
      /*  if (!Yii::$app->hasComponent(__CLASS__ . 'Messages')) {
            Yii::$app->setComponents(array(
                __CLASS__ . 'Messages' => array(
                    'class' => 'CPhpMessageSource',
                    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'messages',
                )
            ));
        }*/
        // Prepare cache component.
        $this->cache = Yii::$app->{$this->cacheId};
        if (!($this->cache instanceof ICache)) {
            // If not set cache component, use dummy cache.
            $this->cache = new \yii\caching\DummyCache();
        }
        // Call parent method for convenience.
        parent::attach($owner);
    }

    /**
     * @param CEvent
     * @return void
     */
    public function afterSave($event) {
        // TODO afterSave не срабатывает если модель не была изменена
        // Save changed attributes.
      //  if ($this->changedAttributes->count > 0) {
            $this->saveEavAttributes($this->changedAttributes->toArray());
       // }
        // Call parent method for convenience.
        parent::afterSave($event);
    }

    /**
     * @param CEvent
     * @return void
     */
    public function afterDelete($event) {
        // Delete all attributes.
        $this->deleteEavAttributes(array(), TRUE);
        // Call parent method for convenience.
        parent::afterDelete($event);
    }

    /**
     * @param CEvent
     * @return void
     */
    public function afterFind($event) {
        // Load attributes for model.
        if ($this->preload) {
            if ($this->owner->getPrimaryKey()){
                $this->loadEavAttributes($this->getSafeAttributesArray());
            }
        }
        // Call parent method for convenience.
       // parent::afterFind($event);
    }

    /**
     * @param array attributes key for save.
     * @return CActiveRecord
     */
    public function saveEavAttributes($attributes) {

        // Delete old attributes values from DB.
        $this->getDeleteCommand($attributes)->execute();
        // Process each attributes.
        foreach ($attributes as $attribute) {
            // Skip if null attributes.
            if (!is_null($values = $this->attributes->itemAt($attribute))) {
                // Create array of values for convenience.
                if (!is_array($values)) {
                    $values = array($values);
                }
                // Save each value of attribute into DB.
                foreach ($values as $value) {
                    $this->getSaveEavAttributeCommand($this->attributesPrefix . $attribute, $value)->execute();
                }
                // Remove from changed list.
                $this->changedAttributes->remove($attribute);
            }
        }
        // Save attributes to cache.
        if ($this->attributes->count > 0) {
            $this->cache->set($this->getCacheKey(), $this->attributes->toArray());
        }
        // Or delete cache is attributes not exists.
        else {
            $this->cache->delete($this->getCacheKey());
        }
        // Return model.
        return $this->owner;
    }

    /**
     * @access public
     * @param array attributes key for load.
     * @return CActiveRecord
     */
    public function loadEavAttributes($attributes) {
        // If exists cache, return it.
        //$data = $this->cache->get($this->getCacheKey());
      //  if ($data !== FALSE) {
      //      $this->attributes->mergeWith($data, FALSE);
      //      return $this->owner;
     //   }
        // Query DB.
        
     
       

        $data = $this->getLoadEavAttributesCommand($attributes)->all();
        foreach ($data as $row) {
            $attribute = $this->stripPrefix($row[$this->attributeField]);
            $value = $row[$this->valueField];

            // Check if value exists.
            if (!is_null($current = $this->attributes->itemAt($attribute)) && $current != $value) {
                //$value = is_array($current) ? $current[] = $value : array($current, $value);
                // Fix if entity has many values
                if (is_array($current)) {
                    $current[] = $value;
                    $value = $current;
                } else
                    $value = array($current, $value);
            }

            $this->attributes->add($attribute, $value);
        }
        // Save loaded attributes to cache.
        //$this->cache->set($this->getCacheKey(), $this->attributes->toArray());
        // Return model.
        return $this->owner;
    }

    /**
     * @param array attributes key for delete.
     * @param boolean whether auto save attributes.
     * @return CActiveRecord
     */
    public function deleteEavAttributes($attributes = array(), $save = FALSE) {
        // If not set attributes for deleting, delete all.
        if (empty($attributes)) {
            $attributes = $this->attributes->keys;
        }
        // Delete each attributes.
        foreach ($attributes as $attribute) {
            $this->attributes->remove($attribute);
            $this->changedAttributes->add($attribute);
        }
        // Auto save if set.
        if ($save) {
            $this->saveEavAttributes($attributes);
        }
        // Return model.
        return $this->owner;
    }

    /**
     * @param array attributes values for change.
     * @param boolean whether auto save attributes.
     * @return CActiveRecord
     */
    public function setEavAttributes($attributes, $save = FALSE) {
        foreach ($attributes as $attribute => $value) {
            $this->attributes->add($attribute, $value);
            $this->changedAttributes->add($attribute);
        }
        // Auto save if set.
        if ($save) {
            $this->saveEavAttributes(array_keys($attributes));
        }
        // Return model.
        return $this->owner;
    }

    /**
     * @param string attribute key.
     * @param mixed attribute value.
     * @param boolean whether auto save attributes.
     * @return CActiveRecord
     */
    public function setEavAttribute($attribute, $value, $save = FALSE) {
        return $this->setEavAttributes(array($attribute => $value), $save);
    }

    /**
     * @param array attributes key for get.
     * @return array
     */
    public function getEavAttributes($attributes = []) {
        // Get all attributes if not specified.
        if (empty($attributes)) {
            $attributes = $this->getSafeAttributesArray();
        }
        // Values array.
        $values = array();
        // Queue for load.
        $loadQueue = new CList;
        foreach ($attributes as $attribute) {
            // Check is safe.
            if ($this->hasSafeAttribute($attribute)) {
                $values[$attribute] = $this->attributes->itemAt($attribute);
                // If attribute not set and not load, prepare array for loaded.
                if (!$this->preload && $values[$attribute] === NULL) {
                    $loadQueue->add($attribute);
                }
            }
        }
        // If array for loaded not empty, load attributes.
        if (!$this->preload && $loadQueue->count() > 0) {
            $this->loadEavAttributes($attributes);
           foreach ($loadQueue as $attribute) {
                $values[$attribute] = $this->attributes->itemAt($attribute);
            }
        }
        // Delete load queue.
        unset($loadQueue);
        // Return values.
        return $values;
    }

    /**
     * @param string attribute for get.
     * @return mixed
     */
    public function getEavAttribute($attribute) {
        $values = $this->getEavAttributes(array($attribute));
        return $this->attributes->itemAt($attribute);

    }

    /**
     * Limit current AR query to have all attributes and values specified.
     * @param array attributes values or key for filter models.
     * @return CActiveRecord
     */
    public function withEavAttributes($attributes = array()) {
        // If not set attributes, search models with anything attributes exists.
        if (empty($attributes)) {
            $attributes = $this->getSafeAttributesArray();
        }
        // $attributes be array of elements: $attribute => $values
        $criteria = $this->getFindByEavAttributesCriteria($attributes);
        // Merge model criteria.
        $this->owner->getDbCriteria()->mergeWith($criteria);
        // Return model.
        return $this->owner;
    }

    /**
     * @access protected
     * @param  $attribute
     * @param  $value
     * @return CDbCommand
     */
    protected function getSaveEavAttributeCommand($attribute, $value) {
        $data = array(
            $this->entityField => $this->getModelId(),
            $this->attributeField => $attribute,
            $this->valueField => $value,
        );
        return $this->owner->db->createCommand()->insert($this->tableName, $data);
        /*return $this->owner
                        ->getCommandBuilder()
                        ->createInsertCommand($this->tableName, $data);*/
    }

    /**
     * @access protected
     * @param  $attributes
     * @return CDbCommand
     */
    protected function getLoadEavAttributesCommand($attributes) {
      //  print_r($attributes);
       // return [];
          //      $criteria->addCondition("{$this->entityField} = {$this->getModelId()}");
       // if (!empty($attributes)) {
       //     $criteria->addInCondition($this->attributeField, $attributes);
       // }
        $query = new \yii\db\Query;
// compose the query
$query->from($this->tableName)->where([$this->entityField=>$this->getModelId()]);
if (!empty($attributes)) {
  $query->andWhere(['IN', $this->attributeField,$attributes ]);
}

return $query;

               
       /* return $this->owner
                        ->getCommandBuilder()
                        ->createFindCommand($this->tableName, $this->getLoadEavAttributesCriteria($attributes));*/
    }

    /**
     * @access protected
     * @param  $attributes
     * @return CDbCommand
     */
    protected function getDeleteCommand($attributes = array()) {
        $query = new \yii\db\Query;
// compose the query
$query->from($this->tableName)->where([$this->entityField=>$this->getModelId()]);
if (!empty($attributes)) {
  $query->andWhere(['IN', $this->attributeField,$attributes ]);
}

return $query->createCommand();
       /* return $this->owner
                        ->getCommandBuilder()
                        ->createDeleteCommand($this->tableName, $this->getDeleteEavAttributesCriteria($attributes));*/
    }

    /**
     * @access protected
     * @param  $attributes
     * @return CDbCriteria
     */
    protected function getLoadEavAttributesCriteria($attributes = array()) {
        $criteria = new CDbCriteria;
        $criteria->addCondition("{$this->entityField} = {$this->getModelId()}");
        if (!empty($attributes)) {
            $criteria->addInCondition($this->attributeField, $attributes);
        }
        return $criteria;
    }

    /**
     * @access protected
     * @param  $attributes
     * @return CDbCriteria
     */
    protected function getDeleteEavAttributesCriteria($attributes = array()) {
        return $this->getLoadEavAttributesCriteria($attributes);
    }

    
    
    protected function getFindByEavAttributesCriteria($attributes) {
        $criteria = new CDbCriteria();
        $pk = $this->getModelTableFk();

        $conn = $this->owner->getDbConnection();
        $i = 0;
        foreach ($attributes as $attribute => $values) {
            // If search models with attribute name with specified values.
            if (is_string($attribute)) {
                // Get attribute compare operator
                $attribute = $conn->quoteValue($attribute);
                if (!is_array($values)) {
                    $values = array($values);
                }

                foreach ($values as $value) {
                    $value = $conn->quoteValue($value);
                    $criteria->join .= "\nJOIN {$this->tableName} eavb$i"
                            . "\nON t.{$pk} = eavb$i.{$this->entityField}";
                          //  . "\nAND eavb$i.{$this->attributeField} = $attribute"
                          //  . "\nAND eavb$i.{$this->valueField} = $value";
                    

                    //$criteria->addCondition("eavb$i.{$this->attributeField}=$attribute");
                    $criteria->addInCondition("eavb$i.{$this->valueField}", $values);

                    $i++;
                }
            }
            // If search models with attribute name with anything values.
            elseif (is_int($attribute)) {
                $values = $conn->quoteValue($values);
                $criteria->join .= "\nJOIN {$this->tableName} eavb$i"
                        . "\nON t.{$pk} = eavb$i.{$this->entityField}"
                        . "\nAND eavb$i.{$this->attributeField} = $values";
                $i++;
            }
        }
        $criteria->distinct = true;
        //$criteria->order = '`t`.`ordern` DESC';
        $criteria->group .= "t.{$pk}";
        return $criteria;
    }
    
    
    /**
     * @access protected
     * @param  $attributes
     * @return CDbCriteria
     */
    protected function getFindByEavAttributesCriteria2($attributes) {
        $criteria = new CDbCriteria();
        $pk = $this->getModelTableFk();

        $conn = $this->owner->getDbConnection();
        $i = 0;
        foreach ($attributes as $attribute => $values) {
            // If search models with attribute name with specified values.
            if (is_string($attribute)) {
                // Get attribute compare operator
                $attribute = $conn->quoteValue($attribute);
                if (!is_array($values)){
                    $values = array($values);
                }
                foreach ($values as $value) {
                    $value = $conn->quoteValue($value);
                   $criteria->join .= "\nJOIN {$this->tableName} eavb$i"
                            . "\nON t.{$pk} = eavb$i.{$this->entityField}"
                            . "\nAND eavb$i.{$this->attributeField} = $attribute"
                            . "\nAND eavb$i.{$this->valueField} = $value";
                    
             
                    $i++;

                }
             
            }
            // If search models with attribute name with anything values.
            elseif (is_int($attribute)) {
                $values = $conn->quoteValue($values);
                $criteria->join .= "\nJOIN {$this->tableName} eavb$i"
                        . "\nON t.{$pk} = eavb$i.{$this->entityField}"
                        . "\nAND eavb$i.{$this->attributeField} = $values";
                $i++;
            }
        }
        $criteria->distinct = true;
        $criteria->group .= "t.{$pk}";
        return $criteria;
    }

}
