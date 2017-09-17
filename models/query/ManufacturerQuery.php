<?php

namespace panix\mod\shop\models\query;

use panix\engine\traits\DefaultQueryTrait;

class ManufacturerQuery extends \yii\db\ActiveQuery {

    use DefaultQueryTrait;

    public function productsCount() {
        // return $this->hasOne(ShopProduct::className(), ['manufacturer_id' => 'id'])->addSelect('count(id)');
    }

}
