<?php

namespace panix\mod\shop\models\query;

use panix\engine\traits\query\TranslateQueryTrait;
use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;

class AttributeOptionsQuery extends ActiveQuery
{

    use DefaultQueryTrait, TranslateQueryTrait;

}
