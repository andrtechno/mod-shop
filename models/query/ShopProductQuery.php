<?php

namespace panix\mod\shop\models\query;

use yii\db\ActiveQuery;

class ShopProductQuery extends ActiveQuery {

    public function published($state = 1) {
        return $this->andWhere(['switch' => $state]);
    }

    /**
     * Product by category
     *
     * @return $this
     */
    public function category() {
        $this->joinWith(['category']);
        return $this;
    }
    
    
    /**
     * Product by manufacturer
     *
     * @return $this
     */
    public function manufacturer() {
        $this->joinWith(['manufacturer']);
        return $this;
    }

}
