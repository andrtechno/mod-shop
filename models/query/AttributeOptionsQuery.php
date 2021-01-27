<?php

namespace panix\mod\shop\models\query;

use panix\engine\traits\query\TranslateQueryTrait;
use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;

/**
 * Class AttributeOptionsQuery
 * @package panix\mod\shop\models\query
 * @use ActiveQuery
 */
class AttributeOptionsQuery extends ActiveQuery
{

    use DefaultQueryTrait;

    public $defaultSort=true;

}
