<?php

namespace panix\shop\models\query;

use yii\db\ActiveQuery;

class ShopProductQuery extends ActiveQuery {

    public function published($state = 1) {
        return $this->andWhere(['switch' => $state]);
    }

}
