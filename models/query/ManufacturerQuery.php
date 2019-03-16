<?php

namespace panix\mod\shop\models\query;


use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;
use panix\engine\traits\query\TranslateQueryTrait;

class ManufacturerQuery extends ActiveQuery
{

    use DefaultQueryTrait, TranslateQueryTrait;

    public function productsCount()
    {
        // return $this->hasOne(Product::className(), ['manufacturer_id' => 'id'])->addSelect('count(id)');
    }

}
