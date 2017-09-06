<?php

namespace panix\mod\shop\models\query;

class AttributeQuery extends \yii\db\ActiveQuery {

    public function useInFilter() {
        return $this->andWhere(['use_in_filter' => 1]);
    }

    public function useInVariants() {
        return $this->andWhere(['use_in_variants' => 1]);
    }

    public function useInCompare() {
        return $this->andWhere(['use_in_compare' => 1]);
    }

    public function displayOnFront() {
        return $this->andWhere(['display_on_front' => 1]);
    }


    /**

      'sorting' => array('order' => $t . '.ordern DESC'),
     */
}
