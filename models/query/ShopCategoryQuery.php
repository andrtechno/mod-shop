<?php

namespace panix\mod\shop\models\query;

use panix\engine\behaviors\NestedSetsQueryBehavior;

class ShopCategoryQuery extends \yii\db\ActiveQuery {

    public function behaviors() {
        return [
            NestedSetsQueryBehavior::className(),
        ];
    }
    public function published($state = 1) {
        return $this->andWhere(['switch' => $state]);
    }
}
