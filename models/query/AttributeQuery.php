<?php

namespace panix\mod\shop\models\query;


use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;
use panix\engine\traits\query\TranslateQueryTrait;

class AttributeQuery extends ActiveQuery
{

    use DefaultQueryTrait, TranslateQueryTrait;

    public function useInFilter()
    {
        $this->andWhere(['use_in_filter' => 1]);
        return $this;
    }

    public function useInVariants()
    {
        $this->andWhere(['use_in_variants' => 1]);
        return $this;
    }

    public function useInCompare()
    {
        $this->andWhere(['use_in_compare' => 1]);
        return $this;
    }

    public function displayOnFront()
    {
        $this->andWhere(['display_on_front' => 1]);
        return $this;
    }

    public function displayOnList()
    {
        $this->andWhere(['display_on_list' => 1]);
        return $this;
    }


}
