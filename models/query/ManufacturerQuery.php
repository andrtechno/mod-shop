<?php

namespace panix\mod\shop\models\query;

class ManufacturerQuery extends \yii\db\ActiveQuery {

    use \panix\engine\traits\DefaultQueryTrait;

    public function productsCount() {
        // return $this->hasOne(Product::className(), ['manufacturer_id' => 'id'])->addSelect('count(id)');
    }

}
