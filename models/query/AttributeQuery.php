<?php

namespace panix\mod\shop\models\query;


use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;
use panix\engine\traits\query\TranslateQueryTrait;

/**
 * Class AttributeQuery
 * @package panix\mod\shop\models\query
 * @use ActiveQuery
 */
class AttributeQuery extends ActiveQuery
{

    use DefaultQueryTrait;

    public function useInFilter()
    {
        return $this->andWhere([$this->modelClass::tableName().'.use_in_filter' => true]);
    }

    /**
     *
     * @return $this
     */
    public function useInVariants()
    {
        $this->andWhere([$this->modelClass::tableName().'.use_in_variants' => true]);
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function useInCompare()
    {
        return $this->andWhere([$this->modelClass::tableName().'.use_in_compare' => true]);
    }

    /**
     * Отобрадение атрибутов в товаре
     * @return $this
     */
    public function displayOnFront()
    {
        return $this->andWhere([$this->modelClass::tableName().'.display_on_front' => true]);
    }

    /**
     * Отобрадение атрибутов в списке
     * @return $this
     */
    public function displayOnList()
    {
        return $this->andWhere([$this->modelClass::tableName().'.display_on_list' => true]);
    }

    /**
     * Отобрадение атрибутов в сетке
     * @return $this
     */
    public function displayOnGrid()
    {
        return $this->andWhere([$this->modelClass::tableName().'.display_on_grid' => true]);
    }

    /**
     * Отобрадение атрибутов в корзине
     * @return $this
     */
    public function displayOnCart()
    {
        return $this->andWhere([$this->modelClass::tableName().'.display_on_cart' => true]);
    }

    /**
     * Отобрадение атрибутов в pdf печатей
     * @return $this
     */
    public function displayOnPdf()
    {
        return $this->andWhere([$this->modelClass::tableName().'.display_on_pdf' => true]);
    }

}
