<?php

namespace panix\mod\shop\models\query;

use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;
use panix\engine\traits\query\TranslateQueryTrait;

class ProductReviewsQuery extends ActiveQuery
{

    use DefaultQueryTrait;

    public function status($status)
    {
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();
        $this->andWhere([$tableName.'.`status`'=>$status]);
        return $this;
    }
    public function aggregateRate()
    {
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();

        $this->addSelect(["SUM({$tableName}.`rate`) AS rate"])->where(['>','rate',0]);
        return $this;
    }

}
