<?php

namespace panix\mod\shop\models\query;

use panix\engine\behaviors\nestedsets\NestedSetsQueryBehavior;
use panix\engine\traits\query\TranslateQueryTrait;
use panix\engine\traits\query\DefaultQueryTrait;
use yii\db\ActiveQuery;

/**
 * Class CategoryQuery
 * @package panix\mod\shop\models\query
 * @use ActiveQuery
 */
class CategoryQuery extends ActiveQuery
{

    use DefaultQueryTrait, TranslateQueryTrait;

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
