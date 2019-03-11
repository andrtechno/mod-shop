<?php

namespace panix\mod\shop\models\query;

use panix\engine\behaviors\nestedsets\NestedSetsQueryBehavior;
use Yii;

class CategoryQuery extends \yii\db\ActiveQuery
{

    use \panix\engine\traits\DefaultQueryTrait;

    public function behaviors()
    {
        return [
            [
                'class' => NestedSetsQueryBehavior::class,
            ]
        ];
    }

    public function excludeRoot()
    {
        // $this->addWhere(['condition' => 'id != 1']);
        $this->andWhere(['!=', 'id', 1]);
        return $this;
    }

}
