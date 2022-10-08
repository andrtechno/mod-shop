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

    public function init()
    {
        //@todo: не нужно в карточке товара!!!
        /** @var \yii\db\ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();
        if (isset($modelClass::getTableSchema()->columns['ordern'])) {
            $this->addOrderBy(["{$tableName}.ordern" => SORT_DESC]);
        }
        parent::init();
    }
}
