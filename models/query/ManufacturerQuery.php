<?php

namespace panix\mod\shop\models\query;

use yii\db\ActiveQuery;
use panix\engine\traits\DefaultQueryTrait;

class ManufacturerQuery extends ActiveQuery
{

    use DefaultQueryTrait;

    public function productsCount()
    {
        // return $this->hasOne(Product::className(), ['manufacturer_id' => 'id'])->addSelect('count(id)');
    }

}
