<?php

namespace panix\mod\shop\models\query;


use panix\engine\behaviors\nestedsets\NestedSetsQueryBehavior;

class ShopCategoryQuery extends \yii\db\ActiveQuery {

    // use NestedSetsQueryTrait;
    public function behaviors() {
        return [
            [
                'class' => NestedSetsQueryBehavior::className(),
            ]
        ];
    }

    public function published($state = 1) {
        return $this->andWhere(['switch' => $state]);
    }

    public function excludeRoot() {
        // $this->addWhere(['condition' => 'id != 1']);
        $this->andWhere(['!=', 'id', 1]);
        return $this;
    }

}
