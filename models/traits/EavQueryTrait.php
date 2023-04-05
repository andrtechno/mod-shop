<?php

namespace panix\mod\shop\models\traits;

use panix\mod\shop\components\collections\CList;
use panix\mod\shop\models\ProductAttributesEav;
use Yii;

trait EavQueryTrait
{

    public function getFindByEavAttributesForViewTest($attributes)
    {
        $class = $this->modelClass;
        $pk = $class::tableName() . '.`id`';
        $i = 0;

        foreach ($attributes as $attribute => $values) {
            // Get attribute compare operator
            if (!is_array($values)) {
                $values = [$values];
            }

            $values = array_unique($values);
            sort($values);

            $values = array_intersect($attributes[$attribute], $values); //anti d-dos убирает лишние значение с запроса.
            // If search models with attribute name with specified values.
            if (is_string($attribute)) {

                //$this->join['eavb' . $i] = ['JOIN', '{{%shop__product_attribute_eav}} eavb' . $i, "$pk=`eavb$i`.`entity`"];

                    $this->andwhere(["$attribute" => $values]);



                // $this->join['eavb'] = ['JOIN', '{{%shop__product_attribute_eav}} eavb', "$pk=`eavb`.`entity`"];
                // $this->andwhere(['IN', "`eavb`.`value`", $values]);

            } elseif (is_int($attribute)) { // If search models with attribute name with anything values.
                //$this->join('JOIN', ProductAttributesEav::tableName() . ' eavb' . $i, "$pk=`eavb$i`.`entity` AND eavb$i.attribute = '$values'");

            }
            $i++;


        }


        // $this->distinct(true);

         //$this->groupBy("{$pk}");
        //$this->addGroupBy("{$pk}");
         //echo $this->createCommand()->rawsql;die;
        return $this;
    }
public function applyRootAttributes(array $attributes){
    if (empty($attributes))
        return $this;
    return $this->getFindByEavAttributesRoot($attributes);
}
    public function applyAttributes(array $attributes)
    {
        if (empty($attributes))
            return $this;
        return $this->withEavAttributes($attributes);
    }

    public function withEavAttributes($attributes = [])
    {
        // If not set attributes, search models with anything attributes exists.
        //if (empty($attributes)) {
        //     $attributes = $this->getSafeAttributesArray();
        // }

        // $attributes be array of elements: $attribute => $values
        return $this->getFindByEavAttributes2($attributes);
    }

    public function getEavAttributes22222222($attributes = array())
    {
        // Get all attributes if not specified.
        if (empty($attributes)) {
            $attributes = $this->getSafeAttributesArray();
        }
        // Values array.
        $values = array();
        // Queue for load.
        $loadQueue = new CList();
        foreach ($attributes as $attribute) {
            // Check is safe.
            // if ($this->hasSafeAttribute($attribute)) {
            $values[$attribute] = $attribute;
            // If attribute not set and not load, prepare array for loaded.
            // if (!$this->preload && $values[$attribute] === NULL) {
            //     $loadQueue->add($attribute);
            // }
            //}
        }
        // If array for loaded not empty, load attributes.
        if (!$this->preload && $loadQueue->count() > 0) {
            $this->loadEavAttributes($loadQueue->toArray());
            foreach ($loadQueue as $attribute) {
                $values[$attribute] = $this->attributes->itemAt($attribute);
            }
        }
        // Delete load queue.
        unset($loadQueue);
        // Return values.
        return $values;
    }

    public function getFindByEavAttributes($attributes)
    {
        $class = $this->modelClass;
        $pk = $class::tableName() . '.id';
        $i = 0;

        foreach ($attributes as $attribute => $values) {
            // If search models with attribute name with specified values.
            if (is_string($attribute)) {
                // Get attribute compare operator
                if (!is_array($values)) {
                    $values = array($values);
                }
                sort($values);


                $cache = \Yii::$app->cache->get("attribute_" . $attribute);
                //anti d-dos убирает лишние значение с запроса.
                if ($cache) {
                    $values = array_intersect($cache[$attribute], $values);
                }
                foreach ($values as $value) {
                    $this->join('JOIN', ProductAttributesEav::tableName() . ' eavbg' . $i, "{$pk}=`eavbg{$i}`.`entity`");
                    $this->andWhere(['IN', "`eavbg$i`.`value`", $values]);
                    $i++;
                }
            } // If search models with attribute name with anything values.
            elseif (is_int($attribute)) {
                $this->join('JOIN', ProductAttributesEav::tableName() . ' eavbg' . $i, "$pk=`eavbg$i`.`entity` AND eavbg$i.attribute = '$values'");
                $i++;
            }
        }

        //$this->distinct(true);
        $this->groupBy("{$pk}");
        // echo $this->createCommand()->getRawSql();die;
        return $this;
    }

    public function getFindByEavAttributes2($attributes)
    {
        $class = $this->modelClass;
        $pk = $class::tableName() . '.`id`';
        $i = 0;
        unset($attributes['brand']);
        foreach ($attributes as $attribute => $values) {
            // Get attribute compare operator
            if (!is_array($values)) {
                $values = [$values];
            }

            $values = array_unique($values);
            sort($values);

            $values = array_intersect($attributes[$attribute], $values); //anti d-dos убирает лишние значение с запроса.
            // If search models with attribute name with specified values.
            if (is_string($attribute)) {

                /*$this->join['eavbs' . $i] = ['JOIN', '{{%shop__product_attribute_eav}} eavbs' . $i, "$pk=`eavbs$i`.`entity`"];
                if (count($values)) {
                    $this->andwhere(['IN', "`eavbs$i`.`value`", $values]);
                    $this->andwhere(['IN', "`eavbs$i`.`value`", $values]);
                } else {
                    $this->andwhere(["`eavbs$i`.`value`" => $values]);
                }*/

                $this->join['eavb' . $i] = ['JOIN', '{{%shop__product_attribute_eav}} eavb' . $i, "$pk=`eavb$i`.`entity`"];
                $this->andwhere(['IN', "`eavb$i`.`value`", $values]);



                /*
                $this->join['eavb'] = ['JOIN', '{{%shop__product_attribute_eav}} eavb', "$pk=`eavb`.`entity`"];
                if(is_array($values)){
                    foreach ($values as $v){

                    }
                    $this->andwhere(['NOT IN',"`eavb`.`value`", 177]);
                }else{
                    $this->andwhere(["`eavb`.`value`" => $values]);
                }
                //$this->andwhere(['IN', "`eavb`.`value`", $values]);*/




                // $this->join['eavb'] = ['JOIN', '{{%shop__product_attribute_eav}} eavb', "$pk=`eavb`.`entity`"];
                // $this->andwhere(['IN', "`eavb`.`value`", $values]);

            } elseif (is_int($attribute)) { // If search models with attribute name with anything values.
                $this->join('JOIN', ProductAttributesEav::tableName() . ' eavb' . $i, "$pk=`eavb$i`.`entity` AND eavb$i.attribute = '$values'");
                //$this->join('JOIN', ProductAttributesEav::tableName().' eavb', "$pk=`eavb`.`entity` AND eavb.attribute = '$values'");

            }
            $i++;


        }


        // $this->distinct(true);

        // $this->groupBy("{$pk}");
        //$this->addGroupBy("{$pk}");
        //  echo $this->createCommand()->getRawSql();die;
        return $this;
    }

    public function getFindByEavAttributesFilterPro($attributes)
    {
        $class = $this->modelClass;
        $pk = $class::tableName() . '.`id`';
        $i = 0;

        foreach ($attributes as $attribute => $values) {
            // Get attribute compare operator
            if (!is_array($values)) {
                $values = [$values];
            }

            $values = array_unique($values);
            sort($values);
            $values = array_intersect($attributes[$attribute], $values); //anti d-dos убирает лишние значение с запроса.
            // If search models with attribute name with specified values.
            if (is_string($attribute)) {

                $this->join['eavb'] = ['JOIN', '{{%shop__product_attribute_eav}} eavb', "$pk=`eavb`.`entity`"];
                if (count($values)) {
                    $this->andwhere(['IN', "`eavb`.`value`", $values]);
                } else {
                    $this->andwhere(["`eavb`.`value`" => $values]);
                }


                // $this->join['eavb'] = ['JOIN', '{{%shop__product_attribute_eav}} eavb', "$pk=`eavb`.`entity`"];
                // $this->andwhere(['IN', "`eavb`.`value`", $values]);

            } elseif (is_int($attribute)) { // If search models with attribute name with anything values.
                $this->join('JOIN', ProductAttributesEav::tableName() . ' eavb' . $i, "$pk=`eavb$i`.`entity` AND eavb$i.attribute = '$values'");
                //$this->join('JOIN', ProductAttributesEav::tableName().' eavb', "$pk=`eavb`.`entity` AND eavb.attribute = '$values'");

            }
            $i++;


        }


        return $this;
    }

    public function getFindByEavAttributesRoot($attributes)
    {
        $class = $this->modelClass;
        $pk = $class::tableName() . '.`id`';
        $i = 0;

        foreach ($attributes as $attribute => $values) {
            // Get attribute compare operator
            if (!is_array($values)) {
                $values = [$values];
            }

            $values = array_unique($values);
            sort($values);
            $values = array_intersect($attributes[$attribute], $values); //anti d-dos убирает лишние значение с запроса.
            // If search models with attribute name with specified values.
            if (is_string($attribute)) {

                $this->join['eavbroot' . $i] = ['JOIN', '{{%shop__product_attribute_eav}} eavbroot' . $i, "$pk=`eavbroot$i`.`entity`"];
                if (count($values)) {
                    $this->andwhere(['IN', "`eavbroot$i`.`value`", $values]);
                } else {
                    $this->andwhere(["`eavbroot$i`.`value`" => $values]);
                }


                // $this->join['eavb'] = ['JOIN', '{{%shop__product_attribute_eav}} eavb', "$pk=`eavb`.`entity`"];
                // $this->andwhere(['IN', "`eavb`.`value`", $values]);

            } elseif (is_int($attribute)) { // If search models with attribute name with anything values.
                $this->join('JOIN', ProductAttributesEav::tableName() . ' eavbroot' . $i, "$pk=`eavbroot$i`.`entity` AND eavbroot$i.attribute = '$values'");
                //$this->join('JOIN', ProductAttributesEav::tableName().' eavb', "$pk=`eavb`.`entity` AND eavb.attribute = '$values'");

            }
            $i++;


        }


        // $this->distinct(true);

        // $this->groupBy("{$pk}");
        //$this->addGroupBy("{$pk}");
        //  echo $this->createCommand()->getRawSql();die;
        return $this;
    }

}
